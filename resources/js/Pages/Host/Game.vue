<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useGame } from '@/Composables/useGame';
import { useTimer } from '@/Composables/useTimer';
import { useSound } from '@/Composables/useSound';
import AvatarDisplay from '@/Components/Avatar/AvatarDisplay.vue';
import ConfettiEffect from '@/Components/Game/ConfettiEffect.vue';
import QRCodeDisplay from '@/Components/Game/QRCodeDisplay.vue';
import GameLayout from '@/Components/Game/GameLayout.vue';
import CountdownOverlay from '@/Components/Game/CountdownOverlay.vue';
import TimerBar from '@/Components/Game/TimerBar.vue';
import FloatingReactions from '@/Components/Game/FloatingReactions.vue';
import TeamBadge from '@/Components/Game/TeamBadge.vue';
import Icon from '@/Components/UI/Icon.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { confirm } = useSwal();

const props = defineProps({
    gameSession: Object,
    quiz: Object,
    questions: Array,
    players: Array,
    mode: { type: String, default: 'individual' },
    teams: { type: Array, default: () => [] },
    theme: Object,
    resumeState: Object,
});

const isTeamMode = computed(() => props.mode === 'team');

const teamGroups = computed(() => props.teams.map(team => ({
    id: team.id,
    name: team.name,
    color: team.color,
    players: livePlayers.value.filter(p => p.team?.id === team.id),
})));

const sound = useSound(props.quiz?.settings?.sound_theme ?? 'classic');
const musicEnabled = computed(() => props.quiz?.settings?.music_enabled ?? true);

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
    correctAnswer, answerStats, playerResults, isPoll, finalLeaderboard, podium,
    teamLeaderboard, reactions, powerupEvents, joinChannel,
} = useGame(props.gameSession.id);

const powerupIcons = { double_points: 'bolt', fifty_fifty: 'scissors', freeze_timer: 'snowflake' };

// Initialize from props
onMounted(async () => {
    livePlayers.value = props.players.map(p => ({
        id: p.id,
        nickname: p.nickname,
        avatar: p.avatar,
        team: p.team ? { id: p.team.id, name: p.team.name, color: p.team.color } : null,
    }));
    totalPlayers.value = props.players.length;

    if (props.gameSession.status === 'waiting') {
        gameState.value = 'lobby';
    } else if (props.gameSession.status === 'finished') {
        gameState.value = 'finished';
    } else if (props.resumeState) {
        // Host reopened an in-progress session — rebuild state without WS replay
        const resume = props.resumeState;
        currentQuestion.value = resume.question;
        questionNumber.value = resume.questionNumber;
        totalQuestions.value = resume.totalQuestions;
        timeLimit.value = resume.timeLimit;
        answeredCount.value = resume.answeredCount;
        totalPlayers.value = resume.totalPlayers;
        hasShownCountdown.value = true;

        if (resume.reveal) {
            correctAnswer.value = resume.reveal.correctAnswer;
            answerStats.value = resume.reveal.stats;
            playerResults.value = resume.reveal.playerResults;
            isPoll.value = resume.reveal.isPoll ?? false;
            leaderboard.value = resume.reveal.leaderboard;
            playerPositions.value = resume.reveal.playerPositions;
            gameState.value = 'scoreboard';
        } else {
            gameState.value = 'question';
            await nextTick();
            startTimer(Math.max(resume.timeLimit - resume.elapsedSeconds, 0));
        }
    } else if (props.gameSession.status === 'playing') {
        gameState.value = 'question';
    }

    joinChannel();
});

// Watch for question state to start timer
watch(gameState, (state) => {
    if (state === 'question' && timeLimit.value > 0 && !isCountdownRunning.value) {
        if (!hasShownCountdown.value) {
            runCountdown();
        } else {
            startTimer(timeLimit.value);
        }
    }
    // Reset countdown flag saat kembali ke lobby atau selesai
    if (state === 'lobby' || state === 'finished') {
        hasShownCountdown.value = false;
    }
});

