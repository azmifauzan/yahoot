<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import QuestionSidebar from '@/Components/Quiz/QuestionSidebar.vue';
import QuestionEditor from '@/Components/Quiz/QuestionEditor.vue';
import QuestionProperties from '@/Components/Quiz/QuestionProperties.vue';
import ThemeSelector from '@/Components/Quiz/ThemeSelector.vue';
import CategorySelect from '@/Components/Quiz/CategorySelect.vue';
import TagInput from '@/Components/Quiz/TagInput.vue';
import SoundThemeSelector from '@/Components/Quiz/SoundThemeSelector.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { toast, confirm, error: swalError } = useSwal();

const props = defineProps({
    quiz: Object,
    questionTypes: Array,
    pointTypes: Array,
    answerColors: Array,
    themes: Array,
    categories: { type: Array, default: () => [] },
});

// Quiz header form
const quizForm = useForm({
    title: props.quiz?.title || '',
    description: props.quiz?.description || '',
});

// Local questions state
const questions = ref(props.quiz?.questions || []);
const selectedQuestionIndex = ref(questions.value.length > 0 ? 0 : -1);
const selectedQuestion = computed(() =>
    selectedQuestionIndex.value >= 0 ? questions.value[selectedQuestionIndex.value] : null
);
const saving = ref(false);
const hasUnsavedChanges = ref(false);
const titleDirty = ref(false);

// Track dirty state from child QuestionEditor
function markDirty() {
    hasUnsavedChanges.value = true;
    resetIdleTimer();
}

// Manual save: triggers immediate save of the current question via QuestionEditor
const questionEditorRef = ref(null);
function manualSave() {
    if (saving.value) return;
    // Save title if changed
    if (titleDirty.value) {
        saveQuizTitle();
    }
    // Save question if dirty
    if (hasUnsavedChanges.value && selectedQuestion.value && questionEditorRef.value) {
        const data = questionEditorRef.value.buildData();
        updateQuestion(selectedQuestion.value, data);
    }
}

// Idle-based autosave: save after 5 seconds of inactivity
let idleTimer = null;
function resetIdleTimer() {
    clearTimeout(idleTimer);
    idleTimer = setTimeout(() => {
        if (hasUnsavedChanges.value || titleDirty.value) {
            manualSave();
        }
    }, 5000);
}

// beforeunload: warn user about unsaved changes
function onBeforeUnload(e) {
    if (hasUnsavedChanges.value || titleDirty.value) {
        e.preventDefault();
        e.returnValue = '';
    }
}

onMounted(() => {
    window.addEventListener('beforeunload', onBeforeUnload);
});

onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', onBeforeUnload);
    clearTimeout(idleTimer);
});

// Intercept Back navigation
function goBack() {
    if (hasUnsavedChanges.value || titleDirty.value) {
        confirm({
            title: t('quiz.unsaved_title'),
            text: t('quiz.unsaved_text'),
            confirmText: t('quiz.leave'),
            cancelText: t('common.cancel'),
            icon: 'warning',
        }).then((result) => {
            if (result.isConfirmed) {
                hasUnsavedChanges.value = false;
                titleDirty.value = false;
                router.visit(route('dashboard'));
            }
        });
    } else {
        router.visit(route('dashboard'));
    }
}

// Create quiz if new
const isNew = computed(() => !props.quiz);

function createQuiz() {
    if (isNew.value) {
        const form = useForm({
            title: quizForm.title || t('quiz.untitled'),
            description: quizForm.description,
        });
        form.post(route('quizzes.store'));
    }
}

// Save quiz title/description
function saveQuizTitle() {
    if (isNew.value || !titleDirty.value) return;
    saving.value = true;
    router.put(route('quizzes.update', props.quiz.id), {
        title: quizForm.title,
        description: quizForm.description,
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            saving.value = false;
            titleDirty.value = false;
        },
    });
}

// Watch title changes — only mark dirty, no auto-save
watch(() => quizForm.title, () => {
    if (!isNew.value) {
        titleDirty.value = true;
        resetIdleTimer();
    }
});

// Default answer templates
function getDefaultAnswers(type) {
    if (type === 'true_false') {
        return [
            { answer_text: t('quiz.true'), is_correct: true, color: 'blue' },
            { answer_text: t('quiz.false'), is_correct: false, color: 'red' },
        ];
    }
    return [
        { answer_text: '', is_correct: false, color: 'red' },
        { answer_text: '', is_correct: false, color: 'blue' },
        { answer_text: '', is_correct: false, color: 'yellow' },
        { answer_text: '', is_correct: false, color: 'green' },
    ];
}

