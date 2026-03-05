<script setup>
import { ref, watch, computed, nextTick, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    question: Object,
    answerColors: Array,
});

const emit = defineEmits(['update', 'remove-image', 'dirty']);

// Local state for editing
const questionText = ref(props.question.question_text || '');
const answers = ref(JSON.parse(JSON.stringify(props.question.answers || [])));
const imageFile = ref(null);
const imagePreview = ref(null);
const fileInput = ref(null);

// Guard to prevent save loop when syncing from parent
let syncing = false;

// Sync local state when parent updates (e.g. type change from properties panel)
watch(() => props.question, (newQ) => {
    syncing = true;
    questionText.value = newQ.question_text || '';
    answers.value = JSON.parse(JSON.stringify(newQ.answers || []));
    // Clear local file state after server has persisted the image
    if (newQ.image_url && imageFile.value) {
        imageFile.value = null;
        imagePreview.value = null;
    }
    nextTick(() => { syncing = false; });
}, { deep: true });

// Mark parent as dirty without triggering a save
function markDirty() {
    if (syncing) return;
    emit('dirty');
}

function buildData() {
    const data = {
        question_text: questionText.value,
        type: props.question.type,
        time_limit: props.question.time_limit,
        points: props.question.points,
        answers: answers.value.map((a, i) => ({
            id: a.id || null,
            answer_text: a.answer_text,
            is_correct: a.is_correct,
            color: a.color,
        })),
    };
    if (imageFile.value) {
        data.image = imageFile.value;
    }
    return data;
}

function emitUpdate() {
    emit('update', buildData());
}

// Image handling
function onImageSelect(e) {
    const file = e.target.files[0];
    if (!file) return;
    imageFile.value = file;
    imagePreview.value = URL.createObjectURL(file);
    markDirty();
    // Reset so same file can be re-selected
    if (fileInput.value) fileInput.value.value = '';
}

function onDrop(e) {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    imageFile.value = file;
    imagePreview.value = URL.createObjectURL(file);
    markDirty();
}

function removeImage() {
    imageFile.value = null;
    imagePreview.value = null;
    emit('remove-image');
}

// Watch for text changes — only mark dirty, no auto-save
watch(questionText, () => markDirty());

function onAnswerTextChange(index, value) {
    answers.value[index].answer_text = value;
    markDirty();
}

function toggleCorrect(index) {
    if (props.question.type === 'multiple_choice') {
        answers.value.forEach((a, i) => {
            a.is_correct = i === index;
        });
    } else {
        answers.value.forEach((a, i) => {
            a.is_correct = i === index;
        });
    }
    markDirty();
}

const isTrueFalse = computed(() => props.question.type === 'true_false');

// Expose for parent to trigger immediate save
function emitUpdateNow() {
    emitUpdate();
}
defineExpose({ emitUpdateNow, buildData });

// Color helpers
function getColorHex(colorValue) {
    const found = props.answerColors.find(c => c.value === colorValue);
    return found ? found.hex : '#ccc';
}

function getColorShape(colorValue) {
    const found = props.answerColors.find(c => c.value === colorValue);
    return found ? found.shape : '';
}

// Shape SVGs
function getShapeIcon(shape) {
    const shapes = {
        triangle: 'M12 2L2 22h20L12 2z',
        diamond: 'M12 2L2 12l10 10 10-10L12 2z',
        circle: '', // use circle element
        square: 'M3 3h18v18H3z',
    };
    return shapes[shape] || '';
}

// Answer slot background classes
function getAnswerClasses(color) {
    const map = {
        red: 'border-[#FF6B6B] bg-[#FF6B6B]/5',
        blue: 'border-[#5B8DEF] bg-[#5B8DEF]/5',
        yellow: 'border-[#FECA57] bg-[#FECA57]/5',
        green: 'border-[#48DBAB] bg-[#48DBAB]/5',
    };
    return map[color] || 'border-gray-200 bg-gray-50';
}

