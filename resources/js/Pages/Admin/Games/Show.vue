<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import AvatarDisplay from '@/Components/Avatar/AvatarDisplay.vue';

const { t } = useI18n();

const props = defineProps({
    gameSession: Object,
    players: Array,
});

function statusBadge(s) {
    const val = s?.value ?? s;
    const map = {
        waiting: 'bg-yellow-100 text-yellow-700',
        playing: 'bg-green-100 text-green-700',
        reviewing: 'bg-blue-100 text-blue-700',
        finished: 'bg-gray-100 text-gray-600',
    };
    return map[val] ?? 'bg-gray-100 text-gray-600';
}
</script>

<template>
    <AppLayout :title="`Game ${gameSession.game_code}`">
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.games.index')" class="text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400">← {{ t('admin.games') }}</Link>
                <h2 class="font-display text-xl font-bold text-gray-800 dark:text-gray-100 font-mono">{{ gameSession.game_code }}</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Meta -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-gray-500">{{ t('admin.quiz') }}</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ gameSession.quiz?.title }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">{{ t('admin.host') }}</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ gameSession.host?.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">{{ t('admin.status') }}</p>
                            <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusBadge(gameSession.status)]">
                                {{ gameSession.status?.value ?? gameSession.status }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">{{ t('admin.created_at') }}</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ new Date(gameSession.created_at).toLocaleDateString() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Players -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('admin.players') }} ({{ players.length }})</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                                <th class="px-6 py-3 font-medium">#</th>
                                <th class="px-6 py-3 font-medium">{{ t('admin.nickname') }}</th>
                                <th class="px-6 py-3 font-medium text-center">{{ t('host.correct') }}</th>
                                <th class="px-6 py-3 font-medium text-center">{{ t('admin.total_answers') }}</th>
                                <th class="px-6 py-3 font-medium text-right">{{ t('host.score') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(player, idx) in players"
                                :key="player.id"
                                class="border-b border-gray-50 dark:border-gray-800/50"
                            >
                                <td class="px-6 py-3 font-bold text-gray-400">{{ idx + 1 }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <AvatarDisplay :name="player.avatar" :size="28" />
                                        <span class="font-medium text-gray-900 dark:text-white">{{ player.nickname }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-center">{{ player.correct_answers }}</td>
                                <td class="px-6 py-3 text-center">{{ player.total_answers }}</td>
                                <td class="px-6 py-3 text-right font-bold text-primary-600 dark:text-primary-400">{{ player.score }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
