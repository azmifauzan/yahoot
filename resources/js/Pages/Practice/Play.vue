<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useTimer } from '@/Composables/useTimer';
import { useSound } from '@/Composables/useSound';
import ConfettiEffect from '@/Components/Game/ConfettiEffect.vue';
import GameLayout from '@/Components/Game/GameLayout.vue';
import CountdownOverlay from '@/Components/Game/CountdownOverlay.vue';
import TimerBar from '@/Components/Game/TimerBar.vue';
import ScoreAnimation from '@/Components/Game/ScoreAnimation.vue';
import StreakBadge from '@/Components/Game/StreakBadge.vue';
import PracticeSummary from '@/Components/Game/PracticeSummary.vue';

const { t } = useI18n();

const props = defineProps({
    quiz: Object,
    theme: Object,
});

const sound = useSound(props.quiz?.settings?.sound_theme ?? 'classic');

const POINTS = { standard: 1000, double: 2000, none: 0 };

// Shuffle a copy of the questions for replayability.
function shuffle(arr) {
    const copy = [...arr];
    for (let i = copy.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [copy[i], copy[j]] = [copy[j], copy[i]];
    }
    return copy;
}

const questions = ref([]);
const index = ref(0);
const state = ref('countdown'); // countdown | question | result | summary
const countdownValue = ref(3);

const score = ref(0);
const streak = ref(0);
const correctCount = ref(0);
const totalTimeMs = ref(0);

const selectedAnswer = ref(null);
const lastResult = ref(null); // { isCorrect, pointsEarned, streakBonus }
const showConfetti = ref(false);
const showShake = ref(false);
const submitted = ref(false);

const { timeRemaining, progress, start: startTimer, stop: stopTimer, getElapsedMs } = useTimer();

const currentQuestion = computed(() => questions.value[index.value] ?? null);
const questionNumber = computed(() => index.value + 1);
const totalQuestions = computed(() => questions.value.length);

const answerColors = {
    red: { bg: 'bg-red-500 hover:bg-red-600', shape: '▲' },
    blue: { bg: 'bg-blue-500 hover:bg-blue-600', shape: '◆' },
    yellow: { bg: 'bg-yellow-500 hover:bg-yellow-600', shape: '●' },
    green: { bg: 'bg-green-500 hover:bg-green-600', shape: '■' },
};

// Mirror of ScoringService::calculate so practice scoring matches multiplayer.
function scoreAnswer(question, isCorrect, timeTakenMs) {
    const scored = question.type !== 'poll';
    if (!scored || !isCorrect) {
        if (!isCorrect) streak.value = 0;
        return { isCorrect, pointsEarned: 0, streakBonus: 0 };
    }

    const basePoints = POINTS[question.points] ?? 0;
    if (basePoints === 0) {
        streak.value += 1;
        return { isCorrect, pointsEarned: 0, streakBonus: 0 };
    }

    const timeLimitMs = question.time_limit * 1000;
    let factor = 1 - timeTakenMs / timeLimitMs / 2;
    factor = Math.max(0.5, Math.min(1.0, factor));

    streak.value += 1;
    const streakBonus = Math.min(streak.value * 100, 500);
    const pointsEarned = Math.floor(basePoints * factor);

    return { isCorrect, pointsEarned, streakBonus };
}

async function runCountdown() {
    state.value = 'countdown';
    for (let i = 3; i >= 1; i--) {
        countdownValue.value = i;
        sound.tick();
        await new Promise((r) => setTimeout(r, 1000));
    }
    sound.go();
    startQuestion();
}

function startQuestion() {
    selectedAnswer.value = null;
    lastResult.value = null;
    state.value = 'question';
    startTimer(currentQuestion.value.time_limit);
}

function selectAnswer(answer) {
    if (selectedAnswer.value !== null || state.value !== 'question') return;
    selectedAnswer.value = answer.id;
    stopTimer();
    const elapsed = getElapsedMs();
    totalTimeMs.value += elapsed;
    finishQuestion(answer.is_correct, elapsed);
}

function finishQuestion(isCorrect, elapsed) {
    const result = scoreAnswer(currentQuestion.value, isCorrect, elapsed);
    lastResult.value = result;
    score.value += result.pointsEarned + result.streakBonus;
    if (isCorrect) correctCount.value += 1;
    state.value = 'result';

    if (isCorrect && currentQuestion.value.type !== 'poll') {
        showConfetti.value = true;
        sound.correct();
        setTimeout(() => { showConfetti.value = false; }, 2500);
    } else if (currentQuestion.value.type !== 'poll') {
        showShake.value = true;
        sound.wrong();
        setTimeout(() => { showShake.value = false; }, 600);
    }
}

function next() {
    if (index.value + 1 >= questions.value.length) {
        finish();
        return;
    }
    index.value += 1;
    startQuestion();
}

function finish() {
    state.value = 'summary';
    sound.fanfare();
    if (correctCount.value >= Math.ceil(totalQuestions.value / 2)) {
        showConfetti.value = true;
    }
    persist();
}

async function persist() {
    if (submitted.value) return;
    submitted.value = true;
    try {
        await window.axios.post(`/quizzes/${props.quiz.id}/practice`, {
            score: score.value,
            correct_count: correctCount.value,
            total_questions: totalQuestions.value,
        });
    } catch {
        // Saving practice history is best-effort; ignore failures.
    }
}

