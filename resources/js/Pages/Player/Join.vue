<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import AvatarGrid from '@/Components/Avatar/AvatarGrid.vue';
import AvatarDisplay from '@/Components/Avatar/AvatarDisplay.vue';

const { t } = useI18n();

const props = defineProps({
    gameCode: { type: String, default: '' },
});

const step = ref(props.gameCode ? 'setup' : 'code'); // code → setup
const gameCode = ref(props.gameCode);
const nickname = ref('');
const avatar = ref('fox');
const error = ref('');
const loading = ref(false);
const codeInputs = ref(['', '', '', '', '', '']);

function onCodeInput(index, event) {
    const value = event.target.value.replace(/\D/g, '');
    codeInputs.value[index] = value.slice(0, 1);

    if (value && index < 5) {
        const nextInput = document.querySelector(`#code-${index + 1}`);
        if (nextInput) nextInput.focus();
    }

    gameCode.value = codeInputs.value.join('');
}

function onCodeKeydown(index, event) {
    if (event.key === 'Backspace' && !codeInputs.value[index] && index > 0) {
        const prevInput = document.querySelector(`#code-${index - 1}`);
        if (prevInput) prevInput.focus();
    }
}

function onCodePaste(event) {
    const pasted = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    for (let i = 0; i < 6; i++) {
        codeInputs.value[i] = pasted[i] || '';
    }
    gameCode.value = codeInputs.value.join('');
    event.preventDefault();
}

function submitCode() {
    if (gameCode.value.length !== 6) {
        error.value = t('play.code_invalid');
        return;
    }
    error.value = '';
    step.value = 'setup';
}

async function joinGame() {
    if (!nickname.value.trim()) {
        error.value = t('play.nickname_required');
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        const response = await axios.post('/api/games/join', {
            game_code: gameCode.value,
            nickname: nickname.value.trim(),
            avatar: avatar.value,
        });

        // Store player info in sessionStorage for the game page
        sessionStorage.setItem('yahoot_player', JSON.stringify(response.data.player));
        sessionStorage.setItem('yahoot_game', JSON.stringify(response.data.gameSession));

        // Navigate to the game page
        router.visit(`/play/${gameCode.value}`);
    } catch (err) {
        error.value = err.response?.data?.message || t('play.join_error');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Head :title="t('play.join_game')" />
    <div class="min-h-screen bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <h1 class="text-5xl font-extrabold text-white tracking-tight">Yahoot!</h1>
                <p class="text-white/80 mt-2 text-lg">{{ t('play.enter_code') }}</p>
            </div>

            <!-- Step 1: Enter Game Code -->
            <div v-if="step === 'code'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8">
                <h2 class="text-xl font-bold text-center text-gray-800 dark:text-white mb-6">
                    {{ t('play.game_code') }}
                </h2>

                <div class="flex justify-center gap-2 mb-6" @paste="onCodePaste">
                    <input
                        v-for="(_, index) in 6"
                        :key="index"
                        :id="`code-${index}`"
                        type="text"
                        inputmode="numeric"
                        maxlength="1"
                        :value="codeInputs[index]"
                        @input="onCodeInput(index, $event)"
                        @keydown="onCodeKeydown(index, $event)"
                        class="w-12 h-14 text-center text-2xl font-bold rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 outline-none transition-all"
                    />
                </div>

                <p v-if="error" class="text-red-500 text-sm text-center mb-4">{{ error }}</p>

                <button
                    @click="submitCode"
                    :disabled="gameCode.length !== 6"
                    class="w-full py-3 px-6 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white font-bold rounded-xl text-lg transition-all shadow-lg shadow-indigo-500/30 disabled:shadow-none"
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
                    {{ t('play.game_code') }}: <span class="font-mono font-bold text-indigo-600">{{ gameCode }}</span>
                </p>

                <div class="mb-4">
                    <input
                        v-model="nickname"
                        type="text"
                        :placeholder="t('play.nickname_placeholder')"
                        maxlength="20"
                        class="w-full px-4 py-3 text-lg rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 outline-none transition-all"
                        @keyup.enter="joinGame"
                    />
                </div>

                <!-- Selected avatar preview -->
                <div class="flex justify-center mb-4">
                    <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl">
                        <AvatarDisplay :name="avatar" :size="80" />
                    </div>
                </div>

                <!-- Avatar grid -->
                <div class="mb-6 max-h-48 overflow-y-auto rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                    <AvatarGrid v-model="avatar" :size="44" />
                </div>

                <p v-if="error" class="text-red-500 text-sm text-center mb-4">{{ error }}</p>

                <button
                    @click="joinGame"
                    :disabled="loading || !nickname.trim()"
                    class="w-full py-3 px-6 bg-green-500 hover:bg-green-600 disabled:bg-gray-300 text-white font-bold rounded-xl text-lg transition-all shadow-lg shadow-green-500/30 disabled:shadow-none"
                >
                    <span v-if="loading">{{ t('play.joining') }}...</span>
                    <span v-else>{{ t('play.join') }} 🎮</span>
                </button>

                <button
                    @click="step = 'code'; error = ''"
                    class="w-full mt-3 py-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm transition-colors"
                >
                    ← {{ t('play.back') }}
                </button>
            </div>
        </div>
    </div>
</template>
