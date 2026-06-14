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

// Looping background music — quiet ambient pads/arpeggios via Web Audio.
const MUSIC_VOLUME = 0.05;
let musicInterval = null;

function musicNote(freq, duration, type) {
    const ac = ctx();
    if (!ac || muted.value) return;

    const osc = ac.createOscillator();
    const gain = ac.createGain();
    const startAt = ac.currentTime;

    osc.type = type;
    osc.frequency.setValueAtTime(freq, startAt);

    gain.gain.setValueAtTime(0.0001, startAt);
    gain.gain.exponentialRampToValueAtTime(MUSIC_VOLUME, startAt + 0.05);
    gain.gain.exponentialRampToValueAtTime(0.0001, startAt + duration);

    osc.connect(gain);
    gain.connect(ac.destination);
    osc.start(startAt);
    osc.stop(startAt + duration);
}

function stopMusic() {
    if (musicInterval) {
        clearInterval(musicInterval);
        musicInterval = null;
    }
}

function startMusicLoop({ step, notes }) {
    stopMusic();
    let i = 0;
    const playStep = () => {
        notes[i % notes.length].forEach(([freq, dur, type]) => musicNote(freq, dur, type));
        i++;
    };
    playStep();
    musicInterval = setInterval(playStep, step * 1000);
}

// Sound theme presets — each maps SFX tones and background music patterns
// to a distinct mood. "none" disables all sound effects and music.
const THEME_PRESETS = {
    classic: {
        tick: { freq: 660, type: 'square' },
        go: { freqs: [660, 880], type: 'square' },
        correct: { freqs: [523, 659, 784], type: 'triangle' },
        wrong: { freqs: [311, 233], type: 'sawtooth' },
        whoosh: { freq: 440, type: 'sine' },
        fanfare: { freqs: [523, 659, 784, 1047], type: 'triangle' },
        lobby: { step: 1.5, notes: [
            [[261.63, 2.6, 'sine']],
            [[329.63, 2.6, 'sine']],
            [[392.00, 2.6, 'sine']],
            [[329.63, 2.6, 'sine']],
        ] },
        game: { step: 0.4, notes: [
            [[130.81, 0.35, 'triangle'], [261.63, 0.3, 'triangle']],
            [[164.81, 0.3, 'triangle']],
            [[196.00, 0.35, 'triangle'], [392.00, 0.3, 'triangle']],
            [[164.81, 0.3, 'triangle']],
        ] },
    },
    chill: {
        tick: { freq: 440, type: 'sine' },
        go: { freqs: [440, 554], type: 'sine' },
        correct: { freqs: [392, 494, 587], type: 'sine' },
        wrong: { freqs: [277, 220], type: 'sine' },
        whoosh: { freq: 330, type: 'sine' },
        fanfare: { freqs: [392, 494, 587, 784], type: 'sine' },
        lobby: { step: 2.2, notes: [
            [[196.00, 3.5, 'sine']],
            [[246.94, 3.5, 'sine']],
            [[293.66, 3.5, 'sine']],
            [[246.94, 3.5, 'sine']],
        ] },
        game: { step: 0.6, notes: [
            [[98.00, 0.5, 'sine'], [196.00, 0.45, 'sine']],
            [[123.47, 0.45, 'sine']],
            [[146.83, 0.5, 'sine'], [293.66, 0.45, 'sine']],
            [[123.47, 0.45, 'sine']],
        ] },
    },
    energetic: {
        tick: { freq: 880, type: 'square' },
        go: { freqs: [880, 1108], type: 'square' },
        correct: { freqs: [659, 880, 1047], type: 'square' },
        wrong: { freqs: [392, 311], type: 'sawtooth' },
        whoosh: { freq: 587, type: 'square' },
        fanfare: { freqs: [659, 880, 1047, 1318], type: 'square' },
        lobby: { step: 1.0, notes: [
            [[329.63, 1.8, 'triangle']],
            [[392.00, 1.8, 'triangle']],
            [[440.00, 1.8, 'triangle']],
            [[392.00, 1.8, 'triangle']],
        ] },
        game: { step: 0.25, notes: [
            [[164.81, 0.2, 'sawtooth'], [329.63, 0.18, 'sawtooth']],
            [[207.65, 0.18, 'sawtooth']],
            [[246.94, 0.2, 'sawtooth'], [493.88, 0.18, 'sawtooth']],
            [[207.65, 0.18, 'sawtooth']],
        ] },
    },
};

export function useSound(theme = 'classic') {
    const silent = theme === 'none';
    const preset = THEME_PRESETS[theme] ?? THEME_PRESETS.classic;

    function toggleMute() {
        unlock();
        muted.value = !muted.value;
        localStorage.setItem(STORAGE_KEY, muted.value ? '1' : '0');
    }

    /**
     * Create/resume the AudioContext inside a user-gesture handler so later,
     * programmatically-triggered sounds (e.g. from WebSocket events) aren't
     * silenced by the browser's autoplay policy.
     */
    function unlock() {
        ctx();
    }

    function playTone({ freq, type }, duration, delay = 0, volume = 0.2) {
        if (silent) return;
        tone(freq, duration, type, delay, volume);
    }

    function playSequence({ freqs, type }, step, dur) {
        if (silent) return;
        sequence(freqs, step, dur, type);
    }

    function playMusic(pattern) {
        if (silent) {
            stopMusic();
            return;
        }
        startMusicLoop(pattern);
    }

    return {
        muted,
        toggleMute,
        unlock,
        // Countdown tick (3, 2, 1)
        tick: () => playTone(preset.tick, 0.12, 0, 0.15),
        // "START!" / go
        go: () => playSequence(preset.go, 0.1, 0.18),
        // Correct answer — rising major chord
        correct: () => playSequence(preset.correct, 0.1, 0.16),
        // Wrong answer — descending buzz
        wrong: () => playSequence(preset.wrong, 0.16, 0.22),
        // Scoreboard / rank reveal
        whoosh: () => playTone(preset.whoosh, 0.25, 0, 0.18),
        // Final podium fanfare
        fanfare: () => playSequence(preset.fanfare, 0.14, 0.22),
        // Background music
        startLobbyMusic: () => playMusic(preset.lobby),
        startGameMusic: () => playMusic(preset.game),
        stopMusic,
    };
}
