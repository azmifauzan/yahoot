<script setup>
defineProps({
    value: { type: [Number, String], required: true },
    prefix: { type: String, default: '' },
    delay: { type: String, default: '0s' },
    textClass: { type: String, default: 'text-2xl font-bold' },
});
</script>

<template>
    <p class="animate-score-reveal" :class="textClass" :style="{ animationDelay: delay }">
        {{ prefix }}{{ value }}
    </p>
</template>
