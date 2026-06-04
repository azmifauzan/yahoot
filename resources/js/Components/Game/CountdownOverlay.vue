<script setup>
import { computed } from 'vue';

const props = defineProps({
    value: { type: [Number, String], default: null },
    size: { type: String, default: 'text-[120px]' },
});

const bgClass = computed(() => ({
    'bg-red-500': props.value === 3,
    'bg-yellow-500': props.value === 2,
    'bg-green-500': props.value === 1,
    'bg-primary-600': props.value === 'START!',
}));

const isStart = computed(() => props.value === 'START!');
</script>

<template>
    <div class="flex-1 flex items-center justify-center" :class="bgClass">
        <div
            :key="value"
            class="font-extrabold text-white"
            :class="isStart ? 'text-[80px] animate-pop-bounce' : `${size} animate-zoom-countdown`"
        >
            {{ value }}
        </div>
    </div>
</template>