// Background music — lobby ambience while waiting, livelier loop during play
let musicMode = null;
watch(gameState, (state) => {
    const mode = state === 'lobby' ? 'lobby' : state === 'finished' ? null : 'game';
    if (mode === musicMode) return;
    musicMode = mode;

    if (mode === 'lobby' && musicEnabled.value) sound.startLobbyMusic();
    else if (mode === 'game' && musicEnabled.value) sound.startGameMusic();
    else sound.stopMusic();
}, { immediate: true });

onUnmounted(() => sound.stopMusic());

async function runCountdown() {
    isCountdownRunning.value = true;
    countdownValue.value = 3;
    gameState.value = 'countdown';

    for (let i = 3; i >= 1; i--) {
        countdownValue.value = i;
        sound.tick();
        await new Promise(r => setTimeout(r, 1000));
    }
    countdownValue.value = 'START!';
    sound.go();
    await new Promise(r => setTimeout(r, 500));

    countdownValue.value = null;
    hasShownCountdown.value = true;
    gameState.value = 'question';
    await nextTick();
    isCountdownRunning.value = false;
    startTimer(timeLimit.value);
}

function startGame() {
    sound.unlock();
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

function cancelGame() {
    confirm({
        title: t('host.cancel_game'),
        text: t('host.confirm_cancel'),
        confirmText: t('common.yes') || 'Ya',
        cancelText: t('common.cancel') || 'Batal',
        icon: 'warning',
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('game.cancel', props.gameSession.id));
        }
    });
}

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
    if (state === 'scoreboard') {
        sound.whoosh();
    }
    if (state === 'finished') {
        setTimeout(() => { showConfetti.value = true; }, 1200);
        setTimeout(() => { sound.fanfare(); }, 1200);
    }
});
</script>

