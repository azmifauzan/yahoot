<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    quizzes: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');

function applyFilters() {
    router.get(route('admin.quizzes.index'), {
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

function confirmDelete(quiz) {
    const msg = quiz.deleted_at
        ? `Permanently delete "${quiz.title}"? This cannot be undone.`
        : `Delete "${quiz.title}"? It will be moved to trash.`;
    if (confirm(msg)) {
        router.delete(route('admin.quizzes.destroy', quiz.id));
    }
}

function restoreQuiz(quiz) {
    if (confirm(`Restore "${quiz.title}"?`)) {
        router.post(route('admin.quizzes.restore', quiz.id));
    }
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <AppLayout title="Quiz Management">
        <Head title="Quiz Management" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Quiz Management</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        <Link :href="route('admin.dashboard')" class="hover:text-gray-700 dark:hover:text-gray-300">Admin</Link>
                        / Quizzes
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-3 mb-6">
                <input
                    v-model="search"
                    @input="onSearchInput"
                    type="text"
                    placeholder="Search by title..."
                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-sm focus:ring-purple-500 focus:border-purple-500"
                />
                <div class="flex gap-2 flex-wrap">
                    <button v-for="status in ['all', 'published', 'draft', 'trashed']" :key="status"
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
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Title</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Creator</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Questions</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Visibility</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Created</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="quiz in quizzes.data" :key="quiz.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50" :class="{ 'opacity-60': quiz.deleted_at }">
                                <td class="px-5 py-3">
                                    <Link :href="route('admin.quizzes.show', quiz.id)" class="font-medium text-gray-900 dark:text-white hover:text-purple-600 dark:hover:text-purple-400">
                                        {{ quiz.title }}
                                    </Link>
                                </td>
                                <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ quiz.user?.name || '—' }}</td>
                                <td class="px-5 py-3 text-gray-600 dark:text-gray-300">{{ quiz.questions_count }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full" :class="quiz.visibility === 'public' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'">
                                        {{ quiz.visibility }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <span v-if="quiz.deleted_at" class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">Trashed</span>
                                    <span v-else-if="quiz.is_published" class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Published</span>
                                    <span v-else class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">Draft</span>
                                </td>
                                <td class="px-5 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ formatDate(quiz.created_at) }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-2">
                                        <Link :href="route('admin.quizzes.show', quiz.id)" class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">View</Link>
                                        <button v-if="quiz.deleted_at" @click="restoreQuiz(quiz)" class="text-xs text-green-600 hover:text-green-800 dark:text-green-400">Restore</button>
                                        <button @click="confirmDelete(quiz)" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400">
                                            {{ quiz.deleted_at ? 'Permanently Delete' : 'Delete' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!quizzes.data.length">
                                <td colspan="7" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No quizzes found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="quizzes.last_page > 1" class="px-5 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Showing {{ quizzes.from }}-{{ quizzes.to }} of {{ quizzes.total }}
                    </p>
                    <div class="flex gap-1">
                        <Link v-for="link in quizzes.links" :key="link.label"
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
