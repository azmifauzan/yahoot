<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Body -->
        <ellipse cx="32" cy="36" rx="22" ry="20" fill="#E17055" />
        <!-- Wings -->
        <path d="M8 30 Q4 20 14 24 L14 36 Z" fill="#D35D47" />
        <path d="M56 30 Q60 20 50 24 L50 36 Z" fill="#D35D47" />
        <!-- Eyes -->
        <circle cx="25" cy="30" r="6" fill="white" />
        <circle cx="39" cy="30" r="6" fill="white" />
        <circle cx="26" cy="31" r="3" fill="#2D3436" />
        <circle cx="40" cy="31" r="3" fill="#2D3436" />
        <circle cx="27" cy="30" r="1.2" fill="white" />
        <circle cx="41" cy="30" r="1.2" fill="white" />
        <!-- Eyebrows (cute angry) -->
        <line x1="20" y1="22" x2="28" y2="24" stroke="#C44539" stroke-width="2" stroke-linecap="round" />
        <line x1="44" y1="22" x2="36" y2="24" stroke="#C44539" stroke-width="2" stroke-linecap="round" />
        <!-- Smile -->
        <path d="M26 42 Q32 46 38 42" stroke="#2D3436" stroke-width="2" fill="none" stroke-linecap="round" />
        <!-- Tail -->
        <path d="M50 46 Q58 42 55 50 Q52 54 48 48" fill="#D35D47" />
        <!-- Feet -->
        <ellipse cx="24" cy="54" rx="6" ry="3" fill="#D35D47" />
        <ellipse cx="40" cy="54" rx="6" ry="3" fill="#D35D47" />
    </svg>
</template>