<template>
    <Head :title="`${quiz.title} - Host`" />
    <GameLayout>
        <!-- Confetti overlay -->
        <ConfettiEffect v-if="showConfetti" :duration="6000" @complete="showConfetti = false" />

        <!-- Floating reactions from players -->
        <FloatingReactions :reactions="reactions" />

        <!-- Power-up usage notices -->
        <div class="fixed left-1/2 top-16 z-40 flex -translate-x-1/2 flex-col items-center gap-2">
            <div
                v-for="p in powerupEvents"
                :key="p.id"
                class="flex items-center gap-2 rounded-full border border-amber-200/50 bg-amber-300/90 px-4 py-2 text-sm font-bold text-amber-950 shadow-xl backdrop-blur animate-slide-in-down"
            >
                <Icon :name="powerupIcons[p.powerup] ?? 'bolt'" class="h-4 w-4" />
                {{ t('powerups.used', { nickname: p.nickname, powerup: t(`powerups.${p.powerup}`) }) }}
            </div>
        </div>

        <!-- Mute toggle -->
        <button
            @click="sound.toggleMute()"
            class="fixed right-4 top-4 z-50 flex h-11 w-11 items-center justify-center rounded-2xl border border-white/15 bg-slate-950/50 text-white shadow-lg backdrop-blur transition-all hover:-translate-y-0.5 hover:bg-white/15"
            :title="sound.muted.value ? t('host.unmute') : t('host.mute')"
        >
            <Icon :name="sound.muted.value ? 'volumeOff' : 'volume'" class="h-5 w-5" />
        </button>

        <!-- Cancel game button -->
        <button
            v-if="gameState !== 'finished'"
            @click="cancelGame"
            class="fixed left-4 top-4 z-50 flex items-center gap-2 rounded-2xl border border-rose-300/20 bg-rose-500/80 px-4 py-2.5 text-xs font-bold text-white shadow-lg backdrop-blur transition-all hover:-translate-y-0.5 hover:bg-rose-500 active:scale-95"
        >
            <Icon name="x" class="h-4 w-4" />
            {{ t('host.cancel_game') }}
        </button>

        <!-- LOBBY -->
        <div v-if="gameState === 'lobby'" class="flex flex-1 flex-col text-white">
            <!-- Header with QR Code -->
            <div class="mx-auto flex w-full max-w-6xl flex-col items-center justify-center gap-6 px-6 pb-4 pt-20 md:flex-row md:pt-12 animate-slide-in-down">
                <!-- QR Code -->
                <div class="rounded-[2rem] border border-white/10 bg-white p-4 shadow-2xl shadow-primary-950/30">
                    <QRCodeDisplay :value="joinUrl" :size="160" />
                    <p class="mt-2 text-center text-xs font-bold uppercase tracking-[0.16em] text-slate-500">{{ t('host.scan_to_join') }}</p>
                </div>
                <!-- Game Code -->
                <div class="text-center md:text-left">
                    <span class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-white/70">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Live lobby
                    </span>
                    <p class="mb-2 text-lg text-white/70">{{ t('host.join_at') }} <span class="font-bold text-white">yahoot.web.id</span></p>
                    <div class="inline-block rounded-[1.75rem] border border-white/15 bg-white/10 px-8 py-4 text-5xl font-black tracking-[0.2em] shadow-xl backdrop-blur sm:text-7xl">
                        {{ gameSession.game_code }}
                    </div>
                    <p class="mt-3 text-sm text-white/50">{{ t('host.or_scan_qr') }}</p>
                </div>
            </div>

            <!-- Players grid -->
            <div class="mx-auto mb-4 flex w-[calc(100%-2rem)] max-w-6xl flex-1 flex-col overflow-y-auto rounded-[2rem] border border-white/10 bg-white/[0.07] p-5 shadow-2xl shadow-slate-950/20 backdrop-blur-xl sm:p-7">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-xl font-black"><Icon name="users" class="h-5 w-5 text-primary-300" />{{ t('host.players') }}</h3>
                    <span class="rounded-full border border-white/10 bg-white/10 px-4 py-1.5 text-sm font-bold">
                        {{ livePlayers.length }} {{ t('host.joined') }}
                    </span>
                </div>
                <!-- Team mode: grouped by team -->
                <div v-if="isTeamMode" class="space-y-6">
                    <div v-for="team in teamGroups" :key="team.id">
                        <div class="mb-2">
                            <TeamBadge :name="team.name" :color="team.color" :score="team.players.length" />
                        </div>
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4">
                            <div
                                v-for="player in team.players"
                                :key="player.id"
                                class="flex flex-col items-center rounded-2xl border border-white/10 bg-white/[0.06] p-3 animate-bounce-in"
                            >
                                <div class="animate-float">
                                    <AvatarDisplay :name="player.avatar" :size="56" />
                                </div>
                                <span class="mt-1 text-sm font-medium truncate max-w-[80px]">{{ player.nickname }}</span>
                            </div>
                            <div v-if="team.players.length === 0" class="col-span-full text-sm opacity-60 py-2">
                                {{ t('host.waiting_players') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Individual mode: flat grid -->
                <div v-else class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4">
                    <div
                        v-for="player in livePlayers"
                        :key="player.id"
                        class="flex flex-col items-center rounded-2xl border border-white/10 bg-white/[0.06] p-3 animate-bounce-in"
                    >
                        <div class="animate-float">
                            <AvatarDisplay :name="player.avatar" :size="56" />
                        </div>
                        <span class="mt-1 text-sm font-medium truncate max-w-[80px]">{{ player.nickname }}</span>
                    </div>
                </div>
                <div v-if="!isTeamMode && livePlayers.length === 0" class="text-center py-12 opacity-60">
                    <p class="text-xl">{{ t('host.waiting_players') }}</p>
                </div>
            </div>

            <!-- Start button -->
            <div class="p-6 text-center">
                <button
                    @click="startGame"
                    :disabled="livePlayers.length === 0"
                    class="rounded-2xl bg-white px-12 py-4 text-xl font-black text-primary-600 shadow-xl shadow-primary-950/20 transition-all hover:-translate-y-1 hover:bg-primary-50 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:translate-y-0 active:scale-95"
                >
                    {{ t('host.start_game') }}
                </button>
            </div>
        </div>

        <!-- COUNTDOWN -->
        <CountdownOverlay v-else-if="gameState === 'countdown'" :value="countdownValue" size="text-[140px]" />

        <!-- QUESTION -->
        <div v-else-if="gameState === 'question'" class="flex flex-1 flex-col text-white">
            <!-- Timer bar -->
            <TimerBar :progress="progress" />

            <!-- Question header -->
            <div class="mx-4 mt-4 flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.07] px-6 py-3 backdrop-blur animate-slide-in-down sm:mx-6">
                <span class="rounded-full bg-white/10 px-3 py-1 text-sm font-bold text-white/60">
                    {{ questionNumber }} / {{ totalQuestions }}
                </span>
                <span class="text-3xl font-black tabular-nums">{{ Math.ceil(timeRemaining) }}</span>
                <span class="rounded-full bg-white/10 px-3 py-1 text-sm font-bold text-white/60">
                    {{ answeredCount }} / {{ totalPlayers }} {{ t('host.answered') }}
                </span>
            </div>

            <!-- Question text -->
            <div class="flex flex-1 items-center justify-center px-6 py-5 animate-slide-in-down" style="animation-delay: 0.1s">
                <div class="max-w-4xl rounded-[2rem] border border-white/10 bg-white/[0.06] px-8 py-6 text-center shadow-xl backdrop-blur">
                    <h2 class="text-3xl font-black leading-tight md:text-4xl">
                        {{ currentQuestion?.question_text }}
                    </h2>
                    <img v-if="currentQuestion?.image_url" :src="currentQuestion.image_url"
                        class="mx-auto mt-5 max-h-60 rounded-2xl border border-white/10 shadow-xl" alt="Question image" />
                </div>
            </div>

            <!-- Answer options -->
            <div class="grid grid-cols-2 gap-3 px-4 pb-4 sm:px-6">
                <div
                    v-for="(answer, idx) in currentQuestion?.answers"
                    :key="answer.id"
                    :class="answerColorMap[answer.color]?.bg"
                    class="flex min-h-[82px] items-center gap-4 rounded-2xl border border-white/15 p-4 shadow-lg transition hover:-translate-y-0.5 animate-slide-in-up"
                    :style="{ animationDelay: `${0.1 + idx * 0.1}s` }"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-black/15 text-2xl">{{ answerColorMap[answer.color]?.shape }}</span>
                    <span class="text-left text-lg font-bold">{{ answer.answer_text }}</span>
                </div>
            </div>

            <!-- Reveal button -->
            <div class="border-t border-white/10 bg-slate-950/30 p-4 text-center backdrop-blur">
                <button @click="revealAnswer" class="rounded-xl bg-white px-8 py-3 font-bold text-primary-600 shadow-lg transition-all hover:-translate-y-0.5 hover:bg-primary-50 active:scale-95">
                    {{ t('host.reveal_answer') }}
                </button>
            </div>
        </div>

        <!-- RESULT / ANSWER REVEAL -->
        <div v-else-if="gameState === 'result'" class="flex flex-1 flex-col text-white">
            <div class="px-6 pb-5 pt-16 text-center animate-slide-in-down sm:pt-8">
                <p v-if="isPoll" class="text-sm font-bold uppercase tracking-wide text-white/70 mb-1">
                    <Icon name="chart" class="mr-1 inline h-4 w-4" /> {{ t('host.poll_results') }}
                </p>
                <h2 class="mx-auto max-w-4xl text-2xl font-black">{{ currentQuestion?.question_text }}</h2>
            </div>

            <div class="grid flex-1 grid-cols-2 gap-4 px-4 sm:px-6">
                <div
                    v-for="(answer, idx) in currentQuestion?.answers"
                    :key="answer.id"
                    :class="[
                        answerColorMap[answer.color]?.bg,
                        !isPoll && isCorrectAnswer(answer.id) ? 'ring-4 ring-white scale-105 animate-pulse-glow' : '',
                        !isPoll && !isCorrectAnswer(answer.id) ? 'animate-fade-dim' : '',
                    ]"
                    class="flex min-h-[120px] flex-col items-center justify-center rounded-2xl border border-white/15 p-4 shadow-xl transition-all"
                >
                    <span class="text-2xl font-bold mb-1">{{ answerColorMap[answer.color]?.shape }} {{ answer.answer_text }}</span>
                    <span v-if="!isPoll && isCorrectAnswer(answer.id)" class="mt-1 flex h-8 w-8 items-center justify-center rounded-full bg-white/20 animate-score-reveal"><Icon name="check" class="h-5 w-5" /></span>
                    <!-- Answer count bar -->
                    <div class="mt-2 w-full">
                        <div class="h-2.5 overflow-hidden rounded-full bg-black/20">
                            <div
                                class="h-full rounded-full bg-white/60 animate-bar-grow"
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
                <button @click="nextQuestion" class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3 text-lg font-bold text-primary-600 shadow-lg transition-all hover:-translate-y-0.5 hover:bg-primary-50 active:scale-95">
                    {{ t('host.next') }} <Icon name="play" class="h-5 w-5" />
                </button>
            </div>
        </div>

        <!-- SCOREBOARD -->
        <div v-else-if="gameState === 'scoreboard'" class="flex flex-1 flex-col text-white">
            <div class="px-6 pb-5 pt-16 text-center animate-slide-in-down sm:pt-8">
                <span class="mb-2 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-500/20 text-primary-200"><Icon name="trophy" class="h-6 w-6" /></span>
                <h2 class="text-3xl font-black">{{ t('host.scoreboard') }}</h2>
            </div>

            <!-- Team standings (team mode) -->
            <div v-if="isTeamMode && teamLeaderboard.length" class="px-8 max-w-2xl mx-auto w-full mb-4">
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <TeamBadge
                        v-for="team in teamLeaderboard"
                        :key="team.team_id"
                        :name="`${team.rank}. ${team.name}`"
                        :color="team.color"
                        :score="team.score"
                    />
                </div>
            </div>

            <div class="flex-1 flex flex-col items-center justify-center px-8 max-w-2xl mx-auto w-full">
                <TransitionGroup name="scoreboard-list" tag="div" class="w-full">
                    <div
                        v-for="(entry, index) in leaderboard"
                        :key="entry.player_id"
                        class="mb-3 flex w-full items-center gap-4 rounded-2xl border border-white/10 bg-white/[0.08] p-4 shadow-lg backdrop-blur transition hover:bg-white/[0.12] animate-slide-in-up"
                        :style="{ animationDelay: `${(leaderboard.length - index) * 0.15}s` }"
                    >
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-black" :class="entry.rank <= 3 ? 'bg-amber-300 text-amber-950' : 'bg-white/10 text-white'">
                            {{ entry.rank }}
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
                <button @click="nextQuestion" class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3 text-lg font-bold text-primary-600 shadow-lg transition-all hover:-translate-y-0.5 hover:bg-primary-50 active:scale-95">
                    {{ t('host.next') }} <Icon name="play" class="h-5 w-5" />
                </button>
            </div>
        </div>

        <!-- FINISHED / PODIUM -->
        <div v-else-if="gameState === 'finished'" class="flex flex-1 flex-col text-white">
            <div class="px-6 pb-3 pt-8 text-center animate-slide-in-down">
                <span class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-300 text-amber-950 shadow-xl shadow-amber-500/20"><Icon name="trophy" class="h-8 w-8" /></span>
                <h2 class="text-4xl font-black">{{ t('host.game_over') }}</h2>
            </div>

            <!-- Team standings (team mode) -->
            <div v-if="isTeamMode && teamLeaderboard.length" class="px-8 max-w-2xl mx-auto w-full mb-2">
                <h3 class="text-center text-sm font-bold uppercase tracking-wide opacity-70 mb-2">
                    {{ t('team.team_standings') }}
                </h3>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <TeamBadge
                        v-for="team in teamLeaderboard"
                        :key="team.team_id"
                        :name="`${team.rank}. ${team.name}`"
                        :color="team.color"
                        :score="team.score"
                    />
                </div>
            </div>

            <!-- Podium -->
            <div class="flex items-end justify-center gap-4 px-8 py-6" v-if="podium.length > 0">
                <!-- 2nd place -->
                <div v-if="podium[1]" class="text-center animate-podium-rise" style="animation-delay: 0.5s">
                    <AvatarDisplay :name="podium[1].avatar" :size="64" class="mx-auto mb-2" />
                    <p class="font-bold text-sm truncate max-w-[100px]">{{ podium[1].nickname }}</p>
                    <p class="text-xs">{{ podium[1].score }}</p>
                    <div class="mt-2 flex h-24 w-24 items-center justify-center rounded-t-2xl border border-white/10 bg-slate-300/20">
                        <span class="text-3xl font-black">2</span>
                    </div>
                </div>
                <!-- 1st place -->
                <div v-if="podium[0]" class="text-center animate-podium-rise" style="animation-delay: 1.2s">
                    <div class="animate-crown-drop" style="animation-delay: 2s">
                        <Icon name="crown" class="mx-auto h-8 w-8 text-amber-200" />
                    </div>
                    <AvatarDisplay :name="podium[0].avatar" :size="80" class="mx-auto mb-2" />
                    <p class="font-bold truncate max-w-[100px]">{{ podium[0].nickname }}</p>
                    <p class="text-sm">{{ podium[0].score }}</p>
                    <div class="mt-2 flex h-32 w-28 items-center justify-center rounded-t-2xl border border-amber-200/20 bg-amber-300/25">
                        <span class="text-4xl font-black">1</span>
                    </div>
                </div>
                <!-- 3rd place -->
                <div v-if="podium[2]" class="text-center animate-podium-rise" style="animation-delay: 0.2s">
                    <AvatarDisplay :name="podium[2].avatar" :size="56" class="mx-auto mb-2" />
                    <p class="font-bold text-sm truncate max-w-[100px]">{{ podium[2].nickname }}</p>
                    <p class="text-xs">{{ podium[2].score }}</p>
                    <div class="mt-2 flex h-16 w-20 items-center justify-center rounded-t-2xl border border-orange-200/20 bg-orange-300/20">
                        <span class="text-2xl font-black">3</span>
                    </div>
                </div>
            </div>

            <!-- Full leaderboard -->
            <div class="flex-1 overflow-y-auto px-8 pb-4">
                <div class="max-w-2xl mx-auto">
                    <div
                        v-for="(entry, index) in finalLeaderboard"
                        :key="entry.player_id"
                        class="mb-2 flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.08] p-3 backdrop-blur animate-slide-in-up"
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
            <div class="flex justify-center gap-4 border-t border-white/10 bg-slate-950/30 p-6 backdrop-blur">
                <a
                    :href="route('game.export', gameSession.id)"
                    class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/10 px-6 py-3 font-bold text-white transition-all hover:-translate-y-0.5 hover:bg-white/20 active:scale-95"
                >
                    <Icon name="download" class="h-5 w-5" /> {{ t('host.download_csv') }}
                </a>
                <button
                    @click="router.visit(route('dashboard'))"
                    class="px-6 py-3 bg-white text-primary-600 font-bold rounded-xl hover:bg-gray-100 transition-all hover:scale-105 active:scale-95"
                >
                    {{ t('host.finish') }}
                </button>
            </div>
        </div>
    </GameLayout>
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
