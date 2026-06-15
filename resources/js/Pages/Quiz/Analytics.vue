<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';

const { t } = useI18n();

const props = defineProps({
    quiz: { type: Object, required: true },
    questions: { type: Array, default: () => [] },
    sessionsCount: { type: Number, default: 0 },
});

const difficultyStyles = {
    easy: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
    medium: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    hard: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    unknown: 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
};

const barColor = {
    easy: 'bg-green-500',
    medium: 'bg-amber-500',
    hard: 'bg-red-500',
    unknown: 'bg-gray-300 dark:bg-gray-600',
};

const hasData = props.questions.some((q) => q.total_attempts > 0);
</script>

<template>
    <Head :title="`${quiz.title} - ${t('analytics.title')}`" />

    <AppLayout :title="t('analytics.title')">
        <template #header>
            <div class="flex items-center gap-2">
                <Link
                    :href="route('quizzes.history', quiz.id)"
                    class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    &larr; {{ t('host.history_title') }}
                </Link>
            </div>
            <h2 class="font-display text-xl font-bold text-gray-800 dark:text-gray-100">
                {{ quiz.title }} · {{ t('analytics.title') }}
            </h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ t('analytics.subtitle', { count: sessionsCount }) }}
            </p>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div v-if="!hasData" class="rounded-xl border border-gray-100 bg-white p-10 text-center text-sm text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
                    {{ t('analytics.no_data') }}
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="(q, idx) in questions"
                        :key="q.question_id"
                        class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-gray-400">Q{{ idx + 1 }}</span>
                                <h3 class="text-sm font-semibold leading-snug text-gray-900 dark:text-white">{{ q.question_text }}</h3>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold" :class="difficultyStyles[q.difficulty]">
                                {{ t(`analytics.${q.difficulty}`) }}
                            </span>
                        </div>

                        <div class="mb-2 h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-full rounded-full" :class="barColor[q.difficulty]" :style="{ width: Math.round(q.correct_rate * 100) + '%' }"></div>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ t('analytics.correct_rate') }}: <strong class="text-gray-900 dark:text-white">{{ Math.round(q.correct_rate * 100) }}%</strong></span>
                            <span>{{ t('analytics.attempts') }}: <strong class="text-gray-900 dark:text-white">{{ q.total_attempts }}</strong></span>
                            <span>{{ t('analytics.avg_time') }}: <strong class="text-gray-900 dark:text-white">{{ t('analytics.seconds', { n: (q.avg_time / 1000).toFixed(1) }) }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
