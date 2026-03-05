import { ref, onUnmounted } from 'vue';

export function useTimer() {
    const timeRemaining = ref(0);
    const totalTime = ref(0);
    const isRunning = ref(false);
    const progress = ref(100);
    let intervalId = null;
    let startTime = null;

    function start(durationSeconds) {
        stop();
        totalTime.value = durationSeconds;
        timeRemaining.value = durationSeconds;
        progress.value = 100;
        isRunning.value = true;
        startTime = Date.now();

        intervalId = setInterval(() => {
            const elapsed = (Date.now() - startTime) / 1000;
            timeRemaining.value = Math.max(0, durationSeconds - elapsed);
            progress.value = (timeRemaining.value / totalTime.value) * 100;

            if (timeRemaining.value <= 0) {
                stop();
            }
        }, 100);
    }

    function stop() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
        isRunning.value = false;
    }

    function getElapsedMs() {
        if (!startTime) return 0;
        return Date.now() - startTime;
    }

    onUnmounted(() => {
        stop();
    });

    return {
        timeRemaining,
        totalTime,
        isRunning,
        progress,
        start,
        stop,
        getElapsedMs,
    };
}
