<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { confirm, toast } = useSwal();

const props = defineProps({
    quizzes: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const filter = ref(props.filters.filter || '');

let searchTimeout = null;
function onSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
}

function applyFilters() {
    router.get(route('admin.quizzes.index'), {
        search: search.value || undefined,
        filter: filter.value || undefined,
    }, { preserveState: true, replace: true });
}

function confirmDelete(quiz) {
    confirm({
        title: t('admin.force_delete_quiz'),
        text: t('admin.confirm_force_delete'),
        confirmText: t('admin.force_delete'),
        cancelText: t('common.cancel'),
        icon: 'warning',
    }).then(result => {
        if (result.isConfirmed) {
            router.delete(route('admin.quizzes.destroy', quiz.id), {
                onSuccess: () => toast(t('admin.quiz_deleted')),
            });
        }
    });
}

function restore(quiz) {
    router.post(route('admin.quizzes.restore', quiz.id), {}, {
        onSuccess: () => toast(t('admin.quiz_restored')),
    });
}
</script>

<template>
    <AppLayout :title="t('admin.quizzes')">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">{{ t('admin.quizzes') }}</h2>
                <Link :href="route('admin.dashboard')" class="text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400">← {{ t('admin.dashboard') }}</Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4">
                <!-- Filters -->
                <div class="flex gap-3 flex-wrap">
                    <input
                        v-model="search"
                        @input="onSearch"
                        type="text"
                        :placeholder="t('dashboard.search_placeholder')"
                        class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200"
                    />
                    <select
                        v-model="filter"
                        @change="applyFilters"
                        class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200"
                    >
                        <option value="">{{ t('dashboard.filter_all') }}</option>
                        <option value="published">{{ t('dashboard.filter_published') }}</option>
                        <option value="draft">{{ t('dashboard.filter_draft') }}</option>
                        <option value="trashed">{{ t('admin.filter_trashed') }}</option>
                    </select>
                </div>

                <!-- Table -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                                <th class="px-6 py-3 font-medium">{{ t('admin.title') }}</th>
                                <th class="px-6 py-3 font-medium">{{ t('admin.owner') }}</th>
                                <th class="px-6 py-3 font-medium text-center">{{ t('admin.questions') }}</th>
                                <th class="px-6 py-3 font-medium text-center">{{ t('admin.status') }}</th>
                                <th class="px-6 py-3 font-medium">{{ t('admin.created_at') }}</th>
                                <th class="px-6 py-3 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="quiz in quizzes.data"
                                :key="quiz.id"
                                :class="['border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50 dark:hover:bg-gray-800/30', quiz.deleted_at && 'opacity-60']"
                            >
                                <td class="px-6 py-3">
                                    <Link :href="route('admin.quizzes.show', quiz.id)" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ quiz.title }}
                                    </Link>
                                </td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ quiz.user?.name }}</td>
                                <td class="px-6 py-3 text-center">{{ quiz.questions_count }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span v-if="quiz.deleted_at" class="bg-red-100 text-red-600 text-xs rounded-full px-2 py-0.5">{{ t('admin.deleted') }}</span>
                                    <span v-else-if="quiz.is_published" class="bg-green-100 text-green-600 text-xs rounded-full px-2 py-0.5">{{ t('dashboard.published') }}</span>
                                    <span v-else class="bg-gray-100 text-gray-600 text-xs rounded-full px-2 py-0.5">{{ t('dashboard.draft') }}</span>
                                </td>
                                <td class="px-6 py-3 text-gray-500">{{ new Date(quiz.created_at).toLocaleDateString() }}</td>
                                <td class="px-6 py-3 flex gap-2">
                                    <button v-if="quiz.deleted_at" @click="restore(quiz)" class="text-green-600 hover:text-green-800 text-xs">{{ t('admin.restore') }}</button>
                                    <button @click="confirmDelete(quiz)" class="text-red-500 hover:text-red-700 text-xs">{{ t('admin.force_delete') }}</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="quizzes.last_page > 1" class="flex justify-center gap-2">
                    <Link
                        v-for="link in quizzes.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 dark:bg-gray-800 dark:text-gray-300', !link.url && 'opacity-50 cursor-not-allowed']"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
