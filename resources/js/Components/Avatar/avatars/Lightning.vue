<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Lightning bolt -->
        <path d="M36 4 L22 28 L30 28 L24 60 L46 30 L36 30 L44 4 Z" fill="#FDCB6E" />
        <path d="M37 8 L25 28 L31 28 L26 54 L43 32 L36 32 L42 8 Z" fill="#FFEAA7" />
        <!-- Face -->
        <circle cx="30" cy="28" r="2" fill="#2D3436" />
        <circle cx="38" cy="28" r="2" fill="#2D3436" />
        <circle cx="31" cy="27" r="0.8" fill="white" />
        <circle cx="39" cy="27" r="0.8" fill="white" />
        <path d="M30 33 Q34 36 38 33" stroke="#2D3436" stroke-width="1.5" fill="none" stroke-linecap="round" />
        <!-- Sparkles -->
        <circle cx="16" cy="16" r="2" fill="#FDCB6E" opacity="0.6" />
        <circle cx="52" cy="20" r="1.5" fill="#FDCB6E" opacity="0.6" />
        <circle cx="14" cy="44" r="1.5" fill="#FDCB6E" opacity="0.6" />
    </svg>
</template>
