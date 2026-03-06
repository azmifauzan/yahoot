<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    gameSession: Object,
    playerStats: Array,
});

function confirmDelete() {
    if (confirm(`Delete game session "${props.gameSession.game_code}"? This cannot be undone.`)) {
        router.delete(route('admin.games.destroy', props.gameSession.id));
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
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
    <AppLayout title="Game Detail">
        <Head :title="`Game: ${gameSession.game_code}`" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Breadcrumb -->
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                <Link :href="route('admin.dashboard')" class="hover:text-gray-700 dark:hover:text-gray-300">Admin</Link>
                / <Link :href="route('admin.games.index')" class="hover:text-gray-700 dark:hover:text-gray-300">Games</Link>
                / {{ gameSession.game_code }}
            </p>

            <!-- Game Info -->
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-6 mb-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white font-mono">{{ gameSession.game_code }}</h1>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ gameSession.quiz?.title || '—' }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full" :class="statusBadge(gameSession.status)">
                            {{ gameSession.status }}
                        </span>
                        <button @click="confirmDelete" class="rounded-lg px-3 py-2 text-sm font-medium bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 transition">
                            Delete
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Host</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ gameSession.host?.name || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Players</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ gameSession.players?.length || 0 }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Started</p>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ formatDate(gameSession.started_at) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Finished</p>
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ formatDate(gameSession.finished_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Players -->
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Players ({{ playerStats.length }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Rank</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Player</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Score</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Correct</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Avg Time</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Connected</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="(player, index) in playerStats" :key="player.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-5 py-3 text-gray-900 dark:text-white font-medium">
                                    <span v-if="index === 0">🥇</span>
                                    <span v-else-if="index === 1">🥈</span>
                                    <span v-else-if="index === 2">🥉</span>
                                    <span v-else>{{ index + 1 }}</span>
                                </td>
                                <td class="px-5 py-3 text-gray-900 dark:text-white font-medium">{{ player.nickname }}</td>
                                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ player.score }}</td>
                                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ player.correct_answers }}/{{ player.total_questions }}</td>
                                <td class="px-5 py-3 text-gray-700 dark:text-gray-300">{{ Math.round(player.avg_time) }}ms</td>
                                <td class="px-5 py-3">
                                    <span v-if="player.is_connected" class="text-green-500">●</span>
                                    <span v-else class="text-gray-400">●</span>
                                </td>
                            </tr>
                            <tr v-if="!playerStats.length">
                                <td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No players</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
