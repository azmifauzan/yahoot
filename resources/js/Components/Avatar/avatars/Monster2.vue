<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Body -->
        <ellipse cx="32" cy="38" rx="20" ry="22" fill="#A29BFE" />
        <!-- Three eyes -->
        <circle cx="20" cy="30" r="6" fill="white" />
        <circle cx="32" cy="26" r="6" fill="white" />
        <circle cx="44" cy="30" r="6" fill="white" />
        <circle cx="20" cy="30" r="3" fill="#6C5CE7" />
        <circle cx="32" cy="26" r="3" fill="#6C5CE7" />
        <circle cx="44" cy="30" r="3" fill="#6C5CE7" />
        <circle cx="21" cy="29" r="1" fill="white" />
        <circle cx="33" cy="25" r="1" fill="white" />
        <circle cx="45" cy="29" r="1" fill="white" />
        <!-- Smile -->
        <path d="M24 42 Q32 50 40 42" stroke="#2D3436" stroke-width="2" fill="none" stroke-linecap="round" />
        <!-- Little arms -->
        <ellipse cx="10" cy="40" rx="5" ry="3" fill="#8B7FE8" transform="rotate(-20 10 40)" />
        <ellipse cx="54" cy="40" rx="5" ry="3" fill="#8B7FE8" transform="rotate(20 54 40)" />
        <!-- Feet -->
        <ellipse cx="24" cy="58" rx="5" ry="3" fill="#8B7FE8" />
        <ellipse cx="40" cy="58" rx="5" ry="3" fill="#8B7FE8" />
    </svg>
</template>
