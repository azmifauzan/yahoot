<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { confirm, toast } = useSwal();

const props = defineProps({
    games: Object,
    filters: Object,
});

const status = ref(props.filters.status || '');
const from = ref(props.filters.from || '');
const to = ref(props.filters.to || '');

function applyFilters() {
    router.get(route('admin.games.index'), {
        status: status.value || undefined,
        from: from.value || undefined,
        to: to.value || undefined,
    }, { preserveState: true, replace: true });
}

function confirmDelete(game) {
    confirm({
        title: t('admin.delete_game'),
        text: t('admin.confirm_delete_game'),
        confirmText: t('common.delete'),
        cancelText: t('common.cancel'),
        icon: 'warning',
    }).then(result => {
        if (result.isConfirmed) {
            router.delete(route('admin.games.destroy', game.id), {
                onSuccess: () => toast(t('admin.game_deleted')),
            });
        }
    });
}

function statusBadge(s) {
    const map = {
        waiting: 'bg-yellow-100 text-yellow-700',
        playing: 'bg-green-100 text-green-700',
        reviewing: 'bg-blue-100 text-blue-700',
        finished: 'bg-gray-100 text-gray-600',
    };
    return map[s] ?? 'bg-gray-100 text-gray-600';
}
</script>

<template>
    <AppLayout :title="t('admin.games')">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-display text-xl font-bold text-gray-800 dark:text-gray-100">{{ t('admin.games') }}</h2>
                <Link :href="route('admin.dashboard')" class="text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400">← {{ t('admin.dashboard') }}</Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4">
                <!-- Filters -->
                <div class="flex gap-3 flex-wrap items-center">
                    <select v-model="status" @change="applyFilters" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">{{ t('dashboard.filter_all') }}</option>
                        <option value="waiting">{{ t('admin.status_waiting') }}</option>
                        <option value="playing">{{ t('admin.status_playing') }}</option>
                        <option value="finished">{{ t('admin.status_finished') }}</option>
                    </select>
                    <input v-model="from" @change="applyFilters" type="date" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200" />
                    <input v-model="to" @change="applyFilters" type="date" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200" />
                </div>

                <!-- Table -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                                <th class="px-6 py-3 font-medium">{{ t('admin.game_code') }}</th>
                                <th class="px-6 py-3 font-medium">{{ t('admin.quiz') }}</th>
                                <th class="px-6 py-3 font-medium">{{ t('admin.host') }}</th>
                                <th class="px-6 py-3 font-medium text-center">{{ t('admin.players') }}</th>
                                <th class="px-6 py-3 font-medium text-center">{{ t('admin.status') }}</th>
                                <th class="px-6 py-3 font-medium">{{ t('admin.created_at') }}</th>
                                <th class="px-6 py-3 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="game in games.data"
                                :key="game.id"
                                class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50 dark:hover:bg-gray-800/30"
                            >
                                <td class="px-6 py-3 font-mono font-bold text-primary-600 dark:text-primary-400">
                                    <Link :href="route('admin.games.show', game.id)">{{ game.game_code }}</Link>
                                </td>
                                <td class="px-6 py-3 truncate max-w-[160px]">{{ game.quiz?.title }}</td>
                                <td class="px-6 py-3">{{ game.host?.name }}</td>
                                <td class="px-6 py-3 text-center">{{ game.players_count }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusBadge(game.status.value || game.status)]">
                                        {{ game.status.value || game.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-500">{{ new Date(game.created_at).toLocaleDateString() }}</td>
                                <td class="px-6 py-3">
                                    <button @click="confirmDelete(game)" class="text-red-500 hover:text-red-700 text-xs">{{ t('common.delete') }}</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="games.last_page > 1" class="flex justify-center gap-2">
                    <Link
                        v-for="link in games.links"
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