function getAnswerActiveClasses(color) {
    const map = {
        red: 'ring-[#FF6B6B] bg-[#FF6B6B]/20 border-[#FF6B6B]',
        blue: 'ring-[#5B8DEF] bg-[#5B8DEF]/20 border-[#5B8DEF]',
        yellow: 'ring-[#FECA57] bg-[#FECA57]/20 border-[#FECA57]',
        green: 'ring-[#48DBAB] bg-[#48DBAB]/20 border-[#48DBAB]',
    };
    return map[color] || '';
}
</script>

<template>
    <div class="mx-auto max-w-3xl">
        <!-- Question Text -->
        <div class="mb-6 rounded-xl bg-white p-6 shadow-sm dark:bg-gray-800">
            <textarea
                v-model="questionText"
                rows="3"
                :placeholder="t('quiz.question_placeholder')"
                class="w-full resize-none border-none bg-transparent text-center text-xl font-semibold text-gray-800 placeholder-gray-300 focus:outline-none focus:ring-0 dark:text-gray-100 dark:placeholder-gray-600"
            ></textarea>

            <!-- Question image -->
            <div class="mt-4">
                <!-- Existing image or preview -->
                <div
                    v-if="imagePreview || question.image_url"
                    class="relative rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-700/50"
                >
                    <img
                        :src="imagePreview || question.image_url"
                        class="max-h-48 w-full object-contain"
                        alt=""
                    />
                    <button
                        @click="removeImage"
                        class="absolute top-2 right-2 rounded-full bg-red-500 p-1.5 text-white shadow-md transition hover:bg-red-600"
                        :title="t('common.delete')"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Upload area -->
                <div
                    v-else
                    class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-200 p-6 transition hover:border-gray-400 hover:bg-gray-50 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-800"
                    @click="fileInput?.click()"
                    @dragover.prevent
                    @drop="onDrop"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-8 w-8 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm text-gray-400 dark:text-gray-500">{{ t('quiz.upload_image') }}</p>
                    <p class="mt-1 text-xs text-gray-300 dark:text-gray-600">{{ t('quiz.upload_hint') }}</p>
                </div>

                <input
                    ref="fileInput"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="onImageSelect"
                />
            </div>
        </div>

        <!-- Answer Grid -->
        <div :class="isTrueFalse ? 'grid grid-cols-2 gap-4' : 'grid grid-cols-2 gap-4'">
            <div
                v-for="(answer, index) in answers"
                :key="answer.id || index"
                :class="[
                    'relative rounded-xl border-2 p-4 transition cursor-pointer',
                    answer.is_correct
                        ? getAnswerActiveClasses(answer.color) + ' ring-2'
                        : getAnswerClasses(answer.color),
                ]"
                @click="toggleCorrect(index)"
            >
                <!-- Shape icon -->
                <div class="mb-3 flex items-center gap-2">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-lg"
                        :style="{ backgroundColor: getColorHex(answer.color) + '30' }"
                    >
                        <svg v-if="getColorShape(answer.color) !== 'circle'" class="h-4 w-4" viewBox="0 0 24 24" :fill="getColorHex(answer.color)">
                            <path :d="getShapeIcon(getColorShape(answer.color))" />
                        </svg>
                        <svg v-else class="h-4 w-4" viewBox="0 0 24 24" :fill="getColorHex(answer.color)">
                            <circle cx="12" cy="12" r="10" />
                        </svg>
                    </div>

                    <!-- Correct indicator -->
                    <div class="ml-auto">
                        <div
                            :class="[
                                'flex h-6 w-6 items-center justify-center rounded-full border-2 transition',
                                answer.is_correct
                                    ? 'border-green-500 bg-green-500'
                                    : 'border-gray-300 hover:border-gray-400',
                            ]"
                        >
                            <svg v-if="answer.is_correct" class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Answer text input -->
                <input
                    v-if="!isTrueFalse"
                    :value="answer.answer_text"
                    @input="(e) => onAnswerTextChange(index, e.target.value)"
                    @click.stop
                    :placeholder="t('quiz.answer_placeholder')"
                    class="w-full border-none bg-transparent text-sm font-medium text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-0 dark:text-gray-200 dark:placeholder-gray-500"
                />

                <!-- Fixed text for True/False -->
                <p v-else class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                    {{ answer.answer_text }}
                </p>
            </div>
        </div>
    </div>
</template>
