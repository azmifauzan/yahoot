<script setup>
defineProps({
    size: {
        type: Number,
        default: 64,
    },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Body -->
        <circle cx="32" cy="36" r="20" fill="#FFB347" />
        <!-- Ears -->
        <path d="M16 20 L12 8 L24 16 Z" fill="#FFB347" />
        <path d="M48 20 L52 8 L40 16 Z" fill="#FFB347" />
        <path d="M17 19 L14 10 L23 16 Z" fill="#FF9B6B" />
        <path d="M47 19 L50 10 L41 16 Z" fill="#FF9B6B" />
        <!-- Eyes -->
        <ellipse cx="24" cy="33" rx="3.5" ry="4" fill="#2D3436" />
        <ellipse cx="40" cy="33" rx="3.5" ry="4" fill="#2D3436" />
        <circle cx="25.5" cy="31.5" r="1.5" fill="white" />
        <circle cx="41.5" cy="31.5" r="1.5" fill="white" />
        <!-- Nose -->
        <ellipse cx="32" cy="39" rx="2.5" ry="2" fill="#FF6B6B" />
        <!-- Mouth -->
        <path d="M28 42 Q32 46 36 42" stroke="#2D3436" stroke-width="1.5" fill="none" stroke-linecap="round" />
        <!-- Whiskers -->
        <line x1="8" y1="35" x2="20" y2="37" stroke="#2D3436" stroke-width="1" stroke-linecap="round" />
        <line x1="8" y1="40" x2="20" y2="40" stroke="#2D3436" stroke-width="1" stroke-linecap="round" />
        <line x1="44" y1="37" x2="56" y2="35" stroke="#2D3436" stroke-width="1" stroke-linecap="round" />
        <line x1="44" y1="40" x2="56" y2="40" stroke="#2D3436" stroke-width="1" stroke-linecap="round" />
    </svg>
</template>
