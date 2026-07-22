<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import AvatarDisplay from '@/Components/Avatar/AvatarDisplay.vue';
import Icon from '@/Components/UI/Icon.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { confirm } = useSwal();

defineProps({
    quiz: Object,
    sessions: Array,
});

function formatDate(value) {
    return new Date(value).toLocaleString();
}

function cancelGame(session) {
    confirm({
        title: t('host.cancel_game'),
        text: t('host.confirm_cancel'),
        confirmText: t('common.yes') || 'Ya',
        cancelText: t('common.cancel') || 'Batal',
        icon: 'warning',
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('game.cancel', session.id));
        }
    });
}
</script>

<template>
    <Head :title="`${quiz.title} - ${t('host.history_title')}`" />

    <AppLayout :title="t('host.history_title')">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-display text-2xl font-black leading-tight tracking-tight text-gray-900 dark:text-white">
                    {{ t('host.history_title') }} &middot; {{ quiz.title }}
                </h2>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('quizzes.analytics', quiz.id)"
                        class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm border border-gray-200 transition hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-800 dark:hover:bg-gray-800"
                    >
                        <Icon name="chart" class="h-4 w-4" /> {{ t('host.view_analytics') }}
                    </Link>
                    <Link
                        :href="route('quizzes.edit', quiz.id)"
                        class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm border border-gray-200 transition hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-800 dark:hover:bg-gray-800"
                    >
                        {{ t('quiz.back') }}
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <!-- Empty state -->
                <div v-if="sessions.length === 0" class="flex flex-col items-center justify-center rounded-[1.5rem] border border-white bg-white/90 py-16 shadow-xl shadow-primary-950/5 dark:border-white/10 dark:bg-gray-900/90">
                    <div class="mb-4 rounded-full bg-primary-100 dark:bg-primary-900/30 p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14" />
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">{{ t('host.no_games_yet') }}</p>
                </div>

                <!-- Sessions list -->
                <div v-else class="space-y-3">
                    <div
                        v-for="(session, index) in sessions"
                        :key="session.id"
                        class="flex flex-col gap-4 rounded-[1.4rem] border border-white bg-white/90 p-4 shadow-lg shadow-primary-950/5 transition hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-gray-900/90 sm:flex-row sm:items-center animate-slide-in-up"
                        :style="{ animationDelay: `${Math.min(index * 0.05, 0.4)}s` }"
                    >
                        <Link
                            :href="session.status === 'finished' ? route('game.stats', session.id) : route('game.host', session.id)"
                            class="flex flex-col sm:flex-row flex-1 sm:items-center gap-4 min-w-0 w-full"
                        >
                            <div class="flex-1 min-w-0 w-full">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    <template v-if="session.status === 'finished'">
                                        {{ t('host.played_on') }} {{ formatDate(session.finished_at) }}
                                    </template>
                                    <template v-else>
                                        <span class="mr-1 inline-block h-2 w-2 rounded-full bg-green-500"></span>{{ t('host.in_progress') }}
                                    </template>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ t('host.game_code') }}: {{ session.game_code }}
                                    &middot;
                                    {{ t('host.players_played', { count: session.players_count }) }}
                                </p>
                            </div>
                            
                            <div class="flex items-center justify-between w-full sm:w-auto gap-4">
                                <!-- Winner -->
                                <div v-if="session.winner" class="flex items-center gap-2">
                                    <AvatarDisplay :name="session.winner.avatar" :size="32" />
                                    <div class="text-left sm:text-right">
                                        <p class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400"><Icon name="trophy" class="h-3.5 w-3.5 text-amber-500" /> {{ t('host.winner') }}</p>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate max-w-[120px]">{{ session.winner.nickname }}</p>
                                    </div>
                                </div>
                                
                                <span class="rounded-lg bg-primary-50 px-3 py-1.5 text-xs font-medium text-primary-600 dark:bg-primary-900/30 dark:text-primary-400 whitespace-nowrap hidden sm:inline-block">
                                    {{ session.status === 'finished' ? t('host.view_results') : t('host.resume_game') }} →
                                </span>
                            </div>
                        </Link>
                        
                        <div class="flex items-center gap-2 w-full sm:w-auto justify-end mt-2 sm:mt-0">
                            <Link
                                :href="session.status === 'finished' ? route('game.stats', session.id) : route('game.host', session.id)"
                                class="rounded-lg bg-primary-50 px-3 py-1.5 text-xs font-medium text-primary-600 dark:bg-primary-900/30 dark:text-primary-400 whitespace-nowrap sm:hidden"
                            >
                                {{ session.status === 'finished' ? t('host.view_results') : t('host.resume_game') }} →
                            </Link>

                            <!-- Cancel button for in-progress sessions -->
                            <button
                                v-if="session.status !== 'finished'"
                                @click="cancelGame(session)"
                                :title="t('host.cancel_game')"
                                class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 whitespace-nowrap"
                            >
                                <Icon name="x" class="inline h-3.5 w-3.5" /> <span class="hidden sm:inline">{{ t('host.cancel_game') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