function restart() {
    questions.value = shuffle(props.quiz.questions);
    index.value = 0;
    score.value = 0;
    streak.value = 0;
    correctCount.value = 0;
    totalTimeMs.value = 0;
    submitted.value = false;
    showConfetti.value = false;
    runCountdown();
}

function goHome() {
    router.visit('/');
}

// Auto-advance to result when the timer expires with no answer.
watch(timeRemaining, (val) => {
    if (val <= 0 && state.value === 'question' && selectedAnswer.value === null) {
        const elapsed = currentQuestion.value.time_limit * 1000;
        totalTimeMs.value += elapsed;
        finishQuestion(false, elapsed);
    }
});

onMounted(() => {
    restart();
});

onUnmounted(() => {
    stopTimer();
    sound.stopMusic();
});
</script>

<template>
    <Head :title="t('practice.practice_mode')" />
    <GameLayout :shake="showShake">
        <ConfettiEffect v-if="showConfetti" :duration="4000" @complete="showConfetti = false" />

        <!-- Mute toggle -->
        <button
            @click="sound.toggleMute()"
            class="fixed top-3 right-3 z-50 w-10 h-10 rounded-full bg-black/30 hover:bg-black/50 text-white text-lg flex items-center justify-center transition-all"
            :title="sound.muted.value ? t('play.unmute') : t('play.mute')"
        >
            {{ sound.muted.value ? '🔇' : '🔊' }}
        </button>

        <!-- Countdown -->
        <CountdownOverlay v-if="state === 'countdown'" :value="countdownValue" />

        <!-- Question -->
        <div v-else-if="state === 'question'" class="flex-1 flex flex-col bg-gray-900">
            <div class="p-4 animate-slide-in-down">
                <div class="flex items-center justify-between text-white mb-3">
                    <span class="text-sm font-bold text-white/70">{{ questionNumber }} / {{ totalQuestions }}</span>
                    <span class="text-sm font-bold text-white/70">{{ score }} {{ t('practice.points') }}</span>
                </div>
                <TimerBar :progress="progress" :time-remaining="timeRemaining" show-number rounded />
                <p class="text-xl font-bold text-white text-center mt-4">{{ currentQuestion?.question_text }}</p>
            </div>

            <div
                class="flex-1 p-3"
                :class="currentQuestion?.type === 'true_false' ? 'grid grid-cols-1 gap-3' : 'grid grid-cols-2 gap-3'"
            >
                <button
                    v-for="(answer, idx) in currentQuestion?.answers"
                    :key="answer.id"
                    @click="selectAnswer(answer)"
                    :class="answerColors[answer.color]?.bg"
                    class="rounded-2xl flex flex-col items-center justify-center text-white font-bold shadow-lg active:scale-95 transition-transform min-h-[120px] p-4 animate-slide-in-up"
                    :style="{ animationDelay: `${idx * 0.1}s` }"
                >
                    <span class="text-4xl">{{ answerColors[answer.color]?.shape }}</span>
                    <span class="text-lg mt-2">{{ answer.answer_text }}</span>
                </button>
            </div>
        </div>

        <!-- Result -->
        <div
            v-else-if="state === 'result'"
            class="flex-1 flex items-center justify-center"
            :class="currentQuestion?.type === 'poll' ? 'bg-primary-500' : lastResult?.isCorrect ? 'bg-green-500' : 'bg-red-500'"
        >
            <div class="text-center text-white p-8">
                <template v-if="currentQuestion?.type === 'poll'">
                    <div class="text-6xl mb-4 animate-pop-bounce">📊</div>
                    <h2 class="text-3xl font-extrabold animate-slide-in-up">{{ t('play.poll_thanks') }}</h2>
                </template>
                <template v-else-if="lastResult?.isCorrect">
                    <div class="text-6xl mb-4 animate-pop-bounce">🎉</div>
                    <h2 class="text-3xl font-extrabold mb-2 animate-slide-in-up">{{ t('play.correct') }}</h2>
                    <ScoreAnimation
                        :value="(lastResult?.pointsEarned || 0) + (lastResult?.streakBonus || 0)"
                        prefix="+"
                        delay="0.3s"
                    />
                    <StreakBadge :streak="streak" />
                </template>
                <template v-else>
                    <div class="text-6xl mb-4">😢</div>
                    <h2 class="text-3xl font-extrabold animate-slide-in-up">{{ t('play.wrong') }}</h2>
                </template>

                <button
                    @click="next"
                    class="mt-8 px-8 py-3 bg-white text-primary-600 font-bold rounded-xl hover:bg-gray-100 transition-all hover:scale-105 active:scale-95 animate-slide-in-up"
                    style="animation-delay: 0.5s"
                >
                    {{ questionNumber >= totalQuestions ? t('practice.see_results') : t('practice.next_question') }}
                </button>
            </div>
        </div>

        <!-- Summary -->
        <div
            v-else-if="state === 'summary'"
            class="flex-1 flex items-center justify-center bg-gradient-to-br from-primary-500 via-purple-500 to-pink-500"
        >
            <PracticeSummary
                :score="score"
                :correct-count="correctCount"
                :total-questions="totalQuestions"
                :avg-time-ms="totalQuestions > 0 ? totalTimeMs / totalQuestions : 0"
                @retry="restart"
                @home="goHome"
            />
        </div>
    </GameLayout>
</template>
