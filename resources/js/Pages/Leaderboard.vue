<script setup>
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const { t } = useI18n();

defineProps({
    topPlayers: { type: Array, default: () => [] },
    myRank: { type: Object, default: null },
});

const medals = ['🥇', '🥈', '🥉'];
</script>

<template>
    <Head :title="t('leaderboard.title')" />

    <GuestLayout :title="t('leaderboard.title')">
        <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
            <div class="mb-8 text-center">
                <h1 class="font-display text-3xl font-extrabold text-gray-900 dark:text-white">
                    🏆 {{ t('leaderboard.title') }}
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t('leaderboard.subtitle') }}</p>
            </div>

            <!-- Your rank card -->
            <div
                v-if="myRank"
                class="mb-6 flex items-center justify-between rounded-2xl border border-primary-200 bg-primary-50 p-4 dark:border-primary-900/40 dark:bg-primary-900/20"
            >
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-600 text-lg font-extrabold text-white">
                        {{ myRank.rank ?? '–' }}
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-300">{{ t('leaderboard.your_rank') }}</p>
                        <p class="font-bold text-gray-900 dark:text-white">{{ myRank.name }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xl font-extrabold text-primary-700 dark:text-primary-300">{{ myRank.total_xp.toLocaleString() }} XP</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ myRank.games_won }} {{ t('leaderboard.games_won') }} · {{ myRank.games_played }} {{ t('leaderboard.games_played') }}
                    </p>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div v-if="topPlayers.length === 0" class="p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ t('leaderboard.empty') }}
                </div>
                <table v-else class="w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400">
                            <th class="w-16 px-4 py-3 text-center">{{ t('leaderboard.rank') }}</th>
                            <th class="px-4 py-3">{{ t('leaderboard.player') }}</th>
                            <th class="px-4 py-3 text-center">{{ t('leaderboard.games_won') }}</th>
                            <th class="px-4 py-3 text-right">{{ t('leaderboard.total_xp') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                        <tr
                            v-for="entry in topPlayers"
                            :key="entry.user_id"
                            class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30"
                            :class="myRank && myRank.rank === entry.rank ? 'bg-primary-50/50 dark:bg-primary-900/10' : ''"
                        >
                            <td class="px-4 py-3 text-center text-lg font-extrabold text-gray-900 dark:text-white">
                                {{ entry.rank <= 3 ? medals[entry.rank - 1] : entry.rank }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img
                                        v-if="entry.avatar"
                                        :src="entry.avatar"
                                        :alt="entry.name"
                                        class="h-9 w-9 rounded-full object-cover"
                                    />
                                    <span class="font-bold text-gray-900 dark:text-white">{{ entry.name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ entry.games_won }}</td>
                            <td class="px-4 py-3 text-right font-extrabold text-primary-600 dark:text-primary-400">
                                {{ entry.total_xp.toLocaleString() }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </GuestLayout>
</template>
