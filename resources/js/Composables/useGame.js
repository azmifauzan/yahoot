import { ref, computed, onUnmounted } from 'vue';
import axios from 'axios';

export function useGame(gameSessionId) {
    const gameState = ref('lobby'); // lobby, countdown, question, answering, result, scoreboard, finished
    const players = ref([]);
    const currentQuestion = ref(null);
    const questionNumber = ref(0);
    const totalQuestions = ref(0);
    const timeLimit = ref(0);
    const answeredCount = ref(0);
    const totalPlayers = ref(0);
    const leaderboard = ref([]);
    const playerPositions = ref({});
    const correctAnswer = ref(null);
    const answerStats = ref([]);
    const playerResults = ref([]);
    const isPoll = ref(false);
    const finalLeaderboard = ref([]);
    const podium = ref([]);
    const myResult = ref(null);
    const reactions = ref([]); // active floating reactions: { id, emoji, nickname }
    const channel = ref(null);
    let reactionSeq = 0;

    function joinChannel() {
        if (!window.Echo || !gameSessionId) return;

        channel.value = window.Echo.channel(`game.${gameSessionId}`)
            .listen('PlayerJoined', (e) => {
                players.value.push(e.player);
                totalPlayers.value = e.totalPlayers;
            })
            .listen('PlayerLeft', (e) => {
                players.value = players.value.filter(p => p.id !== e.player.id);
                totalPlayers.value = e.totalPlayers;
            })
            .listen('GameStarted', (e) => {
                totalQuestions.value = e.totalQuestions;
                gameState.value = 'countdown';
            })
            .listen('QuestionStarted', (e) => {
                currentQuestion.value = e.question;
                questionNumber.value = e.questionNumber;
                totalQuestions.value = e.totalQuestions;
                timeLimit.value = e.timeLimit;
                answeredCount.value = 0;
                myResult.value = null;
                gameState.value = 'question';
            })
            .listen('AnswerSubmitted', (e) => {
                answeredCount.value = e.answeredCount;
                totalPlayers.value = e.totalPlayers;
            })
            .listen('AnswerRevealed', (e) => {
                correctAnswer.value = e.correctAnswer;
                answerStats.value = e.stats;
                playerResults.value = e.playerResults;
                isPoll.value = e.isPoll ?? false;
                gameState.value = 'result';
            })
            .listen('ScoreboardUpdated', (e) => {
                leaderboard.value = e.leaderboard;
                playerPositions.value = e.playerPositions;
                gameState.value = 'scoreboard';
            })
            .listen('GameEnded', (e) => {
                finalLeaderboard.value = e.finalLeaderboard;
                podium.value = e.podium;
                gameState.value = 'finished';
            })
            .listen('GameCancelled', (e) => {
                gameState.value = 'cancelled';
            })
            .listen('ReactionSent', (e) => {
                const id = ++reactionSeq;
                reactions.value.push({ id, emoji: e.emoji, nickname: e.nickname });
                setTimeout(() => {
                    reactions.value = reactions.value.filter(r => r.id !== id);
                }, 3000);
            });
    }

    async function sendReaction(playerId, emoji, playerToken) {
        try {
            await axios.post(`/api/games/${gameSessionId}/react`, {
                player_id: playerId,
                emoji,
                player_token: playerToken,
            });
            return true;
        } catch (error) {
            // Throttled (429) or disabled (403) — fail silently for UX.
            return false;
        }
    }

    function leaveChannel() {
        if (channel.value) {
            window.Echo.leave(`game.${gameSessionId}`);
            channel.value = null;
        }
    }

    async function submitAnswer(playerId, answerId, timeTaken, playerToken) {
        try {
            const response = await axios.post(`/api/games/${gameSessionId}/answer`, {
                player_id: playerId,
                answer_id: answerId,
                time_taken: timeTaken,
                player_token: playerToken,
            });
            myResult.value = response.data;
            gameState.value = 'answering';
            return response.data;
        } catch (error) {
            console.error('Failed to submit answer:', error);
            return null;
        }
    }

    async function fetchStatus() {
        try {
            const response = await axios.get(`/api/games/${gameSessionId}/status`);
            return response.data;
        } catch (error) {
            console.error('Failed to fetch game status:', error);
            return null;
        }
    }

    onUnmounted(() => {
        leaveChannel();
    });

    return {
        gameState,
        players,
        currentQuestion,
        questionNumber,
        totalQuestions,
        timeLimit,
        answeredCount,
        totalPlayers,
        leaderboard,
        playerPositions,
        correctAnswer,
        answerStats,
        playerResults,
        isPoll,
        finalLeaderboard,
        podium,
        myResult,
        reactions,
        joinChannel,
        leaveChannel,
        submitAnswer,
        sendReaction,
        fetchStatus,
    };
}
