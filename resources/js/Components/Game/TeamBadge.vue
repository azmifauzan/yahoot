<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: { type: String, required: true },
    color: { type: String, default: 'red' },
    score: { type: Number, default: null },
});

const COLORS = {
    red: 'bg-red-500',
    blue: 'bg-blue-500',
    green: 'bg-green-500',
    yellow: 'bg-yellow-500',
    purple: 'bg-purple-500',
    pink: 'bg-pink-500',
};

const dot = computed(() => COLORS[props.color] ?? 'bg-gray-500');
</script>

<template>
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 text-white text-sm font-semibold">
        <span class="w-2.5 h-2.5 rounded-full" :class="dot"></span>
        {{ name }}
        <span v-if="score !== null" class="opacity-80">· {{ score }}</span>
    </span>
</template>
