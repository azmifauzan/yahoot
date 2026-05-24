<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';

const { t } = useI18n();

const props = defineProps({
    stats: Object,
    activityChart: Array,
    recentGames: Array,
});

const statCards = [
    { label: t('admin.total_users'), value: props.stats.total_users, color: 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400' },
    { label: t('admin.total_quizzes'), value: props.stats.active_quizzes, color: 'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400' },
    { label: t('admin.total_games'), value: props.stats.total_games, color: 'bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400' },
    { label: t('admin.games_today'), value: props.stats.games_today, color: 'bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400' },
    { label: t('admin.total_players'), value: props.stats.total_players, color: 'bg-pink-50 text-pink-600 dark:bg-pink-900/20 dark:text-pink-400' },
    { label: t('admin.deleted_quizzes'), value: props.stats.deleted_quizzes, color: 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400' },
];

function statusBadge(status) {
    const map = {
        waiting: 'bg-yellow-100 text-yellow-700',
        playing: 'bg-green-100 text-green-700',
        reviewing: 'bg-blue-100 text-blue-700',
        finished: 'bg-gray-100 text-gray-600',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600';
}
</script>

<template>
    <AppLayout :title="t('admin.dashboard')">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">{{ t('admin.dashboard') }}</h2>
                <nav class="flex items-center gap-4 text-sm">
                    <Link :href="route('admin.users.index')" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">{{ t('admin.users') }}</Link>
                    <Link :href="route('admin.quizzes.index')" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">{{ t('admin.quizzes') }}</Link>
                    <Link :href="route('admin.games.index')" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">{{ t('admin.games') }}</Link>
                    <Link :href="route('admin.settings.index')" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">{{ t('admin.settings') }}</Link>
                </nav>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Stats grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div
                        v-for="card in statCards"
                        :key="card.label"
                        :class="['rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-800', card.color]"
                    >
                        <p class="text-xs font-medium opacity-70">{{ card.label }}</p>
                        <p class="text-2xl font-bold mt-1">{{ card.value }}</p>
                    </div>
                </div>

                <!-- Recent games -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('admin.recent_games') }}</h3>
                        <Link :href="route('admin.games.index')" class="text-sm text-indigo-600 dark:text-indigo-400">{{ t('admin.view_all') }}</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                                    <th class="px-6 py-3 font-medium">{{ t('admin.game_code') }}</th>
                                    <th class="px-6 py-3 font-medium">{{ t('admin.quiz') }}</th>
                                    <th class="px-6 py-3 font-medium">{{ t('admin.host') }}</th>
                                    <th class="px-6 py-3 font-medium">{{ t('admin.players') }}</th>
                                    <th class="px-6 py-3 font-medium">{{ t('admin.status') }}</th>
                                    <th class="px-6 py-3 font-medium">{{ t('admin.created_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="game in recentGames"
                                    :key="game.id"
                                    class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50 dark:hover:bg-gray-800/30"
                                >
                                    <td class="px-6 py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                        <Link :href="route('admin.games.show', game.id)">{{ game.game_code }}</Link>
                                    </td>
                                    <td class="px-6 py-3 truncate max-w-[160px]">{{ game.quiz_title }}</td>
                                    <td class="px-6 py-3">{{ game.host_name }}</td>
                                    <td class="px-6 py-3">{{ game.players_count }}</td>
                                    <td class="px-6 py-3">
                                        <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusBadge(game.status)]">
                                            {{ game.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-500">{{ new Date(game.created_at).toLocaleDateString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
