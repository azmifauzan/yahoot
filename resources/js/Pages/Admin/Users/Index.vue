<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { confirm, toast } = useSwal();

const props = defineProps({
    users: Object,
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
    router.get(route('admin.users.index'), {
        search: search.value || undefined,
        filter: filter.value || undefined,
    }, { preserveState: true, replace: true });
}

function toggleAdmin(user) {
    router.put(route('admin.users.update', user.id), { is_admin: !user.is_admin });
}

function confirmDelete(user) {
    confirm({
        title: t('admin.delete_user'),
        text: t('admin.confirm_delete_user'),
        confirmText: t('common.delete'),
        cancelText: t('common.cancel'),
        icon: 'warning',
    }).then(result => {
        if (result.isConfirmed) {
            router.delete(route('admin.users.destroy', user.id), {
                onSuccess: () => toast(t('admin.user_deleted')),
            });
        }
    });
}
</script>

<template>
    <AppLayout :title="t('admin.users')">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-display text-xl font-bold text-gray-800 dark:text-gray-100">{{ t('admin.users') }}</h2>
                <Link :href="route('admin.dashboard')" class="text-sm text-gray-500 hover:text-primary-600 dark:text-gray-400">← {{ t('admin.dashboard') }}</Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4">
                <!-- Filters -->
                <div class="flex gap-3">
                    <input
                        v-model="search"
                        @input="onSearch"
                        type="text"
                        :placeholder="t('admin.search_users')"
                        class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200"
                    />
                    <select
                        v-model="filter"
                        @change="applyFilters"
                        class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200"
                    >
                        <option value="">{{ t('dashboard.filter_all') }}</option>
                        <option value="admin">{{ t('admin.filter_admin') }}</option>
                        <option value="active">{{ t('admin.filter_active') }}</option>
                    </select>
                </div>

                <!-- Table -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                                <th class="px-6 py-3 font-medium">{{ t('admin.name') }}</th>
                                <th class="px-6 py-3 font-medium">{{ t('admin.email') }}</th>
                                <th class="px-6 py-3 font-medium text-center">{{ t('admin.quizzes') }}</th>
                                <th class="px-6 py-3 font-medium text-center">{{ t('admin.games_played') }}</th>
                                <th class="px-6 py-3 font-medium text-center">{{ t('admin.admin_role') }}</th>
                                <th class="px-6 py-3 font-medium">{{ t('admin.registered_at') }}</th>
                                <th class="px-6 py-3 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50 dark:hover:bg-gray-800/30"
                            >
                                <td class="px-6 py-3">
                                    <Link :href="route('admin.users.show', user.id)" class="font-medium text-primary-600 dark:text-primary-400 hover:underline">
                                        {{ user.name }}
                                    </Link>
                                </td>
                                <td class="px-6 py-3 text-gray-600 dark:text-gray-300">{{ user.email }}</td>
                                <td class="px-6 py-3 text-center">{{ user.quizzes_count }}</td>
                                <td class="px-6 py-3 text-center">{{ user.game_players_count }}</td>
                                <td class="px-6 py-3 text-center">
                                    <button
                                        @click="toggleAdmin(user)"
                                        :class="['rounded-full px-2 py-0.5 text-xs font-medium transition', user.is_admin ? 'bg-primary-100 text-primary-700 hover:bg-primary-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300']"
                                    >
                                        {{ user.is_admin ? t('admin.is_admin') : t('admin.not_admin') }}
                                    </button>
                                </td>
                                <td class="px-6 py-3 text-gray-500">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                                <td class="px-6 py-3">
                                    <button @click="confirmDelete(user)" class="text-red-500 hover:text-red-700 text-xs">{{ t('common.delete') }}</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.last_page > 1" class="flex justify-center gap-2">
                    <Link
                        v-for="link in users.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-primary-600 text-white' : 'bg-white text-gray-600 dark:bg-gray-800 dark:text-gray-300', !link.url && 'opacity-50 cursor-not-allowed']"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
