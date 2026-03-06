<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    games: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

function applyFilters() {
    router.get(route('admin.games.index'), {
        search: search.value || undefined,
        status: statusFilter.value !== 'all' ? statusFilter.value : undefined,
    }, { preserveState: true, replace: true });
}

let searchTimeout = null;
function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
}

function setStatus(status) {
    statusFilter.value = status;
    applyFilters();
}

function confirmDelete(game) {
    if (confirm(`Delete game session "${game.game_code}"? This will also delete all player data.`)) {
        router.delete(route('admin.games.destroy', game.id));
    }
}

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
    <AppLayout title="Game History">
        <Head title="Game History" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Game History</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        <Link :href="route('admin.dashboard')" class="hover:text-gray-700 dark:hover:text-gray-300">Admin</Link>
                        / Games
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-3 mb-6">
                <input
                    v-model="search"
                    @input="onSearchInput"
                    type="text"
                    placeholder="Search by game code or quiz title..."
                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm focus:ring-purple-500 focus:border-purple-500"
                />
                <div class="flex gap-2 flex-wrap">
                    <button v-for="status in ['all', 'waiting', 'playing', 'finished']" :key="status"
                        @click="setStatus(status)"
                        class="px-3 py-2 text-sm rounded-lg font-medium transition capitalize"
                        :class="statusFilter === status ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                    >
                        {{ status }}
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
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
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="game in games.data" :key="game.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-5 py-3 font-mono font-medium text-gray-900 dark:text-white">{{ game.game_code }}</td>
                                <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ game.quiz?.title || '—' }}</td>
                                <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ game.host?.name || '—' }}</td>
                                <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ game.players_count }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full" :class="statusBadge(game.status)">
                                        {{ game.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ formatDate(game.created_at) }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-2">
                                        <Link :href="route('admin.games.show', game.id)" class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">View</Link>
                                        <button @click="confirmDelete(game)" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!games.data.length">
                                <td colspan="7" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No games found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="games.last_page > 1" class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Showing {{ games.from }}-{{ games.to }} of {{ games.total }}
                    </p>
                    <div class="flex gap-1">
                        <Link v-for="link in games.links" :key="link.label"
                            :href="link.url || '#'"
                            class="px-3 py-1 text-sm rounded-lg transition"
                            :class="link.active ? 'bg-purple-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                            v-html="link.label"
                            :preserve-state="true"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
