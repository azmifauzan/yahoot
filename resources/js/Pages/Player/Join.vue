<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import { useSound } from '@/Composables/useSound';
import { randomNickname } from '@/utils/nicknameGenerator';
import AvatarGrid from '@/Components/Avatar/AvatarGrid.vue';
import AvatarDisplay from '@/Components/Avatar/AvatarDisplay.vue';
import GameCodeInput from '@/Components/Game/GameCodeInput.vue';
import TeamBadge from '@/Components/Game/TeamBadge.vue';
import LanguageSwitcher from '@/Components/UI/LanguageSwitcher.vue';
import ThemeSwitcher from '@/Components/UI/ThemeSwitcher.vue';

const { t, locale } = useI18n();
const sound = useSound();

const props = defineProps({
    gameCode: { type: String, default: '' },
});

const step = ref(props.gameCode ? 'setup' : 'code'); // code → setup → team → (join)
const gameCode = ref(props.gameCode);
const nickname = ref(randomNickname(locale.value));
const avatar = ref('fox');
const error = ref('');
const loading = ref(false);
const gameInfo = ref(null); // { mode, team_selection, teams }

function randomizeNickname() {
    nickname.value = randomNickname(locale.value);
}

async function submitCode() {
    if (gameCode.value.length !== 6) {
        error.value = t('play.code_invalid');
        return;
    }
    error.value = '';

    try {
        const { data } = await axios.get(`/api/games/code/${gameCode.value}/info`);
        gameInfo.value = data;
    } catch {
        gameInfo.value = null;
    }

    step.value = 'setup';
}

function proceedFromSetup() {
    if (!nickname.value.trim()) {
        error.value = t('play.nickname_required');
        return;
    }

    error.value = '';

    if (gameInfo.value?.mode === 'team' && gameInfo.value?.team_selection === 'manual') {
        step.value = 'team';
        return;
    }

    joinGame();
}

