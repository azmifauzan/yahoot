<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Body - blob shape -->
        <ellipse cx="32" cy="38" rx="22" ry="20" fill="#55EFC4" />
        <!-- Horns -->
        <ellipse cx="20" cy="14" rx="4" ry="8" fill="#00B894" />
        <ellipse cx="44" cy="14" rx="4" ry="8" fill="#00B894" />
        <!-- One big eye -->
        <circle cx="32" cy="32" r="10" fill="white" />
        <circle cx="32" cy="32" r="5" fill="#2D3436" />
        <circle cx="34" cy="30" r="2" fill="white" />
        <!-- Big smile -->
        <path d="M22 44 Q32 52 42 44" stroke="#2D3436" stroke-width="2" fill="none" stroke-linecap="round" />
        <!-- Teeth -->
        <rect x="28" y="44" width="3" height="4" rx="1" fill="white" />
        <rect x="33" y="44" width="3" height="4" rx="1" fill="white" />
        <!-- Feet -->
        <ellipse cx="22" cy="56" rx="6" ry="3" fill="#00B894" />
        <ellipse cx="42" cy="56" rx="6" ry="3" fill="#00B894" />
    </svg>
</template>
