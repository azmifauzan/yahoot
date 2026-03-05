<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    question: Object,
    questionTypes: Array,
    pointTypes: Array,
});

const emit = defineEmits(['update']);

const timeLimits = [5, 10, 20, 30, 60, 90, 120];

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

function updateType(newType) {
    emit('update', {
        type: newType,
        question_text: props.question.question_text,
        time_limit: props.question.time_limit,
        points: typeof props.question.points === 'object' ? props.question.points.value || props.question.points : props.question.points,
        answers: getDefaultAnswers(newType),
    });
}

function updateTimeLimit(limit) {
    emit('update', {
        type: props.question.type,
        question_text: props.question.question_text,
        time_limit: parseInt(limit),
        points: typeof props.question.points === 'object' ? props.question.points.value || props.question.points : props.question.points,
        answers: props.question.answers.map(a => ({
            id: a.id || null,
            answer_text: a.answer_text,
            is_correct: a.is_correct,
            color: typeof a.color === 'object' ? a.color.value || a.color : a.color,
        })),
    });
}

function updatePoints(points) {
    emit('update', {
        type: props.question.type,
        question_text: props.question.question_text,
        time_limit: props.question.time_limit,
        points: points,
        answers: props.question.answers.map(a => ({
            id: a.id || null,
            answer_text: a.answer_text,
            is_correct: a.is_correct,
            color: typeof a.color === 'object' ? a.color.value || a.color : a.color,
        })),
    });
}

function getQuestionTypeValue(q) {
    return typeof q.type === 'object' ? q.type.value || q.type : q.type;
}

function getPointsValue(q) {
    return typeof q.points === 'object' ? q.points.value || q.points : q.points;
}

function getPointLabel(pt) {
    const labels = {
        standard: () => t('quiz.points_standard'),
        double: () => t('quiz.points_double'),
        none: () => t('quiz.points_none'),
    };
    const val = typeof pt === 'object' ? pt.value || pt : pt;
    return labels[val] ? labels[val]() : val;
}

function getQuestionTypeLabel(qt) {
    const labels = {
        multiple_choice: () => t('quiz.multiple_choice'),
        true_false: () => t('quiz.true_false'),
    };
    const val = typeof qt === 'object' ? qt.value || qt : qt;
    return labels[val] ? labels[val]() : val;
}
</script>

<template>
    <div class="w-64 flex-shrink-0 overflow-y-auto border-l border-gray-200 bg-white p-4">
        <h3 class="mb-4 text-sm font-semibold text-gray-500 uppercase tracking-wide">
            {{ t('quiz.question_type') }}
        </h3>

        <!-- Question Type -->
        <div class="mb-6">
            <div class="space-y-2">
                <button
                    v-for="qt in questionTypes"
                    :key="qt.value || qt"
                    @click="updateType(qt.value || qt)"
                    :class="[
                        'flex w-full items-center gap-3 rounded-lg border-2 px-3 py-2.5 text-sm font-medium transition',
                        getQuestionTypeValue(question) === (qt.value || qt)
                            ? 'border-primary-500 bg-primary-50 text-primary-700'
                            : 'border-gray-200 text-gray-600 hover:border-gray-300',
                    ]"
                >
                    <span v-if="(qt.value || qt) === 'multiple_choice'" class="text-base">📝</span>
                    <span v-else class="text-base">✅</span>
                    {{ getQuestionTypeLabel(qt) }}
                </button>
            </div>
        </div>

        <!-- Time Limit -->
        <div class="mb-6">
            <h3 class="mb-2 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                {{ t('quiz.time_limit') }}
            </h3>
            <select
                :value="question.time_limit"
                @change="(e) => updateTimeLimit(e.target.value)"
                class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
            >
                <option v-for="limit in timeLimits" :key="limit" :value="limit">
                    {{ t('quiz.seconds', { n: limit }) }}
                </option>
            </select>
        </div>

        <!-- Points -->
        <div class="mb-6">
            <h3 class="mb-2 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                {{ t('quiz.points') }}
            </h3>
            <div class="space-y-2">
                <button
                    v-for="pt in pointTypes"
                    :key="pt.value || pt"
                    @click="updatePoints(pt.value || pt)"
                    :class="[
                        'flex w-full items-center justify-between rounded-lg border-2 px-3 py-2 text-sm font-medium transition',
                        getPointsValue(question) === (pt.value || pt)
                            ? 'border-primary-500 bg-primary-50 text-primary-700'
                            : 'border-gray-200 text-gray-600 hover:border-gray-300',
                    ]"
                >
                    {{ getPointLabel(pt) }}
                    <span class="text-xs text-gray-400">
                        {{ (pt.value || pt) === 'standard' ? '1000' : (pt.value || pt) === 'double' ? '2000' : '0' }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>