// Add question
function addQuestion() {
    if (isNew.value) {
        createQuiz();
        return;
    }

    const type = 'multiple_choice';
    const form = useForm({
        type: type,
        question_text: '',
        time_limit: 20,
        points: 'standard',
        answers: getDefaultAnswers(type),
    });

    form.post(route('quizzes.questions.store', props.quiz.id), {
        preserveScroll: true,
        onSuccess: (page) => {
            questions.value = page.props.quiz?.questions || [];
            selectedQuestionIndex.value = questions.value.length - 1;
        },
    });
}

// Select question — save current if dirty before switching
function selectQuestion(index) {
    if (index === selectedQuestionIndex.value) return;
    if (hasUnsavedChanges.value && selectedQuestion.value && questionEditorRef.value) {
        const data = questionEditorRef.value.buildData();
        updateQuestion(selectedQuestion.value, data);
    }
    selectedQuestionIndex.value = index;
}

// Update question
function updateQuestion(question, data) {
    saving.value = true;

    // If data contains a file, use POST with method spoofing for FormData
    if (data.image instanceof File) {
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('type', data.type);
        formData.append('question_text', data.question_text || '');
        formData.append('time_limit', data.time_limit);
        formData.append('points', data.points);
        formData.append('image', data.image);

        if (data.answers) {
            data.answers.forEach((answer, i) => {
                if (answer.id) formData.append(`answers[${i}][id]`, answer.id);
                formData.append(`answers[${i}][answer_text]`, answer.answer_text || '');
                formData.append(`answers[${i}][is_correct]`, answer.is_correct ? '1' : '0');
                formData.append(`answers[${i}][color]`, answer.color);
            });
        }

        router.post(route('questions.update', question.id), formData, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page) => {
                questions.value = page.props.quiz?.questions || [];
                saving.value = false;
                hasUnsavedChanges.value = false;
            },
            onError: () => { saving.value = false; },
        });
    } else {
        router.put(route('questions.update', question.id), data, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page) => {
                questions.value = page.props.quiz?.questions || [];
                saving.value = false;
                hasUnsavedChanges.value = false;
            },
            onError: () => { saving.value = false; },
        });
    }
}

// Remove question image
function removeQuestionImage(question) {
    saving.value = true;
    router.delete(route('questions.remove-image', question.id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
            questions.value = page.props.quiz?.questions || [];
            saving.value = false;
        },
        onError: () => { saving.value = false; },
    });
}

// Delete question
function deleteQuestion(question) {
    confirm({
        title: t('quiz.delete_question_title'),
        text: t('quiz.delete_question_confirm'),
        confirmText: t('common.delete'),
        cancelText: t('common.cancel'),
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('questions.destroy', question.id), {
                preserveScroll: true,
                onSuccess: (page) => {
                    questions.value = page.props.quiz?.questions || [];
                    if (selectedQuestionIndex.value >= questions.value.length) {
                        selectedQuestionIndex.value = Math.max(0, questions.value.length - 1);
                    }
                    if (questions.value.length === 0) {
                        selectedQuestionIndex.value = -1;
                    }
                    toast(t('quiz.question_deleted'));
                },
            });
        }
    });
}

// Reorder questions
function reorderQuestions(newOrder) {
    questions.value = newOrder;
    const reorderData = newOrder.map((q, index) => ({
        id: q.id,
        order: index,
    }));

    router.post(route('questions.reorder'), { questions: reorderData }, {
        preserveScroll: true,
        preserveState: true,
    });
}

// Publish / Unpublish
function togglePublish() {
    router.post(route('quizzes.publish', props.quiz.id), {}, {
        preserveScroll: true,
        onSuccess: (page) => {
            // Update from server
        },
        onError: (errors) => {
            if (errors.publish) {
                const publishErrors = errors.publish instanceof Array ? errors.publish : [errors.publish];
                const errorList = publishErrors.map(e => `<li>${e}</li>`).join('');
                swalError(t('quiz.validation_error'), `<ul class="text-left space-y-1">${errorList}</ul>`);
            }
        },
    });
}

// Update theme
function updateTheme(themeValue) {
    if (isNew.value) return;
    router.put(route('quizzes.update', props.quiz.id), {
        theme: themeValue,
    }, {
        preserveScroll: true,
        preserveState: true,
    });
}

// Category, tags & sound settings
const categoryId = ref(props.quiz?.category_id ?? null);
const tags = ref(props.quiz?.tags?.map((tag) => tag.name) ?? []);
const soundTheme = ref(props.quiz?.settings?.sound_theme ?? 'classic');
const musicEnabled = ref(props.quiz?.settings?.music_enabled ?? true);

