<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useGame } from '@/Composables/useGame';
import { useTimer } from '@/Composables/useTimer';
import AvatarDisplay from '@/Components/Avatar/AvatarDisplay.vue';

const { t } = useI18n();

const props = defineProps({
    gameSession: Object,
});

const player = ref(null);
const selectedAnswer = ref(null);
const hasAnswered = ref(false);
const countdownValue = ref(null);

const { timeRemaining, progress, start: startTimer, stop: stopTimer, getElapsedMs } = useTimer();

const {
    gameState, players, currentQuestion, questionNumber, totalQuestions,
    timeLimit, myResult, correctAnswer, answerStats, playerResults,
    leaderboard, playerPositions, finalLeaderboard, podium,
    joinChannel, submitAnswer,
} = useGame(props.gameSession.id);

// Load player from sessionStorage
onMounted(() => {
    const stored = sessionStorage.getItem('yahoot_player');
    if (stored) {
        player.value = JSON.parse(stored);
    }
    joinChannel();
});

// Auto-start timer when question arrives
watch(gameState, (state) => {
    if (state === 'question') {
        selectedAnswer.value = null;
        hasAnswered.value = false;
        runCountdown();
    }
});

async function runCountdown() {
    countdownValue.value = 3;
    gameState.value = 'countdown';

    for (let i = 3; i >= 1; i--) {
        countdownValue.value = i;
        await new Promise(r => setTimeout(r, 1000));
    }

    countdownValue.value = null;
    gameState.value = 'question';
    startTimer(timeLimit.value);
}

async function selectAnswer(answer) {
    if (hasAnswered.value || !player.value) return;

    selectedAnswer.value = answer.id;
    hasAnswered.value = true;
    stopTimer();

    const elapsed = getElapsedMs();
    await submitAnswer(player.value.id, answer.id, elapsed);
}

// Timer expired — send no answer
watch(timeRemaining, (val) => {
    if (val <= 0 && gameState.value === 'question' && !hasAnswered.value) {
        hasAnswered.value = true;
        submitAnswer(player.value.id, null, timeLimit.value * 1000);
    }
});

const myPosition = computed(() => {
    if (!player.value || !playerPositions.value) return null;
    return playerPositions.value[player.value.id];
});

const myFinalRank = computed(() => {
    if (!player.value || !finalLeaderboard.value) return null;
    return finalLeaderboard.value.find(e => e.player_id === player.value.id);
});

const myPlayerResult = computed(() => {
    if (!player.value || !playerResults.value) return null;
    return playerResults.value.find(r => r.player_id === player.value.id);
});

const timerColor = computed(() => {
    if (progress.value > 50) return 'bg-green-500';
    if (progress.value > 25) return 'bg-yellow-500';
    return 'bg-red-500';
});

const answerColors = {
    red: { bg: 'bg-red-500 hover:bg-red-600', shape: '▲' },
    blue: { bg: 'bg-blue-500 hover:bg-blue-600', shape: '◆' },
    yellow: { bg: 'bg-yellow-500 hover:bg-yellow-600', shape: '●' },
    green: { bg: 'bg-green-500 hover:bg-green-600', shape: '■' },
};

function goHome() {
    sessionStorage.removeItem('yahoot_player');
    sessionStorage.removeItem('yahoot_game');
    router.visit('/');
}
</script>

