<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useGame } from '@/Composables/useGame';
import { useTimer } from '@/Composables/useTimer';
import AvatarDisplay from '@/Components/Avatar/AvatarDisplay.vue';
import ConfettiEffect from '@/Components/Game/ConfettiEffect.vue';
import QRCodeDisplay from '@/Components/Game/QRCodeDisplay.vue';

const { t } = useI18n();

const props = defineProps({
    gameSession: Object,
    quiz: Object,
    questions: Array,
    players: Array,
    theme: Object,
});

const themeGradients = computed(() => props.theme?.gradients || {
    lobby: 'from-indigo-600 to-purple-700',
    question: 'bg-gray-900',
    scoreboard: 'from-indigo-600 to-purple-700',
    finished: 'from-yellow-400 via-pink-500 to-purple-600',
});

const countdownValue = ref(null);
const showConfetti = ref(false);
const isCountdownRunning = ref(false);
const hasShownCountdown = ref(false);

const joinUrl = computed(() => {
    const base = window.location.origin;
    return `${base}/play?code=${props.gameSession.game_code}`;
});
const { timeRemaining, progress, start: startTimer, stop: stopTimer } = useTimer();

const {
    gameState, players: livePlayers, currentQuestion, questionNumber, totalQuestions,
    timeLimit, answeredCount, totalPlayers, leaderboard, playerPositions,
    correctAnswer, answerStats, playerResults, finalLeaderboard, podium,
    joinChannel,
} = useGame(props.gameSession.id);

// Initialize from props
onMounted(() => {
    livePlayers.value = props.players.map(p => ({
        id: p.id,
        nickname: p.nickname,
        avatar: p.avatar,
    }));
    totalPlayers.value = props.players.length;

    if (props.gameSession.status === 'waiting') {
        gameState.value = 'lobby';
    } else if (props.gameSession.status === 'playing') {
        gameState.value = 'question';
    } else if (props.gameSession.status === 'finished') {
        gameState.value = 'finished';
    }

    joinChannel();
});

// Watch for question state to start timer
watch(gameState, (state) => {
    if (state === 'question' && timeLimit.value > 0 && !isCountdownRunning.value && !hasShownCountdown.value) {
        runCountdown();
    }
    // Reset countdown flag if kembali ke lobby atau selesai
    if (state === 'lobby' || state === 'finished') {
        hasShownCountdown.value = false;
    }
});

async function runCountdown() {
    isCountdownRunning.value = true;
    countdownValue.value = 3;
    gameState.value = 'countdown';

    for (let i = 3; i >= 1; i--) {
        countdownValue.value = i;
        await new Promise(r => setTimeout(r, 1000));
    }
    countdownValue.value = 'START!';
    await new Promise(r => setTimeout(r, 500));

    countdownValue.value = null;
    hasShownCountdown.value = true;
    gameState.value = 'question';
    await nextTick();
    isCountdownRunning.value = false;
    startTimer(timeLimit.value);
}

function startGame() {
    router.post(route('game.start', props.gameSession.id), {}, { preserveState: true });
}

function revealAnswer() {
    stopTimer();
    router.post(route('game.reveal', props.gameSession.id), {}, { preserveState: true });
}

function nextQuestion() {
    router.post(route('game.next', props.gameSession.id), {}, { preserveState: true });
}

function endGame() {
    router.post(route('game.end', props.gameSession.id), {}, { preserveState: true });
}

const timerColor = computed(() => {
    if (progress.value > 50) return 'bg-green-500';
    if (progress.value > 25) return 'bg-yellow-500';
    return 'bg-red-500';
});

const answerColorMap = {
    red: { bg: 'bg-red-500', text: 'text-white', shape: '▲' },
    blue: { bg: 'bg-blue-500', text: 'text-white', shape: '◆' },
    yellow: { bg: 'bg-yellow-500', text: 'text-white', shape: '●' },
    green: { bg: 'bg-green-500', text: 'text-white', shape: '■' },
};

function isCorrectAnswer(answerId) {
    return correctAnswer.value?.answers?.some(a => a.id === answerId) ?? false;
}

function getAnswerCount(answerId) {
    return answerStats.value?.answer_counts?.find(s => s.answer_id === answerId)?.count ?? 0;
}

function getAnswerPercentage(answerId) {
    const total = answerStats.value?.answer_counts?.reduce((sum, s) => sum + s.count, 0) ?? 0;
    if (total === 0) return 0;
    return Math.round((getAnswerCount(answerId) / total) * 100);
}

// Trigger confetti when game finishes
watch(gameState, (state) => {
    if (state === 'finished') {
        setTimeout(() => { showConfetti.value = true; }, 1200);
    }
});
</script>

