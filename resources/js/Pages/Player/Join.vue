<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import { useSound } from '@/Composables/useSound';
import { randomNickname } from '@/utils/nicknameGenerator';
import AvatarGrid from '@/Components/Avatar/AvatarGrid.vue';
import AvatarDisplay from '@/Components/Avatar/AvatarDisplay.vue';
import GameCodeInput from '@/Components/Game/GameCodeInput.vue';
import TeamBadge from '@/Components/Game/TeamBadge.vue';

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
    <div class="min-h-screen bg-gradient-to-br from-primary-500 via-primary-600 to-accent-red flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <h1 class="font-display text-6xl font-extrabold text-white tracking-tight drop-shadow-sm">Yahoot!</h1>
                <p class="text-white/80 mt-2 text-lg">{{ t('play.enter_code') }}</p>
            </div>

            <!-- Step 1: Enter Game Code -->
            <div v-if="step === 'code'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8">
                <h2 class="text-xl font-bold text-center text-gray-800 dark:text-white mb-6">
                    {{ t('play.game_code') }}
                </h2>

                <GameCodeInput v-model="gameCode" class="mb-6" @complete="submitCode" />

                <p v-if="error" class="text-red-500 text-sm text-center mb-4">{{ error }}</p>

                <button
                    @click="submitCode"
                    :disabled="gameCode.length !== 6"
                    class="w-full py-3 px-6 bg-primary-600 hover:bg-primary-700 disabled:bg-gray-300 text-white font-bold rounded-xl text-lg transition-all shadow-lg shadow-primary-500/30 disabled:shadow-none"
                >
                    {{ t('play.enter') }}
                </button>
            </div>

            <!-- Step 2: Setup Nickname & Avatar -->
            <div v-else-if="step === 'setup'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8">
                <h2 class="text-xl font-bold text-center text-gray-800 dark:text-white mb-2">
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
                    class="w-full py-3 px-6 bg-green-500 hover:bg-green-600 disabled:bg-gray-300 text-white font-bold rounded-xl text-lg transition-all shadow-lg shadow-green-500/30 disabled:shadow-none"
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
            <div v-else-if="step === 'team'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8">
                <h2 class="text-xl font-bold text-center text-gray-800 dark:text-white mb-6">
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
    </div>
</template>
