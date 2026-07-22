<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/UI/Icon.vue';

const { t } = useI18n();

const props = defineProps({
    progress: { type: Object, required: true },
    badges: { type: Array, default: () => [] },
    earnedCount: { type: Number, default: 0 },
});

const games = computed(() => props.progress.games ?? []);
const totals = computed(() => props.progress.totals ?? {});

// Build an SVG polyline from a numeric series scaled into a 100x40 viewBox.
function buildPath(values) {
    if (values.length === 0) return '';
    if (values.length === 1) return `0,20 100,20`;
    const max = Math.max(...values, 1);
    const min = Math.min(...values, 0);
    const range = max - min || 1;
    return values
        .map((v, i) => {
            const x = (i / (values.length - 1)) * 100;
            const y = 40 - ((v - min) / range) * 38 - 1;
            return `${x.toFixed(2)},${y.toFixed(2)}`;
        })
        .join(' ');
}

const scorePath = computed(() => buildPath(games.value.map((g) => g.score)));
const accuracyPath = computed(() => buildPath(games.value.map((g) => g.accuracy)));
</script>

<template>
    <Head :title="t('progress.title')" />

    <AppLayout :title="t('progress.title')">
        <template #header>
            <h2 class="font-display text-2xl font-black tracking-tight text-gray-900 dark:text-white">{{ t('progress.title') }}</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ t('progress.subtitle') }}</p>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Totals -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <div class="group rounded-[1.4rem] border border-white bg-white/90 p-5 shadow-lg shadow-primary-950/5 transition hover:-translate-y-1 dark:border-white/10 dark:bg-gray-900/90">
                        <Icon name="play" class="mb-4 h-6 w-6 text-primary-600 dark:text-primary-400" />
                        <p class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('progress.games_played') }}</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ totals.games_played ?? 0 }}</p>
                    </div>
                    <div class="group rounded-[1.4rem] border border-white bg-white/90 p-5 shadow-lg shadow-primary-950/5 transition hover:-translate-y-1 dark:border-white/10 dark:bg-gray-900/90">
                        <Icon name="trophy" class="mb-4 h-6 w-6 text-amber-500" />
                        <p class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('progress.games_won') }}</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ totals.games_won ?? 0 }}</p>
                    </div>
                    <div class="group rounded-[1.4rem] border border-white bg-white/90 p-5 shadow-lg shadow-primary-950/5 transition hover:-translate-y-1 dark:border-white/10 dark:bg-gray-900/90">
                        <Icon name="bolt" class="mb-4 h-6 w-6 text-primary-600 dark:text-primary-400" />
                        <p class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('progress.total_xp') }}</p>
                        <p class="mt-2 text-3xl font-bold text-primary-600 dark:text-primary-400">{{ (totals.total_xp ?? 0).toLocaleString() }}</p>
                    </div>
                    <div class="group rounded-[1.4rem] border border-white bg-white/90 p-5 shadow-lg shadow-primary-950/5 transition hover:-translate-y-1 dark:border-white/10 dark:bg-gray-900/90">
                        <Icon name="accuracy" class="mb-4 h-6 w-6 text-emerald-500" />
                        <p class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('progress.avg_accuracy') }}</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ totals.avg_accuracy ?? 0 }}%</p>
                    </div>
                </div>

                <!-- Trends -->
                <div v-if="games.length" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="rounded-[1.4rem] border border-white bg-white/90 p-5 shadow-lg shadow-primary-950/5 dark:border-white/10 dark:bg-gray-900/90">
                        <h3 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-white">{{ t('progress.score_trend') }}</h3>
                        <svg viewBox="0 0 100 40" preserveAspectRatio="none" class="h-32 w-full">
                            <polyline :points="scorePath" fill="none" stroke="#6366f1" stroke-width="1.5" vector-effect="non-scaling-stroke" />
                        </svg>
                    </div>
                    <div class="rounded-[1.4rem] border border-white bg-white/90 p-5 shadow-lg shadow-primary-950/5 dark:border-white/10 dark:bg-gray-900/90">
                        <h3 class="mb-3 font-display text-sm font-bold text-gray-900 dark:text-white">{{ t('progress.accuracy_trend') }}</h3>
                        <svg viewBox="0 0 100 40" preserveAspectRatio="none" class="h-32 w-full">
                            <polyline :points="accuracyPath" fill="none" stroke="#22c55e" stroke-width="1.5" vector-effect="non-scaling-stroke" />
                        </svg>
                    </div>
                </div>

                <!-- Achievements -->
                <div class="rounded-[1.4rem] border border-white bg-white/90 p-5 shadow-lg shadow-primary-950/5 dark:border-white/10 dark:bg-gray-900/90">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-display text-lg font-bold text-gray-900 dark:text-white">{{ t('badges.title') }}</h3>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ t('badges.progress', { earned: earnedCount, total: badges.length }) }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        <div
                            v-for="badge in badges"
                            :key="badge.key"
                            class="flex items-center gap-3 rounded-xl border p-3 transition"
                            :class="badge.earned
                                ? 'border-amber-200 bg-amber-50 dark:border-amber-900/40 dark:bg-amber-900/10'
                                : 'border-gray-100 bg-gray-50 opacity-60 dark:border-gray-800 dark:bg-gray-800/40'"
                        >
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" :class="badge.earned ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-gray-200 text-gray-400 dark:bg-gray-700'">
                                <Icon :name="badge.earned ? badge.icon : 'lock'" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ t(`badges.${badge.key}.name`) }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ t(`badges.${badge.key}.desc`) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent games -->
                <div class="overflow-x-auto rounded-[1.4rem] border border-white bg-white/90 shadow-lg shadow-primary-950/5 dark:border-white/10 dark:bg-gray-900/90">
                    <div class="border-b border-gray-100 p-5 dark:border-gray-800">
                        <h3 class="font-display text-lg font-bold text-gray-900 dark:text-white">{{ t('progress.recent_games') }}</h3>
                    </div>
                    <div v-if="games.length === 0" class="p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        {{ t('progress.empty') }}
                    </div>
                    <table v-else class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400">
                                <th class="px-4 py-3">{{ t('progress.quiz') }}</th>
                                <th class="px-4 py-3">{{ t('progress.date') }}</th>
                                <th class="px-4 py-3 text-center">{{ t('progress.accuracy') }}</th>
                                <th class="px-4 py-3 text-right">{{ t('progress.score') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="(g, idx) in [...games].reverse()" :key="idx" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ g.quiz_title ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ g.played_at ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ g.accuracy }}%</td>
                                <td class="px-4 py-3 text-right font-extrabold text-primary-600 dark:text-primary-400">{{ g.score }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
