<script setup>
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/UI/Icon.vue';

const { t } = useI18n();

defineProps({
    topPlayers: { type: Array, default: () => [] },
    myRank: { type: Object, default: null },
    panel: { type: Boolean, default: false },
});
</script>

<template>
    <Head :title="t('leaderboard.title')" />

    <component :is="panel ? AppLayout : GuestLayout" :title="t('leaderboard.title')">
        <template #header>
            <div v-if="panel">
                <h2 class="font-display text-2xl font-black tracking-tight text-gray-900 dark:text-white">{{ t('leaderboard.title') }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('leaderboard.subtitle') }}</p>
            </div>
        </template>

        <section v-if="!panel" class="relative overflow-hidden bg-gray-950 pb-32 pt-20 text-white sm:pt-24">
            <div class="absolute left-1/2 top-0 h-80 w-80 -translate-x-1/2 rounded-full bg-accent-yellow/15 blur-3xl"></div>
            <div class="absolute -left-20 bottom-0 h-64 w-64 rounded-full bg-primary-600/30 blur-3xl"></div>
            <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6">
                <span class="mb-5 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-accent-yellow text-gray-950 shadow-xl shadow-accent-yellow/20 transition hover:-rotate-6 hover:scale-110"><Icon name="trophy" class="h-8 w-8" /></span>
                <h1 class="font-display text-5xl font-black tracking-[-0.03em] sm:text-6xl">
                    {{ t('leaderboard.title') }}
                </h1>
                <p class="mx-auto mt-5 max-w-xl text-lg text-gray-300">{{ t('leaderboard.subtitle') }}</p>
            </div>
        </section>

        <div class="mx-auto max-w-5xl px-4 sm:px-6" :class="panel ? 'py-8' : '-mt-20 pb-20'">
            <div v-if="topPlayers.length" class="relative z-10 mb-8 grid gap-4 sm:grid-cols-3">
                <div
                    v-for="entry in topPlayers.slice(0, 3)"
                    :key="entry.user_id"
                    class="group rounded-[1.6rem] border border-white bg-white/95 p-6 text-center shadow-2xl shadow-primary-950/10 backdrop-blur transition duration-300 hover:-translate-y-2 dark:border-white/10 dark:bg-gray-900/95"
                    :class="entry.rank === 1 ? 'sm:-translate-y-5 sm:hover:-translate-y-7' : ''"
                >
                    <div class="relative mx-auto mb-4 h-20 w-20">
                        <div class="absolute -inset-1 rounded-full bg-gradient-to-br from-accent-yellow via-primary-400 to-accent-blue opacity-80 blur-sm transition group-hover:opacity-100"></div>
                        <img v-if="entry.avatar" :src="entry.avatar" :alt="entry.name" class="relative h-20 w-20 rounded-full border-4 border-white object-cover dark:border-gray-900" />
                        <div v-else class="relative flex h-20 w-20 items-center justify-center rounded-full border-4 border-white bg-primary-100 font-display text-2xl font-black text-primary-700 dark:border-gray-900 dark:bg-primary-950 dark:text-primary-300">
                            {{ entry.name.charAt(0).toUpperCase() }}
                        </div>
                        <span class="absolute -bottom-2 left-1/2 flex h-8 min-w-8 -translate-x-1/2 items-center justify-center rounded-full bg-gray-950 px-2 text-sm font-black text-white shadow-lg dark:bg-white dark:text-gray-950">
                            {{ entry.rank }}
                        </span>
                    </div>
                    <h2 class="truncate font-display text-xl font-extrabold text-gray-900 dark:text-white">{{ entry.name }}</h2>
                    <p class="mt-1 text-2xl font-black text-primary-600 dark:text-primary-400">{{ entry.total_xp.toLocaleString() }} XP</p>
                    <p class="mt-2 text-xs font-semibold text-gray-400">{{ entry.games_won }} {{ t('leaderboard.games_won') }}</p>
                </div>
            </div>

            <!-- Your rank card -->
            <div
                v-if="myRank"
                class="mb-6 flex flex-col gap-4 rounded-[1.4rem] border border-primary-200 bg-primary-50/90 p-5 shadow-lg shadow-primary-900/5 sm:flex-row sm:items-center sm:justify-between dark:border-primary-900/40 dark:bg-primary-900/20"
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
            <div class="overflow-x-auto rounded-[1.6rem] border border-white bg-white/90 shadow-xl shadow-primary-950/5 backdrop-blur dark:border-white/10 dark:bg-gray-900/90">
                <div v-if="topPlayers.length === 0" class="p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ t('leaderboard.empty') }}
                </div>
                <table v-else class="w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/80 text-xs font-bold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-gray-800/80 dark:text-gray-400">
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
                            class="transition hover:bg-primary-50/60 dark:hover:bg-primary-950/20"
                            :class="myRank && myRank.rank === entry.rank ? 'bg-primary-50/50 dark:bg-primary-900/10' : ''"
                        >
                            <td class="px-4 py-3 text-center text-lg font-extrabold text-gray-900 dark:text-white">
                                {{ entry.rank }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img
                                        v-if="entry.avatar"
                                        :src="entry.avatar"
                                        :alt="entry.name"
                                        class="h-10 w-10 rounded-full border-2 border-white object-cover shadow dark:border-gray-700"
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
    </component>
</template>
