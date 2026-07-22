<script setup>
import { computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AvatarDisplay from '@/Components/Avatar/AvatarDisplay.vue';
import GameLayout from '@/Components/Game/GameLayout.vue';
import Icon from '@/Components/UI/Icon.vue';

const { t } = useI18n();

const props = defineProps({
    gameSession: Object,
    quiz: Object,
    leaderboard: Array,
    totalQuestions: Number,
    playerStats: Array,
});

const podium = computed(() => props.leaderboard.slice(0, 3));
</script>

<template>
    <Head :title="`${quiz.title} - ${t('host.results')}`" />

    <GameLayout>
    <div class="flex min-h-screen flex-col text-white">
        <!-- Header -->
        <div class="px-4 pb-4 pt-8 text-center">
            <span class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-300 text-amber-950 shadow-xl shadow-amber-500/20"><Icon name="trophy" class="h-8 w-8" /></span>
            <h1 class="mb-1 text-3xl font-black md:text-4xl">{{ quiz.title }}</h1>
            <p class="text-white/60">{{ t('host.game_code') }}: {{ gameSession.game_code }} · {{ totalQuestions }} {{ t('host.questions') }}</p>
        </div>

        <!-- Podium -->
        <div class="flex items-end justify-center gap-6 px-8 py-6" v-if="podium.length > 0">
            <!-- 2nd -->
            <div v-if="podium[1]" class="text-center w-28 animate-podium-rise" style="animation-delay: 0.5s">
                <AvatarDisplay :name="podium[1].avatar" :size="64" class="mx-auto mb-2" />
                <p class="font-bold text-sm truncate">{{ podium[1].nickname }}</p>
                <p class="text-xs opacity-80">{{ podium[1].score }} pts</p>
                <div class="mt-2 flex h-24 w-full items-center justify-center rounded-t-2xl border border-white/10 bg-slate-300/20">
                    <span class="text-3xl font-black">2</span>
                </div>
            </div>
            <!-- 1st -->
            <div v-if="podium[0]" class="text-center w-32 animate-podium-rise" style="animation-delay: 1.2s">
                <AvatarDisplay :name="podium[0].avatar" :size="80" class="mx-auto mb-2" />
                <p class="font-bold truncate">{{ podium[0].nickname }}</p>
                <p class="text-sm opacity-80">{{ podium[0].score }} pts</p>
                <div class="mt-2 flex h-32 w-full items-center justify-center rounded-t-2xl border border-amber-200/20 bg-amber-300/25">
                    <span class="text-4xl font-black">1</span>
                </div>
            </div>
            <!-- 3rd -->
            <div v-if="podium[2]" class="text-center w-24 animate-podium-rise" style="animation-delay: 0.2s">
                <AvatarDisplay :name="podium[2].avatar" :size="56" class="mx-auto mb-2" />
                <p class="font-bold text-sm truncate">{{ podium[2].nickname }}</p>
                <p class="text-xs opacity-80">{{ podium[2].score }} pts</p>
                <div class="mt-2 flex h-16 w-full items-center justify-center rounded-t-2xl border border-orange-200/20 bg-orange-300/20">
                    <span class="text-2xl font-black">3</span>
                </div>
            </div>
        </div>

        <!-- Leaderboard table -->
        <div class="max-w-3xl mx-auto px-6 pb-8">
            <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.07] shadow-2xl backdrop-blur-xl">
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
                            v-for="(entry, index) in leaderboard"
                            :key="entry.player_id"
                            class="border-b border-white/5 hover:bg-white/5 animate-slide-in-up"
                            :style="{ animationDelay: `${1.5 + index * 0.08}s` }"
                        >
                            <td class="p-3 font-extrabold">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg text-xs font-black" :class="entry.rank <= 3 ? 'bg-amber-300 text-amber-950' : 'bg-white/10 text-white'">{{ entry.rank }}</span>
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
        <div class="sticky bottom-0 mt-auto border-t border-white/10 bg-slate-950/60 p-4 backdrop-blur-xl">
            <div class="max-w-3xl mx-auto flex justify-center gap-4 flex-wrap">
                <button
                    @click="router.post(route('game.store', quiz.id))"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-6 py-3 font-bold text-white transition-all hover:-translate-y-0.5 hover:bg-emerald-400"
                >
                    <Icon name="refresh" class="h-5 w-5" /> {{ t('game.play_again') }}
                </button>
                <a
                    :href="route('game.export', gameSession.id)"
                    class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/10 px-6 py-3 font-bold text-white transition-all hover:-translate-y-0.5 hover:bg-white/20"
                >
                    <Icon name="download" class="h-5 w-5" /> {{ t('host.download_csv') }}
                </a>
                <Link
                    :href="route('dashboard')"
                    class="rounded-xl bg-white px-6 py-3 font-bold text-primary-600 transition-all hover:-translate-y-0.5 hover:bg-primary-50"
                >
                    {{ t('host.back_dashboard') }}
                </Link>
            </div>
        </div>
    </div>
    </GameLayout>
</template>
