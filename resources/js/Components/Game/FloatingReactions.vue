<script setup>
import { computed } from 'vue';

const props = defineProps({
    reactions: {
        type: Array,
        default: () => [], // [{ id, emoji, nickname }]
    },
});

// Give each reaction a stable pseudo-random horizontal position + drift.
const positioned = computed(() =>
    props.reactions.map((r) => {
        const seed = (r.id * 9301 + 49297) % 233280;
        const rand = seed / 233280;
        return {
            ...r,
            left: 5 + Math.round(rand * 80), // 5%–85%
            drift: Math.round((rand - 0.5) * 60), // -30px..30px
            delay: (r.id % 5) * 0.05,
        };
    })
);
</script>

<template>
    <div class="pointer-events-none fixed inset-0 z-40 overflow-hidden">
        <div
            v-for="r in positioned"
            :key="r.id"
            class="floating-reaction absolute bottom-24 flex flex-col items-center"
            :style="{ left: r.left + '%', '--drift': r.drift + 'px', animationDelay: r.delay + 's' }"
        >
            <span class="text-4xl drop-shadow-lg">{{ r.emoji }}</span>
            <span class="text-[10px] text-white/80 font-semibold truncate max-w-[80px]">{{ r.nickname }}</span>
        </div>
    </div>
</template>

<style scoped>
.floating-reaction {
    animation: reaction-float 3s ease-out forwards;
}

@keyframes reaction-float {
    0% {
        opacity: 0;
        transform: translateY(0) translateX(0) scale(0.5);
    }
    15% {
        opacity: 1;
        transform: translateY(-20px) translateX(0) scale(1.1);
    }
    100% {
        opacity: 0;
        transform: translateY(-60vh) translateX(var(--drift)) scale(1);
    }
}
</style>