function updateCategory(value) {
    categoryId.value = value;
    if (isNew.value) return;
    router.put(route('quizzes.update', props.quiz.id), {
        category_id: value,
    }, {
        preserveScroll: true,
        preserveState: true,
    });
}

function updateTags(value) {
    tags.value = value;
    if (isNew.value) return;
    router.put(route('quizzes.update', props.quiz.id), {
        tags: value,
    }, {
        preserveScroll: true,
        preserveState: true,
    });
}

function updateSoundTheme(value) {
    soundTheme.value = value;
    if (isNew.value) return;
    router.put(route('quizzes.update', props.quiz.id), {
        settings: { sound_theme: value },
    }, {
        preserveScroll: true,
        preserveState: true,
    });
}

function toggleMusicEnabled() {
    musicEnabled.value = !musicEnabled.value;
    if (isNew.value) return;
    router.put(route('quizzes.update', props.quiz.id), {
        settings: { music_enabled: musicEnabled.value },
    }, {
        preserveScroll: true,
        preserveState: true,
    });
}

// Question validation indicator
function isQuestionComplete(question) {
    if (!question.question_text) return false;
    if (!question.answers || question.answers.length < 2) return false;

    // Polls don't require a correct answer
    if (question.type !== 'poll') {
        const hasCorrect = question.answers.some(a => a.is_correct);
        if (!hasCorrect) return false;
    }

    if (question.type === 'multiple_choice') {
        const filledAnswers = question.answers.filter(a => a.answer_text);
        if (filledAnswers.length < 2) return false;
    }

    return true;
}
</script>