async function joinGame(teamId = null) {
    sound.unlock();
    loading.value = true;
    error.value = '';

    try {
        const response = await axios.post('/api/games/join', {
            game_code: gameCode.value,
            nickname: nickname.value.trim(),
            avatar: avatar.value,
            team_id: teamId,
        });

        // Store player info in sessionStorage for the game page
        sessionStorage.setItem('yahoot_player', JSON.stringify(response.data.player));
        sessionStorage.setItem('yahoot_game', JSON.stringify(response.data.gameSession));

        // Navigate to the game page
        router.visit(`/play/${gameCode.value}`);
    } catch (err) {
        error.value = err.response?.data?.message || t('play.join_error');
        if (teamId) step.value = 'team';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Head :title="t('play.join_game')" />
    <div class="public-grid relative min-h-screen overflow-hidden bg-[#fbfaff] dark:bg-gray-950">
        <div class="pointer-events-none absolute -left-24 top-1/3 h-72 w-72 rounded-full bg-primary-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 bottom-10 h-72 w-72 rounded-full bg-accent-blue/20 blur-3xl"></div>

        <header class="relative z-20 mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
            <Link href="/" class="group">
                <img src="/images/logo.png?v=3" alt="Yahoot" class="h-10 w-auto transition group-hover:-rotate-3 group-hover:scale-105 dark:hidden" />
                <img src="/images/logo-dark.png?v=1" alt="Yahoot" class="hidden h-10 w-auto transition group-hover:-rotate-3 group-hover:scale-105 dark:block" />
            </Link>
            <div class="flex items-center gap-2">
                <LanguageSwitcher />
                <ThemeSwitcher />
            </div>
        </header>

        <main class="relative z-10 mx-auto grid min-h-[calc(100vh-80px)] max-w-7xl items-center gap-12 px-4 pb-16 sm:px-6 lg:grid-cols-[1fr_28rem] lg:px-8">
            <section class="hidden lg:block">
                <span class="inline-flex rounded-full border border-primary-200 bg-white/80 px-3 py-1.5 text-xs font-bold uppercase tracking-[.16em] text-primary-700 shadow-sm dark:border-primary-800 dark:bg-gray-900/80 dark:text-primary-300">{{ t('landing.feature_realtime') }}</span>
                <h1 class="mt-6 max-w-2xl font-display text-6xl font-black leading-[.95] tracking-[-.04em] text-gray-950 dark:text-white">
                    {{ t('landing.hero_title') }}
                </h1>
                <p class="mt-6 max-w-lg text-lg leading-8 text-gray-600 dark:text-gray-300">{{ t('landing.hero_subtitle') }}</p>
                <div class="mt-10 flex -space-x-3">
                    <div v-for="(item, index) in ['fox', 'robot_blue', 'monster_1', 'panda']" :key="item" class="animate-float rounded-full border-4 border-[#fbfaff] bg-white p-1 shadow-lg dark:border-gray-950 dark:bg-gray-900" :style="{ animationDelay: `${index * .25}s` }">
                        <AvatarDisplay :name="item" :size="50" />
                    </div>
                    <div class="flex h-16 w-16 items-center justify-center rounded-full border-4 border-[#fbfaff] bg-primary-600 text-sm font-black text-white shadow-lg dark:border-gray-950">+99</div>
                </div>
            </section>

            <div class="w-full max-w-md justify-self-center">
                <div class="mb-5 text-center lg:hidden">
                    <h1 class="font-display text-4xl font-black tracking-tight text-gray-950 dark:text-white">{{ t('play.join_game') }}</h1>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">{{ t('play.enter_code') }}</p>
                </div>

                <div class="mb-4 flex items-center justify-center gap-2" aria-hidden="true">
                    <span class="h-2.5 rounded-full transition-all" :class="step === 'code' ? 'w-8 bg-primary-600' : 'w-2.5 bg-primary-200 dark:bg-primary-900'"></span>
                    <span class="h-2.5 rounded-full transition-all" :class="step === 'setup' ? 'w-8 bg-primary-600' : 'w-2.5 bg-primary-200 dark:bg-primary-900'"></span>
                    <span v-if="gameInfo?.mode === 'team'" class="h-2.5 rounded-full transition-all" :class="step === 'team' ? 'w-8 bg-primary-600' : 'w-2.5 bg-primary-200 dark:bg-primary-900'"></span>
                </div>

            <!-- Step 1: Enter Game Code -->
            <div v-if="step === 'code'" class="rounded-[1.7rem] border border-white bg-white/90 p-7 shadow-2xl shadow-primary-950/10 backdrop-blur sm:p-9 dark:border-white/10 dark:bg-gray-900/90">
                <h2 class="mb-2 text-center font-display text-2xl font-extrabold text-gray-900 dark:text-white">
                    {{ t('play.game_code') }}
                </h2>
                <p class="mb-7 text-center text-sm text-gray-500 dark:text-gray-400">{{ t('play.enter_code') }}</p>

                <GameCodeInput v-model="gameCode" class="mb-6" @complete="submitCode" />

                <p v-if="error" class="text-red-500 text-sm text-center mb-4">{{ error }}</p>

                <button
                    @click="submitCode"
                    :disabled="gameCode.length !== 6"
                    class="w-full rounded-2xl bg-primary-600 px-6 py-3.5 text-lg font-bold text-white shadow-lg shadow-primary-500/30 transition-all hover:-translate-y-0.5 hover:bg-primary-700 disabled:translate-y-0 disabled:bg-gray-300 disabled:shadow-none"
                >
                    {{ t('play.enter') }}
                </button>
            </div>

            <!-- Step 2: Setup Nickname & Avatar -->
            <div v-else-if="step === 'setup'" class="rounded-[1.7rem] border border-white bg-white/90 p-7 shadow-2xl shadow-primary-950/10 backdrop-blur sm:p-9 dark:border-white/10 dark:bg-gray-900/90">
                <h2 class="mb-2 text-center font-display text-2xl font-extrabold text-gray-900 dark:text-white">
                    {{ t('play.choose_nickname') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">
                    {{ t('play.game_code') }}: <span class="font-mono font-bold text-primary-600">{{ gameCode }}</span>
                </p>

                <div class="mb-4 relative">
                    <input
                        v-model="nickname"
                        type="text"
                        :placeholder="t('play.nickname_placeholder')"
                        maxlength="20"
                        class="w-full px-4 py-3 pr-12 text-lg rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/30 outline-none transition-all"
                        @keyup.enter="joinGame"
                    />
                    <button
                        type="button"
                        @click="randomizeNickname"
                        :title="t('game.randomize_nickname')"
                        class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 flex items-center justify-center text-xl rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                    >
                        🎲
                    </button>
                </div>

                <!-- Selected avatar preview -->
                <div class="flex justify-center mb-4">
                    <div class="p-3 bg-primary-50 dark:bg-primary-900/20 rounded-2xl">
                        <AvatarDisplay :name="avatar" :size="80" />
                    </div>
                </div>

                <!-- Avatar grid -->
                <div class="mb-6 max-h-48 overflow-y-auto rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                    <AvatarGrid v-model="avatar" :size="44" />
                </div>

                <p v-if="error" class="text-red-500 text-sm text-center mb-4">{{ error }}</p>

                <button
                    @click="proceedFromSetup"
                    :disabled="loading || !nickname.trim()"
                    class="w-full rounded-2xl bg-green-500 px-6 py-3.5 text-lg font-bold text-white shadow-lg shadow-green-500/30 transition-all hover:-translate-y-0.5 hover:bg-green-600 disabled:translate-y-0 disabled:bg-gray-300 disabled:shadow-none"
                >
                    <span v-if="loading">{{ t('play.joining') }}...</span>
                    <span v-else-if="gameInfo?.mode === 'team' && gameInfo?.team_selection === 'manual'">{{ t('common.confirm') }} →</span>
                    <span v-else>{{ t('play.join') }} 🎮</span>
                </button>

                <button
                    @click="step = 'code'; error = ''"
                    class="w-full mt-3 py-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm transition-colors"
                >
                    ← {{ t('play.back') }}
                </button>
            </div>

            <!-- Step 3: Choose a team (manual team selection) -->
            <div v-else-if="step === 'team'" class="rounded-[1.7rem] border border-white bg-white/90 p-7 shadow-2xl shadow-primary-950/10 backdrop-blur sm:p-9 dark:border-white/10 dark:bg-gray-900/90">
                <h2 class="mb-6 text-center font-display text-2xl font-extrabold text-gray-900 dark:text-white">
                    {{ t('team.choose_team') }}
                </h2>

                <div class="grid grid-cols-1 gap-3 mb-6">
                    <button
                        v-for="team in gameInfo?.teams ?? []"
                        :key="team.id"
                        @click="joinGame(team.id)"
                        :disabled="loading"
                        class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 hover:border-primary-500 disabled:opacity-50 transition-all"
                    >
                        <TeamBadge :name="team.name" :color="team.color" />
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ team.player_count }}</span>
                    </button>
                </div>

                <p v-if="error" class="text-red-500 text-sm text-center mb-4">{{ error }}</p>

                <button
                    @click="step = 'setup'; error = ''"
                    class="w-full mt-1 py-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm transition-colors"
                >
                    ← {{ t('play.back') }}
                </button>
            </div>
            </div>
        </main>
        </div>
</template>
