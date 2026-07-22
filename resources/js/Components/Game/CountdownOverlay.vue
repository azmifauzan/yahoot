<script setup>
import { computed } from 'vue';

const props = defineProps({
    value: { type: [Number, String], default: null },
    size: { type: String, default: 'text-[120px]' },
});

const accentClass = computed(() => ({
    'from-rose-500 to-orange-400': props.value === 3,
    'from-amber-400 to-yellow-300': props.value === 2,
    'from-emerald-500 to-teal-300': props.value === 1,
    'from-primary-500 to-fuchsia-500': props.value === 'START!',
}));

const isStart = computed(() => props.value === 'START!');
</script>

<template>
    <div class="flex flex-1 items-center justify-center px-6">
        <div class="relative flex h-64 w-64 items-center justify-center rounded-[3rem] border border-white/15 bg-white/10 shadow-2xl shadow-primary-950/40 backdrop-blur-xl sm:h-80 sm:w-80">
            <div class="absolute inset-5 rounded-[2.25rem] bg-gradient-to-br opacity-90 blur-sm" :class="accentClass"></div>
            <div
                :key="value"
                class="relative font-black tracking-tight text-white drop-shadow-2xl"
                :class="isStart ? 'text-5xl animate-pop-bounce sm:text-7xl' : `${size} animate-zoom-countdown`"
            >
                {{ value }}
            </div>
        </div>
    </div>
</template>