<template>
    <AppLayout :title="isNew ? t('dashboard.create_quiz') : quizForm.title || t('quiz.untitled')" :fullscreen="true">
        <!-- Custom header -->
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2 sm:gap-4 flex-1 min-w-0">
                    <button
                        @click="goBack"
                        class="flex flex-shrink-0 items-center gap-1 text-sm text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span class="hidden sm:inline">{{ t('quiz.back') }}</span>
                    </button>

                    <!-- Inline title edit -->
                    <input
                        v-if="!isNew"
                        v-model="quizForm.title"
                        type="text"
                        :placeholder="t('quiz.untitled')"
                        class="w-full min-w-0 border-none bg-transparent text-base sm:text-lg font-semibold text-gray-800 focus:outline-none focus:ring-0 p-0 dark:text-gray-100 truncate"
                    />
                    <input
                        v-else
                        v-model="quizForm.title"
                        type="text"
                        :placeholder="t('quiz.untitled')"
                        class="w-full min-w-0 border-none bg-transparent text-base sm:text-lg font-semibold text-gray-800 focus:outline-none focus:ring-0 p-0 dark:text-gray-100 truncate"
                    />
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 sm:gap-3">
                    <!-- Saving indicator -->
                    <span v-if="saving" class="text-xs text-gray-400 flex items-center gap-1">
                        <svg class="h-3.5 w-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        <span class="hidden sm:inline">{{ t('common.loading') }}</span>
                    </span>
                    <span v-else-if="!hasUnsavedChanges && !titleDirty && !isNew && questions.length > 0" class="text-xs text-green-500 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="hidden sm:inline">{{ t('quiz.saved') }}</span>
                    </span>

                    <!-- Save button -->
                    <button
                        v-if="!isNew && selectedQuestion"
                        @click="manualSave"
                        :disabled="saving || (!hasUnsavedChanges && !titleDirty)"
                        :class="[
                            'rounded-lg px-3 sm:px-4 py-2 text-sm font-semibold transition flex items-center gap-1.5',
                            saving || (!hasUnsavedChanges && !titleDirty)
                                ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-700 dark:text-gray-500'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600',
                        ]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        <span class="hidden sm:inline">{{ t('quiz.save') }}</span>
                    </button>

                    <!-- Publish button -->
                    <button
                        v-if="!isNew"
                        @click="togglePublish"
                        :class="[
                            'rounded-lg px-3 sm:px-4 py-2 text-sm font-semibold transition',
                            quiz.is_published
                                ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                : 'bg-primary-600 text-white hover:bg-primary-700',
                        ]"
                    >
                        {{ quiz.is_published ? t('quiz.unpublish') : t('quiz.publish') }}
                    </button>

                    <!-- Play button (only for published quizzes) -->
                    <button
                        v-if="!isNew && quiz.is_published"
                        @click="router.post(route('game.store', quiz.id))"
                        class="rounded-lg bg-primary-600 px-3 sm:px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700 flex items-center gap-1.5"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        <span class="hidden sm:inline">{{ t('dashboard.play') }}</span>
                    </button>

                    <!-- Create button for new quiz -->
                    <button
                        v-if="isNew"
                        @click="createQuiz"
                        class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
                    >
                        {{ t('quiz.save') }}
                    </button>
                </div>
            </div>
        </template>

        <!-- Editor Layout -->
        <div v-if="!isNew" class="flex flex-col md:flex-row h-[calc(100vh-120px)] md:h-full overflow-y-auto md:overflow-hidden">
            <!-- Left Sidebar - Question List -->
            <QuestionSidebar
                :questions="questions"
                :selectedIndex="selectedQuestionIndex"
                :isQuestionComplete="isQuestionComplete"
                class="w-full md:w-64 max-h-48 md:max-h-none h-auto md:h-full flex-shrink-0 border-b md:border-b-0 md:border-r border-gray-200"
                @select="selectQuestion"
                @add="addQuestion"
                @reorder="reorderQuestions"
                @delete="deleteQuestion"
            />

            <!-- Main Content - Question Editor -->
            <div class="flex-1 overflow-visible md:overflow-y-auto bg-gray-50 p-4 md:p-6 dark:bg-gray-950">
                <QuestionEditor
                    v-if="selectedQuestion"
                    ref="questionEditorRef"
                    :key="selectedQuestion.id"
                    :question="selectedQuestion"
                    :answerColors="answerColors"
                    @update="(data) => updateQuestion(selectedQuestion, data)"
                    @dirty="markDirty"
                    @remove-image="removeQuestionImage(selectedQuestion)"
                />

                <div v-else class="flex h-full items-center justify-center py-10">
                    <div class="text-center">
                        <div class="mb-4 rounded-full bg-gray-100 dark:bg-gray-800 p-6 inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">{{ t('quiz.add_question') }}</p>
                        <button
                            @click="addQuestion"
                            class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
                        >
                            {{ t('quiz.add_question') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar - Question Properties + Theme -->
            <div class="w-full md:w-64 flex-shrink-0 h-auto md:h-full overflow-y-auto border-t md:border-t-0 md:border-l border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-950">
                <QuestionProperties
                    v-if="selectedQuestion"
                    :question="selectedQuestion"
                    :questionTypes="questionTypes"
                    :pointTypes="pointTypes"
                    @update="(data) => updateQuestion(selectedQuestion, data)"
                />

                <!-- Theme Selector -->
                <div :class="selectedQuestion ? 'mt-6 pt-6 border-t border-gray-200 dark:border-gray-700' : ''">
                    <ThemeSelector
                        :themes="themes"
                        :currentTheme="quiz?.theme?.value || quiz?.theme || 'standard'"
                        @select="updateTheme"
                    />
                </div>

                <!-- Category -->
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <CategorySelect
                        :categories="categories"
                        :modelValue="categoryId"
                        @update:modelValue="updateCategory"
                    />
                </div>

                <!-- Tags -->
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <TagInput :modelValue="tags" @update:modelValue="updateTags" />
                </div>

                <!-- Sound theme -->
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <SoundThemeSelector :currentTheme="soundTheme" @select="updateSoundTheme" />
                    <label class="mt-3 flex items-center justify-between">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ t('sound.music_enabled') }}</span>
                        <button
                            type="button"
                            @click="toggleMusicEnabled"
                            :class="['relative inline-flex h-6 w-11 rounded-full transition', musicEnabled ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700']"
                        >
                            <span :class="['absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform', musicEnabled ? 'translate-x-5' : 'translate-x-0']" />
                        </button>
                    </label>
                </div>
            </div>
        </div>

        <!-- New Quiz Form -->
        <div v-else class="py-12">
            <div class="mx-auto max-w-xl px-4">
                <div class="rounded-xl bg-white p-8 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                    <h3 class="mb-6 text-lg font-semibold text-gray-800">{{ t('dashboard.create_quiz') }}</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ t('quiz.untitled') }}</label>
                            <input
                                v-model="quizForm.title"
                                type="text"
                                :placeholder="t('quiz.untitled')"
                                class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:focus:border-primary-400 dark:focus:ring-primary-400"
                                @keyup.enter="createQuiz"
                            />
                        </div>

                        <button
                            @click="createQuiz"
                            class="w-full rounded-lg bg-primary-600 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700"
                        >
                            {{ t('dashboard.create_quiz') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>


    </AppLayout>
</template>
