import { ref, onUnmounted } from 'vue';

export function useTimer() {
    const timeRemaining = ref(0);
    const totalTime = ref(0);
    const isRunning = ref(false);
    const progress = ref(100);
    const isFrozen = ref(false);
    let intervalId = null;
    let startTime = null;
    let frozenMs = 0; // accumulated paused time (freeze_timer power-up)
    let freezeStart = null;
    let freezeTimeout = null;

    function currentFreezeMs() {
        return freezeStart !== null ? Date.now() - freezeStart : 0;
    }

    function start(durationSeconds) {
        stop();
        totalTime.value = durationSeconds;
        timeRemaining.value = durationSeconds;
        progress.value = 100;
        isRunning.value = true;
        startTime = Date.now();
        frozenMs = 0;
        freezeStart = null;
        isFrozen.value = false;

        intervalId = setInterval(() => {
            const elapsed = (Date.now() - startTime - frozenMs - currentFreezeMs()) / 1000;
            timeRemaining.value = Math.max(0, durationSeconds - elapsed);
            progress.value = (timeRemaining.value / totalTime.value) * 100;

            if (timeRemaining.value <= 0) {
                stop();
            }
        }, 100);
    }

    // Pause the countdown for the given duration (freeze_timer power-up).
    function freeze(durationMs = 5000) {
        if (!isRunning.value || isFrozen.value) return;
        isFrozen.value = true;
        freezeStart = Date.now();
        freezeTimeout = setTimeout(() => {
            frozenMs += Date.now() - freezeStart;
            freezeStart = null;
            isFrozen.value = false;
            freezeTimeout = null;
        }, durationMs);
    }

    function stop() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
        if (freezeTimeout) {
            clearTimeout(freezeTimeout);
            freezeTimeout = null;
        }
        freezeStart = null;
        isFrozen.value = false;
        isRunning.value = false;
    }

    function getElapsedMs() {
        if (!startTime) return 0;
        return Date.now() - startTime - frozenMs - currentFreezeMs();
    }

    onUnmounted(() => {
        stop();
    });

    return {
        timeRemaining,
        totalTime,
        isRunning,
        isFrozen,
        progress,
        start,
        stop,
        freeze,
        getElapsedMs,
    };
}
