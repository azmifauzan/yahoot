<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    stats: Object,
    dailyGames: Array,
    recentGames: Array,
});

const statCards = [
    { key: 'total_users', label: 'Total Users', icon: '👥', color: 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' },
    { key: 'total_quizzes', label: 'Total Quizzes', icon: '📝', color: 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' },
    { key: 'total_game_sessions', label: 'Total Games', icon: '🎮', color: 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' },
    { key: 'games_today', label: 'Games Today', icon: '📅', color: 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' },
];

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function statusBadge(status) {
    const map = {
        waiting: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        playing: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        reviewing: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        finished: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return map[status] || 'bg-gray-100 text-gray-800';
}
</script>

<template>
    <AppLayout title="Admin Dashboard">
        <Head title="Admin Dashboard" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Overview statistik platform</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('admin.users.index')" class="rounded-lg px-3 py-2 text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                        Users
                    </Link>
                    <Link :href="route('admin.quizzes.index')" class="rounded-lg px-3 py-2 text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                        Quizzes
                    </Link>
                    <Link :href="route('admin.games.index')" class="rounded-lg px-3 py-2 text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                        Games
                    </Link>
                    <Link :href="route('admin.settings')" class="rounded-lg px-3 py-2 text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                        Settings
                    </Link>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div v-for="card in statCards" :key="card.key" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ card.icon }}</span>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ card.label }}</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats[card.key] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Extra Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Published / Draft</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.total_published }} / {{ stats.total_draft }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Games This Month</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.games_this_month }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Guest Players</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.total_guest_players }}</p>
                </div>
            </div>

            <!-- Daily Games Chart -->
            <div v-if="dailyGames.length" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5 shadow-sm mb-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Game Activity (30 Days)</h2>
                <div class="flex items-end gap-1 h-32">
                    <div v-for="day in dailyGames" :key="day.date" class="flex-1 flex flex-col items-center">
                        <div
                            class="w-full rounded-t bg-purple-500 dark:bg-purple-400 transition-all duration-300"
                            :style="{ height: `${Math.max((day.count / Math.max(...dailyGames.map(d => d.count))) * 100, 4)}%` }"
                            :title="`${day.date}: ${day.count} games`"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Recent Games -->
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Games</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Code</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Quiz</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Host</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Players</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="game in recentGames" :key="game.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-5 py-3 font-mono text-gray-900 dark:text-white">{{ game.game_code }}</td>
                                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ game.quiz?.title || '—' }}</td>
                                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ game.host?.name || '—' }}</td>
                                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ game.players_count }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full" :class="statusBadge(game.status)">
                                        {{ game.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ formatDate(game.created_at) }}</td>
                            </tr>
                            <tr v-if="!recentGames.length">
                                <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No games yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
