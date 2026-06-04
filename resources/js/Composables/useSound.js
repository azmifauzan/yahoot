import { ref } from 'vue';

const STORAGE_KEY = 'yahoot_muted';
const muted = ref(localStorage.getItem(STORAGE_KEY) === '1');

let audioCtx = null;

function ctx() {
    if (typeof window === 'undefined') return null;
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return null;
    if (!audioCtx) audioCtx = new AudioCtx();
    if (audioCtx.state === 'suspended') audioCtx.resume();
    return audioCtx;
}

/**
 * Play a single tone.
 *
 * @param {number} freq      frequency in Hz
 * @param {number} duration  seconds
 * @param {string} type      oscillator type
 * @param {number} delay     seconds to wait before start
 * @param {number} volume    0..1
 */
function tone(freq, duration, type = 'sine', delay = 0, volume = 0.2) {
    const ac = ctx();
    if (!ac || muted.value) return;

    const osc = ac.createOscillator();
    const gain = ac.createGain();
    const startAt = ac.currentTime + delay;

    osc.type = type;
    osc.frequency.setValueAtTime(freq, startAt);

    gain.gain.setValueAtTime(0.0001, startAt);
    gain.gain.exponentialRampToValueAtTime(volume, startAt + 0.01);
    gain.gain.exponentialRampToValueAtTime(0.0001, startAt + duration);

    osc.connect(gain);
    gain.connect(ac.destination);
    osc.start(startAt);
    osc.stop(startAt + duration);
}

/**
 * @param {number[]} freqs   ascending/descending notes
 * @param {number}   step    seconds between notes
 * @param {number}   dur     per-note duration
 * @param {string}   type    oscillator type
 */
function sequence(freqs, step = 0.12, dur = 0.14, type = 'triangle') {
    freqs.forEach((f, i) => tone(f, dur, type, i * step));
}

export function useSound() {
    function toggleMute() {
        muted.value = !muted.value;
        localStorage.setItem(STORAGE_KEY, muted.value ? '1' : '0');
    }

    return {
        muted,
        toggleMute,
        // Countdown tick (3, 2, 1)
        tick: () => tone(660, 0.12, 'square', 0, 0.15),
        // "START!" / go
        go: () => sequence([660, 880], 0.1, 0.18, 'square'),
        // Correct answer — rising major chord
        correct: () => sequence([523, 659, 784], 0.1, 0.16, 'triangle'),
        // Wrong answer — descending buzz
        wrong: () => sequence([311, 233], 0.16, 0.22, 'sawtooth'),
        // Scoreboard / rank reveal
        whoosh: () => tone(440, 0.25, 'sine', 0, 0.18),
        // Final podium fanfare
        fanfare: () => sequence([523, 659, 784, 1047], 0.14, 0.22, 'triangle'),
    };
}