<template>
    <Head :title="t('play.playing')" />
    <div class="min-h-screen flex flex-col">
        <!-- Lobby: Waiting for host -->
        <div v-if="gameState === 'lobby'" class="flex-1 flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">
            <div class="text-center text-white p-8">
                <div v-if="player" class="mb-6">
                    <AvatarDisplay :name="player.avatar" :size="96" class="mx-auto mb-4" />
                    <h2 class="text-2xl font-bold">{{ player.nickname }}</h2>
                </div>
                <div class="animate-pulse">
                    <p class="text-xl">{{ t('play.waiting_host') }}</p>
                    <div class="mt-4 flex justify-center gap-1">
                        <div class="w-3 h-3 bg-white rounded-full animate-bounce" style="animation-delay: 0s"></div>
                        <div class="w-3 h-3 bg-white rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        <div class="w-3 h-3 bg-white rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Countdown -->
        <div v-else-if="gameState === 'countdown'" class="flex-1 flex items-center justify-center" :class="{
            'bg-red-500': countdownValue === 3,
            'bg-yellow-500': countdownValue === 2,
            'bg-green-500': countdownValue === 1,
        }">
            <div class="text-center">
                <div class="text-[120px] font-extrabold text-white animate-ping-once">
                    {{ countdownValue }}
                </div>
            </div>
        </div>

        <!-- Question: Show answer buttons -->
        <div v-else-if="gameState === 'question' && !hasAnswered" class="flex-1 flex flex-col bg-gray-900">
            <!-- Timer -->
            <div class="p-4 text-center">
                <div class="text-5xl font-extrabold text-white mb-3">
                    {{ Math.ceil(timeRemaining) }}
                </div>
                <div class="w-full h-2 bg-gray-700 rounded-full overflow-hidden">
                    <div
                        :class="timerColor"
                        class="h-full transition-all duration-100 ease-linear rounded-full"
                        :style="{ width: progress + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Answer buttons -->
            <div class="flex-1 p-3" :class="currentQuestion?.type === 'true_false' ? 'grid grid-cols-1 gap-3' : 'grid grid-cols-2 gap-3'">
                <button
                    v-for="answer in currentQuestion?.answers"
                    :key="answer.id"
                    @click="selectAnswer(answer)"
                    :class="answerColors[answer.color]?.bg"
                    class="rounded-2xl flex items-center justify-center text-white text-5xl font-bold shadow-lg active:scale-95 transition-transform min-h-[120px]"
                >
                    {{ answerColors[answer.color]?.shape }}
                </button>
            </div>
        </div>

        <!-- Waiting after answering -->
        <div v-else-if="(gameState === 'question' || gameState === 'answering') && hasAnswered" class="flex-1 flex items-center justify-center bg-gray-800">
            <div class="text-center text-white">
                <div class="text-6xl mb-4">✓</div>
                <p class="text-xl font-bold">{{ t('play.answer_sent') }}</p>
                <p class="text-gray-400 mt-2">{{ t('play.waiting_others') }}</p>
            </div>
        </div>

        <!-- Result: Correct / Wrong -->
        <div v-else-if="gameState === 'result'" class="flex-1 flex items-center justify-center"
            :class="myPlayerResult?.is_correct ? 'bg-green-500' : 'bg-red-500'">
            <div class="text-center text-white p-8">
                <template v-if="myPlayerResult?.is_correct">
                    <div class="text-6xl mb-4">🎉</div>
                    <h2 class="text-3xl font-extrabold mb-2">{{ t('play.correct') }}</h2>
                    <p class="text-2xl font-bold">+{{ (myPlayerResult?.points_earned || 0) + (myPlayerResult?.streak_bonus || 0) }}</p>
                    <p v-if="myResult?.streak > 1" class="mt-2 text-lg">
                        🔥 {{ myResult.streak }} {{ t('play.streak') }}
                    </p>
                </template>
                <template v-else>
                    <div class="text-6xl mb-4">😢</div>
                    <h2 class="text-3xl font-extrabold">{{ t('play.wrong') }}</h2>
                </template>
            </div>
        </div>

        <!-- Scoreboard -->
        <div v-else-if="gameState === 'scoreboard'" class="flex-1 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-700">
            <div class="text-center text-white p-8">
                <h2 class="text-lg text-white/70 mb-2">{{ t('play.your_position') }}</h2>
                <div class="text-7xl font-extrabold mb-2">#{{ myPosition?.rank || '?' }}</div>
                <p class="text-2xl font-bold">{{ myPosition?.score || 0 }} {{ t('play.points') }}</p>
            </div>
        </div>

        <!-- Finished -->
        <div v-else-if="gameState === 'finished'" class="flex-1 flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">
            <div class="text-center text-white p-8 max-w-sm">
                <template v-if="myFinalRank && myFinalRank.rank <= 3">
                    <div class="text-6xl mb-4">
                        {{ myFinalRank.rank === 1 ? '🥇' : myFinalRank.rank === 2 ? '🥈' : '🥉' }}
                    </div>
                </template>
                <div v-else class="text-6xl mb-4">🎮</div>

                <h2 class="text-3xl font-extrabold mb-1">#{{ myFinalRank?.rank || '?' }}</h2>
                <p class="text-xl mb-4">{{ myFinalRank?.score || 0 }} {{ t('play.points') }}</p>

                <button @click="goHome" class="px-8 py-3 bg-white text-indigo-600 font-bold rounded-xl hover:bg-gray-100 transition-colors">
                    {{ t('play.back_home') }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes ping-once {
    0% { transform: scale(0.5); opacity: 0; }
    50% { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}
.animate-ping-once {
    animation: ping-once 0.6s ease-out;
}
</style>
