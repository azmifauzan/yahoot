<script setup>
import { ref, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    question: Object,
    answerColors: Array,
});

const emit = defineEmits(['update']);

// Local state for editing
const questionText = ref(props.question.question_text || '');
const answers = ref(JSON.parse(JSON.stringify(props.question.answers || [])));

// Debounced auto-save
let saveTimeout = null;
function scheduleUpdate() {
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => {
        emitUpdate();
    }, 600);
}

function emitUpdate() {
    emit('update', {
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
    });
}

// Watch for text changes
watch(questionText, () => scheduleUpdate());

function onAnswerTextChange(index, value) {
    answers.value[index].answer_text = value;
    scheduleUpdate();
}

function toggleCorrect(index) {
    if (props.question.type === 'multiple_choice') {
        // Single correct answer for now
        answers.value.forEach((a, i) => {
            a.is_correct = i === index;
        });
    } else {
        // True/False: toggle
        answers.value.forEach((a, i) => {
            a.is_correct = i === index;
        });
    }
    emitUpdate();
}

const isTrueFalse = computed(() => props.question.type === 'true_false');

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
        <div class="mb-6 rounded-xl bg-white p-6 shadow-sm">
            <textarea
                v-model="questionText"
                rows="3"
                :placeholder="t('quiz.question_placeholder')"
                class="w-full resize-none border-none bg-transparent text-center text-xl font-semibold text-gray-800 placeholder-gray-300 focus:outline-none focus:ring-0"
            ></textarea>

            <!-- Question image upload area -->
            <div
                v-if="question.image"
                class="relative mt-4 rounded-lg overflow-hidden"
            >
                <img :src="question.image" class="max-h-48 w-full object-contain" alt="" />
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
                    class="w-full border-none bg-transparent text-sm font-medium text-gray-700 placeholder-gray-300 focus:outline-none focus:ring-0"
                />

                <!-- Fixed text for True/False -->
                <p v-else class="text-sm font-semibold text-gray-700">
                    {{ answer.answer_text }}
                </p>
            </div>
        </div>
    </div>
</template>
