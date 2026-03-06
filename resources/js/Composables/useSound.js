import { ref } from 'vue';

const isMuted = ref(false);

/**
 * Composable for managing game sound effects using Web Audio API.
 * Generates simple tones programmatically — no external audio files needed.
 */
export function useSound() {
    let audioContext = null;

    function getContext() {
        if (!audioContext) {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
        }
        return audioContext;
    }

    function playTone(frequency, duration = 0.2, type = 'sine', volume = 0.3) {
        if (isMuted.value) return;

        try {
            const ctx = getContext();
            const oscillator = ctx.createOscillator();
            const gainNode = ctx.createGain();

            oscillator.type = type;
            oscillator.frequency.setValueAtTime(frequency, ctx.currentTime);

            gainNode.gain.setValueAtTime(volume, ctx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + duration);

            oscillator.connect(gainNode);
            gainNode.connect(ctx.destination);

            oscillator.start(ctx.currentTime);
            oscillator.stop(ctx.currentTime + duration);
        } catch {
            // Web Audio API not available in this environment
        }
    }

    function playCorrect() {
        playTone(523.25, 0.15, 'sine', 0.2);
        setTimeout(() => playTone(659.25, 0.15, 'sine', 0.2), 150);
        setTimeout(() => playTone(783.99, 0.3, 'sine', 0.2), 300);
    }

    function playWrong() {
        playTone(200, 0.3, 'square', 0.15);
        setTimeout(() => playTone(150, 0.4, 'square', 0.15), 300);
    }

    function playCountdown() {
        playTone(440, 0.15, 'sine', 0.2);
    }

    function playGameStart() {
        playTone(440, 0.1, 'sine', 0.2);
        setTimeout(() => playTone(554.37, 0.1, 'sine', 0.2), 100);
        setTimeout(() => playTone(659.25, 0.1, 'sine', 0.2), 200);
        setTimeout(() => playTone(880, 0.3, 'sine', 0.2), 300);
    }

    function playClick() {
        playTone(800, 0.05, 'sine', 0.1);
    }

    function playReveal() {
        playTone(440, 0.1, 'triangle', 0.2);
        setTimeout(() => playTone(660, 0.2, 'triangle', 0.2), 100);
    }

    function playVictory() {
        const notes = [523.25, 659.25, 783.99, 1046.5];
        notes.forEach((freq, i) => {
            setTimeout(() => playTone(freq, 0.2, 'sine', 0.2), i * 150);
        });
    }

    function toggleMute() {
        isMuted.value = !isMuted.value;
    }

    return {
        isMuted,
        playCorrect,
        playWrong,
        playCountdown,
        playGameStart,
        playClick,
        playReveal,
        playVictory,
        toggleMute,
    };
}
