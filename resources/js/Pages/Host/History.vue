<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import AvatarDisplay from '@/Components/Avatar/AvatarDisplay.vue';

const { t } = useI18n();

defineProps({
    quiz: Object,
    sessions: Array,
});

function formatDate(value) {
    return new Date(value).toLocaleString();
}
</script>

<template>
    <Head :title="`${quiz.title} - ${t('host.history_title')}`" />

    <AppLayout :title="t('host.history_title')">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-display text-xl font-bold leading-tight text-gray-800 dark:text-gray-100">
                    {{ t('host.history_title') }} &middot; {{ quiz.title }}
                </h2>
                <Link
                    :href="route('quizzes.edit', quiz.id)"
                    class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm border border-gray-200 transition hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-800 dark:hover:bg-gray-800"
                >
                    {{ t('quiz.back') }}
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <!-- Empty state -->
                <div v-if="sessions.length === 0" class="flex flex-col items-center justify-center rounded-xl bg-white py-16 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                    <div class="mb-4 rounded-full bg-primary-100 dark:bg-primary-900/30 p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14" />
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">{{ t('host.no_games_yet') }}</p>
                </div>

                <!-- Sessions list -->
                <div v-else class="space-y-3">
                    <Link
                        v-for="(session, index) in sessions"
                        :key="session.id"
                        :href="session.status === 'finished' ? route('game.stats', session.id) : route('game.host', session.id)"
                        class="flex items-center gap-4 rounded-xl bg-white p-4 shadow-sm border border-gray-100 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-primary-500/10 dark:bg-gray-900 dark:border-gray-800 animate-slide-in-up"
                        :style="{ animationDelay: `${Math.min(index * 0.05, 0.4)}s` }"
                    >
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                <template v-if="session.status === 'finished'">
                                    {{ t('host.played_on') }} {{ formatDate(session.finished_at) }}
                                </template>
                                <template v-else>
                                    🟢 {{ t('host.in_progress') }}
                                </template>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ t('host.game_code') }}: {{ session.game_code }}
                                &middot;
                                {{ t('host.players_played', { count: session.players_count }) }}
                            </p>
                        </div>
                        <!-- Winner -->
                        <div v-if="session.winner" class="flex items-center gap-2">
                            <AvatarDisplay :name="session.winner.avatar" :size="32" />
                            <div class="text-right">
                                <p class="text-xs text-gray-500 dark:text-gray-400">🥇 {{ t('host.winner') }}</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate max-w-[120px]">{{ session.winner.nickname }}</p>
                            </div>
                        </div>
                        <span class="rounded-lg bg-primary-50 px-3 py-1.5 text-xs font-medium text-primary-600 dark:bg-primary-900/30 dark:text-primary-400 whitespace-nowrap">
                            {{ session.status === 'finished' ? t('host.view_results') : t('host.resume_game') }} →
                        </span>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
