<script setup>
import { computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AvatarDisplay from '@/Components/Avatar/AvatarDisplay.vue';

const { t } = useI18n();

const props = defineProps({
    gameSession: Object,
    quiz: Object,
    leaderboard: Array,
    totalQuestions: Number,
    playerStats: Array,
});

const podium = computed(() => props.leaderboard.slice(0, 3));
const rest = computed(() => props.leaderboard.slice(3));
</script>

<template>
    <Head :title="`${quiz.title} - ${t('host.results')}`" />

    <div class="min-h-screen bg-gradient-to-br from-indigo-600 to-purple-700 text-white">
        <!-- Header -->
        <div class="text-center pt-8 pb-4 px-4">
            <h1 class="text-3xl md:text-4xl font-extrabold mb-1">{{ quiz.title }}</h1>
            <p class="opacity-80">{{ t('host.game_code') }}: {{ gameSession.game_code }} · {{ totalQuestions }} {{ t('host.questions') }}</p>
        </div>

        <!-- Podium -->
        <div class="flex items-end justify-center gap-6 px-8 py-6" v-if="podium.length > 0">
            <!-- 2nd -->
            <div v-if="podium[1]" class="text-center w-28">
                <AvatarDisplay :name="podium[1].avatar" :size="64" class="mx-auto mb-2" />
                <p class="font-bold text-sm truncate">{{ podium[1].nickname }}</p>
                <p class="text-xs opacity-80">{{ podium[1].score }} pts</p>
                <div class="bg-gray-300/30 w-full h-24 rounded-t-xl mt-2 flex items-center justify-center">
                    <span class="text-4xl">🥈</span>
                </div>
            </div>
            <!-- 1st -->
            <div v-if="podium[0]" class="text-center w-32">
                <AvatarDisplay :name="podium[0].avatar" :size="80" class="mx-auto mb-2" />
                <p class="font-bold truncate">{{ podium[0].nickname }}</p>
                <p class="text-sm opacity-80">{{ podium[0].score }} pts</p>
                <div class="bg-yellow-300/30 w-full h-32 rounded-t-xl mt-2 flex items-center justify-center">
                    <span class="text-5xl">🥇</span>
                </div>
            </div>
            <!-- 3rd -->
            <div v-if="podium[2]" class="text-center w-24">
                <AvatarDisplay :name="podium[2].avatar" :size="56" class="mx-auto mb-2" />
                <p class="font-bold text-sm truncate">{{ podium[2].nickname }}</p>
                <p class="text-xs opacity-80">{{ podium[2].score }} pts</p>
                <div class="bg-orange-300/30 w-full h-16 rounded-t-xl mt-2 flex items-center justify-center">
                    <span class="text-3xl">🥉</span>
                </div>
            </div>
        </div>

        <!-- Leaderboard table -->
        <div class="max-w-3xl mx-auto px-6 pb-8">
            <div class="bg-white/10 backdrop-blur rounded-2xl overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-sm text-white/60 border-b border-white/10">
                            <th class="p-3 w-12">#</th>
                            <th class="p-3">{{ t('host.player') }}</th>
                            <th class="p-3 text-center">{{ t('host.correct') }}</th>
                            <th class="p-3 text-center">{{ t('host.streak') }}</th>
                            <th class="p-3 text-right">{{ t('host.score') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="entry in leaderboard"
                            :key="entry.player_id"
                            class="border-b border-white/5 hover:bg-white/5"
                        >
                            <td class="p-3 font-extrabold">
                                <template v-if="entry.rank <= 3">
                                    {{ ['🥇','🥈','🥉'][entry.rank - 1] }}
                                </template>
                                <template v-else>{{ entry.rank }}</template>
                            </td>
                            <td class="p-3">
                                <div class="flex items-center gap-2">
                                    <AvatarDisplay :name="entry.avatar" :size="32" />
                                    <span class="font-bold truncate max-w-[160px]">{{ entry.nickname }}</span>
                                </div>
                            </td>
                            <td class="p-3 text-center">
                                {{ entry.correct_count ?? '-' }} / {{ totalQuestions }}
                            </td>
                            <td class="p-3 text-center">
                                {{ entry.best_streak ?? '-' }}
                            </td>
                            <td class="p-3 text-right font-extrabold">
                                {{ entry.score }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="sticky bottom-0 bg-black/30 backdrop-blur p-4">
            <div class="max-w-3xl mx-auto flex justify-center gap-4">
                <a
                    :href="route('game.export', gameSession.id)"
                    class="px-6 py-3 bg-white/20 hover:bg-white/30 text-white font-bold rounded-xl transition-colors"
                >
                    📥 {{ t('host.download_csv') }}
                </a>
                <Link
                    :href="route('dashboard')"
                    class="px-6 py-3 bg-white text-indigo-600 font-bold rounded-xl hover:bg-gray-100 transition-colors"
                >
                    {{ t('host.back_dashboard') }}
                </Link>
            </div>
        </div>
    </div>
</template>
