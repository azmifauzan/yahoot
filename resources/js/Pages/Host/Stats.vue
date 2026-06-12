<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import AvatarDisplay from '@/Components/Avatar/AvatarDisplay.vue';

const { t } = useI18n();

const props = defineProps({
    gameSession: Object,
    quiz: Object,
    leaderboard: Array,
    totalQuestions: Number,
    questions: Array,
});

function formatDate(value) {
    return new Date(value).toLocaleString();
}

function getCorrectPercentage(correct, total) {
    if (total === 0) return 0;
    return Math.round((correct / total) * 100);
}
</script>

<template>
    <Head :title="`${quiz.title} - ${t('host.stats_title')}`" />

    <AppLayout :title="t('host.stats_title')">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <Link
                            :href="route('quizzes.history', quiz.id)"
                            class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            &larr; {{ t('host.history_title') }}
                        </Link>
                    </div>
                    <h2 class="font-display text-xl font-bold leading-tight text-gray-800 dark:text-gray-100">
                        {{ quiz.title }} &middot; {{ t('host.stats_title') }}
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ t('host.played_on') }} {{ formatDate(gameSession.finished_at) }} &middot;
                        {{ t('host.game_code') }}: {{ gameSession.game_code }}
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <button
                        @click="router.post(route('game.store', quiz.id))"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-500 active:scale-95"
                    >
                        🔄 {{ t('game.play_again') }}
                    </button>
                    <a
                        :href="route('game.export', gameSession.id)"
                        class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm border border-gray-200 transition hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-800 dark:hover:bg-gray-800"
                    >
                        📥 {{ t('host.download_csv') }}
                    </a>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Summary Stats Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div class="overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ t('host.players') }}</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ leaderboard.length }}</p>
                    </div>
                    <div class="overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ t('host.questions') }}</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ totalQuestions }}</p>
                    </div>
                    <div class="overflow-hidden rounded-xl bg-white p-5 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ t('host.winner') }}</p>
                        <div v-if="leaderboard[0]" class="mt-2 flex items-center gap-2">
                            <AvatarDisplay :name="leaderboard[0].avatar" :size="32" />
                            <p class="text-lg font-bold text-gray-900 dark:text-white truncate max-w-[150px]">{{ leaderboard[0].nickname }}</p>
                        </div>
                        <p v-else class="mt-2 text-lg font-bold text-gray-400 truncate">-</p>
                    </div>
                </div>

                <!-- Leaderboard & Question breakdown grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Leaderboard Table -->
                    <div class="lg:col-span-2 overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 flex flex-col">
                        <div class="p-5 border-b border-gray-100 dark:border-gray-800">
                            <h3 class="font-display text-lg font-bold text-gray-900 dark:text-white">{{ t('host.player_rankings') }}</h3>
                        </div>
                        <div class="overflow-x-auto flex-1">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-800 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                                        <th class="py-3 px-4 w-12 text-center">#</th>
                                        <th class="py-3 px-4">{{ t('host.player') }}</th>
                                        <th class="py-3 px-4 text-center">{{ t('host.correct') }}</th>
                                        <th class="py-3 px-4 text-center">{{ t('host.streak') }}</th>
                                        <th class="py-3 px-4 text-right">{{ t('host.score') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                                    <tr v-for="entry in leaderboard" :key="entry.player_id" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                        <td class="py-3 px-4 text-center font-extrabold text-gray-950 dark:text-white">
                                            {{ entry.rank <= 3 ? ['🥇','🥈','🥉'][entry.rank - 1] : entry.rank }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2">
                                                <AvatarDisplay :name="entry.avatar" :size="32" />
                                                <span class="font-bold text-gray-900 dark:text-white truncate max-w-[150px]">{{ entry.nickname }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">
                                            {{ entry.correct_count ?? '-' }} / {{ totalQuestions }}
                                        </td>
                                        <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300">
                                            🔥 {{ entry.best_streak ?? '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-extrabold text-primary-600 dark:text-primary-400">
                                            {{ entry.score }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Question Breakdown -->
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 flex flex-col">
                        <div class="p-5 border-b border-gray-100 dark:border-gray-800">
                            <h3 class="font-display text-lg font-bold text-gray-900 dark:text-white">{{ t('host.question_analysis') }}</h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-800 overflow-y-auto max-h-[500px] p-5 space-y-4">
                            <div v-for="(q, idx) in questions" :key="q.id" class="pt-4 first:pt-0">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <span class="text-xs font-bold text-gray-400">Q{{ idx + 1 }}</span>
                                    <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                        {{ q.type === 'multiple_choice' ? t('question.multiple_choice') : t('question.true_false') }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 leading-snug">{{ q.question_text }}</h4>
                                
                                <!-- Correct rate bar -->
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-gray-500 dark:text-gray-400">{{ t('host.correct_pct') }}</span>
                                        <span class="font-bold text-green-600 dark:text-green-400">
                                            {{ getCorrectPercentage(q.correct_count, leaderboard.length) }}%
                                        </span>
                                    </div>
                                    <div class="h-2 bg-gray-100 rounded-full dark:bg-gray-800 overflow-hidden">
                                        <div
                                            class="h-full bg-green-500 rounded-full"
                                            :style="{ width: getCorrectPercentage(q.correct_count, leaderboard.length) + '%' }"
                                        ></div>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] text-gray-500 dark:text-gray-400 pt-1">
                                        <span class="text-green-600 dark:text-green-400 font-medium font-semibold">✓ {{ q.correct_count }} {{ t('host.correct') }}</span>
                                        <span class="text-red-500 dark:text-red-400 font-medium font-semibold">✗ {{ q.incorrect_count }} {{ t('host.wrong') }}</span>
                                        <span>&middot; ∅ {{ q.no_answer_count }} {{ t('host.no_answer') || 'No answer' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
