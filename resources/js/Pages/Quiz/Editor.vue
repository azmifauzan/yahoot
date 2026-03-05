<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import QuestionSidebar from '@/Components/Quiz/QuestionSidebar.vue';
import QuestionEditor from '@/Components/Quiz/QuestionEditor.vue';
import QuestionProperties from '@/Components/Quiz/QuestionProperties.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { toast, confirm } = useSwal();

const props = defineProps({
    quiz: Object,
    questionTypes: Array,
    pointTypes: Array,
    answerColors: Array,
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
    });
}

// Question validation indicator
function isQuestionComplete(question) {
    if (!question.question_text) return false;
    if (!question.answers || question.answers.length < 2) return false;

    const hasCorrect = question.answers.some(a => a.is_correct);
    if (!hasCorrect) return false;

    if (question.type === 'multiple_choice') {
        const filledAnswers = question.answers.filter(a => a.answer_text);
        if (filledAnswers.length < 2) return false;
    }

    return true;
}
</script>

<template>
    <AppLayout :title="isNew ? t('dashboard.create_quiz') : quizForm.title || t('quiz.untitled')">
        <!-- Custom header -->
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button
                        @click="goBack"
                        class="flex items-center gap-1 text-sm text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        {{ t('quiz.back') }}
                    </button>

                    <!-- Inline title edit -->
                    <input
                        v-if="!isNew"
                        v-model="quizForm.title"
                        type="text"
                        :placeholder="t('quiz.untitled')"
                        class="border-none bg-transparent text-lg font-semibold text-gray-800 focus:outline-none focus:ring-0 p-0 dark:text-gray-100"
                    />
                    <input
                        v-else
                        v-model="quizForm.title"
                        type="text"
                        :placeholder="t('quiz.untitled')"
                        class="border-none bg-transparent text-lg font-semibold text-gray-800 focus:outline-none focus:ring-0 p-0 dark:text-gray-100"
                    />
                </div>

                <div class="flex items-center gap-3">
                    <!-- Saving indicator -->
                    <span v-if="saving" class="text-xs text-gray-400 flex items-center gap-1">
                        <svg class="h-3.5 w-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ t('common.loading') }}
                    </span>
                    <span v-else-if="!hasUnsavedChanges && !titleDirty && !isNew && questions.length > 0" class="text-xs text-green-500 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ t('quiz.saved') }}
                    </span>

                    <!-- Save button -->
                    <button
                        v-if="!isNew && selectedQuestion"
                        @click="manualSave"
                        :disabled="saving || (!hasUnsavedChanges && !titleDirty)"
                        :class="[
                            'rounded-lg px-4 py-2 text-sm font-semibold transition flex items-center gap-1.5',
                            saving || (!hasUnsavedChanges && !titleDirty)
                                ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-700 dark:text-gray-500'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600',
                        ]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        {{ t('quiz.save') }}
                    </button>

                    <!-- Publish button -->
                    <button
                        v-if="!isNew"
                        @click="togglePublish"
                        :class="[
                            'rounded-lg px-4 py-2 text-sm font-semibold transition',
                            quiz.is_published
                                ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                : 'bg-primary-500 text-white hover:bg-primary-600',
                        ]"
                    >
                        {{ quiz.is_published ? t('quiz.unpublish') : t('quiz.publish') }}
                    </button>

                    <!-- Create button for new quiz -->
                    <button
                        v-if="isNew"
                        @click="createQuiz"
                        class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-600"
                    >
                        {{ t('quiz.save') }}
                    </button>
                </div>
            </div>
        </template>

        <!-- Editor Layout -->
        <div v-if="!isNew" class="flex h-[calc(100vh-4rem)] overflow-hidden">
            <!-- Left Sidebar - Question List -->
            <QuestionSidebar
                :questions="questions"
                :selectedIndex="selectedQuestionIndex"
                :isQuestionComplete="isQuestionComplete"
                @select="selectQuestion"
                @add="addQuestion"
                @reorder="reorderQuestions"
                @delete="deleteQuestion"
            />

            <!-- Main Content - Question Editor -->
            <div class="flex-1 overflow-y-auto bg-gray-50 p-6 dark:bg-gray-900">
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

                <div v-else class="flex h-full items-center justify-center">
                    <div class="text-center">
                        <div class="mb-4 rounded-full bg-primary-50 p-6 inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">{{ t('quiz.add_question') }}</p>
                        <button
                            @click="addQuestion"
                            class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-600"
                        >
                            {{ t('quiz.add_question') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar - Question Properties -->
            <QuestionProperties
                v-if="selectedQuestion"
                :question="selectedQuestion"
                :questionTypes="questionTypes"
                :pointTypes="pointTypes"
                @update="(data) => updateQuestion(selectedQuestion, data)"
            />
        </div>

        <!-- New Quiz Form -->
        <div v-else class="py-12">
            <div class="mx-auto max-w-xl px-4">
                <div class="rounded-xl bg-white p-8 shadow-sm">
                    <h3 class="mb-6 text-lg font-semibold text-gray-800">{{ t('dashboard.create_quiz') }}</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">{{ t('quiz.untitled') }}</label>
                            <input
                                v-model="quizForm.title"
                                type="text"
                                :placeholder="t('quiz.untitled')"
                                class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                @keyup.enter="createQuiz"
                            />
                        </div>

                        <button
                            @click="createQuiz"
                            class="w-full rounded-lg bg-primary-500 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-600"
                        >
                            {{ t('dashboard.create_quiz') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Publish errors -->
        <div v-if="$page.props.errors?.publish" class="fixed bottom-4 right-4 z-50 max-w-sm">
            <div class="rounded-xl bg-red-50 p-4 shadow-lg">
                <h4 class="mb-2 text-sm font-semibold text-red-700">{{ t('quiz.validation_error') }}</h4>
                <ul class="space-y-1">
                    <li v-for="(error, i) in ($page.props.errors.publish instanceof Array ? $page.props.errors.publish : [$page.props.errors.publish])" :key="i" class="text-xs text-red-600">
                        {{ error }}
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