<template>
    <Head :title="`${quiz.title} - Host`" />
    <div class="min-h-screen flex flex-col">
        <!-- Confetti overlay -->
        <ConfettiEffect v-if="showConfetti" :duration="6000" @complete="showConfetti = false" />

        <!-- LOBBY -->
        <div v-if="gameState === 'lobby'" class="flex-1 flex flex-col text-white bg-gradient-to-br" :class="themeGradients.lobby">
            <!-- Header with QR Code -->
            <div class="flex flex-col md:flex-row items-center justify-center gap-6 pt-8 pb-4 px-6 animate-slide-in-down">
                <!-- QR Code -->
                <div class="bg-white p-3 rounded-2xl shadow-lg">
                    <QRCodeDisplay :value="joinUrl" :size="160" />
                    <p class="text-xs text-gray-500 text-center mt-1 font-medium">{{ t('host.scan_to_join') }}</p>
                </div>
                <!-- Game Code -->
                <div class="text-center">
                    <p class="text-lg opacity-80 mb-2">{{ t('host.join_at') }} <span class="font-bold">yahoot.my.id</span></p>
                    <div class="text-7xl font-extrabold tracking-[0.3em] bg-white/10 inline-block px-8 py-4 rounded-2xl backdrop-blur">
                        {{ gameSession.game_code }}
                    </div>
                    <p class="text-sm opacity-60 mt-2">{{ t('host.or_scan_qr') }}</p>
                </div>
            </div>

            <!-- Players grid -->
            <div class="flex-1 px-8 py-4 overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold">{{ t('host.players') }}</h3>
                    <span class="bg-white/20 px-4 py-1 rounded-full text-sm font-bold">
                        {{ livePlayers.length }} {{ t('host.joined') }}
                    </span>
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4">
                    <div
                        v-for="player in livePlayers"
                        :key="player.id"
                        class="flex flex-col items-center animate-bounce-in"
                    >
                        <div class="animate-float">
                            <AvatarDisplay :name="player.avatar" :size="56" />
                        </div>
                        <span class="mt-1 text-sm font-medium truncate max-w-[80px]">{{ player.nickname }}</span>
                    </div>
                </div>
                <div v-if="livePlayers.length === 0" class="text-center py-12 opacity-60">
                    <p class="text-xl">{{ t('host.waiting_players') }}</p>
                </div>
            </div>

            <!-- Start button -->
            <div class="p-6 text-center">
                <button
                    @click="startGame"
                    :disabled="livePlayers.length === 0"
                    class="px-12 py-4 bg-white text-indigo-600 font-extrabold text-xl rounded-2xl hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-xl hover:scale-105 active:scale-95"
                >
                    {{ t('host.start_game') }}
                </button>
            </div>
        </div>

        <!-- COUNTDOWN -->
        <div v-else-if="gameState === 'countdown'" class="flex-1 flex items-center justify-center" :class="{
            'bg-red-500': countdownValue === 3,
            'bg-yellow-500': countdownValue === 2,
            'bg-green-500': countdownValue === 1,
            'bg-indigo-600': countdownValue === 'START!',
        }">
            <div
                :key="countdownValue"
                :class="countdownValue === 'START!' ? 'text-[80px] animate-pop-bounce' : 'text-[140px] animate-zoom-countdown'"
                class="font-extrabold text-white"
            >
                {{ countdownValue }}
            </div>
        </div>

        <!-- QUESTION -->
        <div v-else-if="gameState === 'question'" class="flex-1 flex flex-col text-white" :class="themeGradients.question">
            <!-- Timer bar -->
            <div class="h-2 bg-gray-700">
                <div
                    :class="timerColor"
                    class="h-full transition-all duration-100 ease-linear"
                    :style="{ width: progress + '%' }"
                ></div>
            </div>

            <!-- Question header -->
            <div class="flex items-center justify-between px-6 py-3 bg-gray-800 animate-slide-in-down">
                <span class="text-sm font-medium text-gray-400">
                    {{ questionNumber }} / {{ totalQuestions }}
                </span>
                <span class="text-3xl font-extrabold">{{ Math.ceil(timeRemaining) }}</span>
                <span class="text-sm font-medium text-gray-400">
                    {{ answeredCount }} / {{ totalPlayers }} {{ t('host.answered') }}
                </span>
            </div>

            <!-- Question text -->
            <div class="flex-1 flex items-center justify-center px-8 py-4 animate-slide-in-down" style="animation-delay: 0.1s">
                <div class="text-center max-w-3xl">
                    <h2 class="text-3xl md:text-4xl font-extrabold leading-tight">
                        {{ currentQuestion?.question_text }}
                    </h2>
                    <img v-if="currentQuestion?.image_url" :src="currentQuestion.image_url"
                        class="mt-4 max-h-60 mx-auto rounded-xl" alt="Question image" />
                </div>
            </div>

            <!-- Answer options -->
            <div class="px-4 pb-4 grid grid-cols-2 gap-3">
                <div
                    v-for="(answer, idx) in currentQuestion?.answers"
                    :key="answer.id"
                    :class="answerColorMap[answer.color]?.bg"
                    class="p-4 rounded-2xl flex items-center gap-3 min-h-[70px] animate-slide-in-up"
                    :style="{ animationDelay: `${0.1 + idx * 0.1}s` }"
                >
                    <span class="text-3xl">{{ answerColorMap[answer.color]?.shape }}</span>
                    <span class="text-lg font-bold">{{ answer.answer_text }}</span>
                </div>
            </div>

            <!-- Reveal button -->
            <div class="p-4 text-center bg-gray-800">
                <button @click="revealAnswer" class="px-8 py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl transition-all hover:scale-105 active:scale-95">
                    {{ t('host.reveal_answer') }}
                </button>
            </div>
        </div>

        <!-- RESULT / ANSWER REVEAL -->
        <div v-else-if="gameState === 'result'" class="flex-1 flex flex-col text-white" :class="themeGradients.question">
            <div class="p-6 text-center animate-slide-in-down">
                <h2 class="text-2xl font-extrabold mb-6">{{ currentQuestion?.question_text }}</h2>
            </div>

            <div class="flex-1 px-4 grid grid-cols-2 gap-4">
                <div
                    v-for="(answer, idx) in currentQuestion?.answers"
                    :key="answer.id"
                    :class="[
                        answerColorMap[answer.color]?.bg,
                        isCorrectAnswer(answer.id) ? 'ring-4 ring-white scale-105 animate-pulse-glow' : 'animate-fade-dim'
                    ]"
                    class="p-4 rounded-2xl flex flex-col items-center justify-center min-h-[100px] transition-all"
                >
                    <span class="text-2xl font-bold mb-1">{{ answerColorMap[answer.color]?.shape }} {{ answer.answer_text }}</span>
                    <span v-if="isCorrectAnswer(answer.id)" class="text-3xl animate-score-reveal">✓</span>
                    <!-- Answer count bar -->
                    <div class="mt-2 w-full">
                        <div class="h-6 bg-black/20 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-white/30 rounded-full animate-bar-grow"
                                :style="{
                                    width: getAnswerPercentage(answer.id) + '%',
                                    animationDelay: `${idx * 0.15}s`,
                                }"
                            ></div>
                        </div>
                        <p class="text-sm mt-1 text-center">
                            {{ getAnswerCount(answer.id) }} {{ t('host.votes') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-6 text-center">
                <button @click="nextQuestion" class="px-8 py-3 bg-indigo-500 hover:bg-indigo-600 text-white font-bold rounded-xl transition-all hover:scale-105 active:scale-95 text-lg">
                    {{ t('host.next') }} →
                </button>
            </div>
        </div>

        <!-- SCOREBOARD -->
        <div v-else-if="gameState === 'scoreboard'" class="flex-1 flex flex-col text-white bg-gradient-to-br" :class="themeGradients.scoreboard">
            <div class="p-6 text-center animate-slide-in-down">
                <h2 class="text-3xl font-extrabold">{{ t('host.scoreboard') }}</h2>
            </div>

            <div class="flex-1 flex flex-col items-center justify-center px-8 max-w-2xl mx-auto w-full">
                <TransitionGroup name="scoreboard-list" tag="div" class="w-full">
                    <div
                        v-for="(entry, index) in leaderboard"
                        :key="entry.player_id"
                        class="w-full flex items-center gap-4 mb-3 bg-white/10 rounded-xl p-4 backdrop-blur animate-slide-in-up"
                        :style="{ animationDelay: `${(leaderboard.length - index) * 0.15}s` }"
                    >
                        <span class="text-2xl font-extrabold w-10 text-center">
                            {{ entry.rank <= 3 ? ['🥇','🥈','🥉'][entry.rank - 1] : `#${entry.rank}` }}
                        </span>
                        <AvatarDisplay :name="entry.avatar" :size="44" />
                        <span class="flex-1 text-lg font-bold truncate">{{ entry.nickname }}</span>
                        <span class="text-xl font-extrabold animate-score-reveal" :style="{ animationDelay: `${(leaderboard.length - index) * 0.15 + 0.3}s` }">
                            {{ entry.score }}
                        </span>
                    </div>
                </TransitionGroup>
            </div>

            <div class="p-6 text-center">
                <button @click="nextQuestion" class="px-8 py-3 bg-white text-indigo-600 font-bold rounded-xl hover:bg-gray-100 transition-all hover:scale-105 active:scale-95 text-lg">
                    {{ t('host.next') }} →
                </button>
            </div>
        </div>

        <!-- FINISHED / PODIUM -->
        <div v-else-if="gameState === 'finished'" class="flex-1 flex flex-col text-white bg-gradient-to-br" :class="themeGradients.finished">
            <div class="p-6 text-center animate-slide-in-down">
                <h2 class="text-4xl font-extrabold">🏆 {{ t('host.game_over') }}</h2>
            </div>

            <!-- Podium -->
            <div class="flex items-end justify-center gap-4 px-8 py-6" v-if="podium.length > 0">
                <!-- 2nd place -->
                <div v-if="podium[1]" class="text-center animate-podium-rise" style="animation-delay: 0.5s">
                    <AvatarDisplay :name="podium[1].avatar" :size="64" class="mx-auto mb-2" />
                    <p class="font-bold text-sm truncate max-w-[100px]">{{ podium[1].nickname }}</p>
                    <p class="text-xs">{{ podium[1].score }}</p>
                    <div class="bg-gray-300/30 w-24 h-24 rounded-t-xl mt-2 flex items-center justify-center">
                        <span class="text-4xl">🥈</span>
                    </div>
                </div>
                <!-- 1st place -->
                <div v-if="podium[0]" class="text-center animate-podium-rise" style="animation-delay: 1.2s">
                    <div class="animate-crown-drop" style="animation-delay: 2s">
                        <span class="text-3xl">👑</span>
                    </div>
                    <AvatarDisplay :name="podium[0].avatar" :size="80" class="mx-auto mb-2" />
                    <p class="font-bold truncate max-w-[100px]">{{ podium[0].nickname }}</p>
                    <p class="text-sm">{{ podium[0].score }}</p>
                    <div class="bg-yellow-300/30 w-28 h-32 rounded-t-xl mt-2 flex items-center justify-center">
                        <span class="text-5xl">🥇</span>
                    </div>
                </div>
                <!-- 3rd place -->
                <div v-if="podium[2]" class="text-center animate-podium-rise" style="animation-delay: 0.2s">
                    <AvatarDisplay :name="podium[2].avatar" :size="56" class="mx-auto mb-2" />
                    <p class="font-bold text-sm truncate max-w-[100px]">{{ podium[2].nickname }}</p>
                    <p class="text-xs">{{ podium[2].score }}</p>
                    <div class="bg-orange-300/30 w-20 h-16 rounded-t-xl mt-2 flex items-center justify-center">
                        <span class="text-3xl">🥉</span>
                    </div>
                </div>
            </div>

            <!-- Full leaderboard -->
            <div class="flex-1 overflow-y-auto px-8 pb-4">
                <div class="max-w-2xl mx-auto">
                    <div
                        v-for="(entry, index) in finalLeaderboard"
                        :key="entry.player_id"
                        class="flex items-center gap-3 mb-2 bg-white/10 rounded-xl p-3 backdrop-blur animate-slide-in-up"
                        :style="{ animationDelay: `${1.5 + index * 0.08}s` }"
                    >
                        <span class="font-extrabold w-8 text-center">#{{ entry.rank }}</span>
                        <AvatarDisplay :name="entry.avatar" :size="36" />
                        <span class="flex-1 font-bold truncate">{{ entry.nickname }}</span>
                        <span class="font-extrabold">{{ entry.score }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="p-6 flex justify-center gap-4 bg-black/20">
                <a
                    :href="route('game.export', gameSession.id)"
                    class="px-6 py-3 bg-white/20 hover:bg-white/30 text-white font-bold rounded-xl transition-all hover:scale-105 active:scale-95"
                >
                    📥 {{ t('host.download_csv') }}
                </a>
                <button
                    @click="router.visit(route('dashboard'))"
                    class="px-6 py-3 bg-white text-indigo-600 font-bold rounded-xl hover:bg-gray-100 transition-all hover:scale-105 active:scale-95"
                >
                    {{ t('host.finish') }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* TransitionGroup for scoreboard ranking changes */
.scoreboard-list-move {
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.scoreboard-list-enter-active {
    transition: all 0.4s ease-out;
}
.scoreboard-list-leave-active {
    transition: all 0.3s ease-in;
    position: absolute;
}
.scoreboard-list-enter-from {
    opacity: 0;
    transform: translateX(-30px);
}
.scoreboard-list-leave-to {
    opacity: 0;
    transform: translateX(30px);
}
</style>
