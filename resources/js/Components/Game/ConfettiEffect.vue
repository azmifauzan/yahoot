<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    duration: { type: Number, default: 5000 },
    particleCount: { type: Number, default: 80 },
});

const emit = defineEmits(['complete']);
const particles = ref([]);
const active = ref(true);

const colors = ['#FF6B6B', '#4ECDC4', '#FFD93D', '#6C5CE7', '#FF85B3', '#00CEC9', '#FD79A8', '#74B9FF'];
const shapes = ['circle', 'square', 'triangle'];

function createParticles() {
    const result = [];
    for (let i = 0; i < props.particleCount; i++) {
        result.push({
            id: i,
            color: colors[Math.floor(Math.random() * colors.length)],
            shape: shapes[Math.floor(Math.random() * shapes.length)],
            left: Math.random() * 100,
            delay: Math.random() * 2,
            duration: 2 + Math.random() * 3,
            size: 6 + Math.random() * 8,
            rotation: Math.random() * 360,
            swayAmount: -50 + Math.random() * 100,
        });
    }
    return result;
}

let timer = null;

onMounted(() => {
    particles.value = createParticles();
    timer = setTimeout(() => {
        active.value = false;
        emit('complete');
    }, props.duration);
});

onBeforeUnmount(() => {
    clearTimeout(timer);
});
</script>

<template>
    <div v-if="active" class="confetti-container" aria-hidden="true">
        <div
            v-for="p in particles"
            :key="p.id"
            class="confetti-particle"
            :class="`confetti-${p.shape}`"
            :style="{
                left: `${p.left}%`,
                width: `${p.size}px`,
                height: `${p.size}px`,
                backgroundColor: p.shape !== 'triangle' ? p.color : 'transparent',
                borderBottomColor: p.shape === 'triangle' ? p.color : undefined,
                animationDelay: `${p.delay}s`,
                animationDuration: `${p.duration}s`,
                '--sway': `${p.swayAmount}px`,
                transform: `rotate(${p.rotation}deg)`,
            }"
        ></div>
    </div>
</template>

<style scoped>
.confetti-container {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 9999;
    overflow: hidden;
}

.confetti-particle {
    position: absolute;
    top: -20px;
    animation: confetti-fall linear forwards;
    will-change: transform;
}

.confetti-circle {
    border-radius: 50%;
}

.confetti-square {
    border-radius: 2px;
}

.confetti-triangle {
    width: 0 !important;
    height: 0 !important;
    background: transparent !important;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-bottom: 10px solid;
}

@keyframes confetti-fall {
    0% {
        transform: translateY(0) translateX(0) rotate(0deg);
        opacity: 1;
    }
    25% {
        transform: translateY(25vh) translateX(var(--sway)) rotate(180deg);
    }
    50% {
        transform: translateY(50vh) translateX(calc(var(--sway) * -0.5)) rotate(360deg);
    }
    75% {
        transform: translateY(75vh) translateX(var(--sway)) rotate(540deg);
        opacity: 0.8;
    }
    100% {
        transform: translateY(110vh) translateX(calc(var(--sway) * -1)) rotate(720deg);
        opacity: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .confetti-particle {
        animation: none;
        display: none;
    }
}
</style>
