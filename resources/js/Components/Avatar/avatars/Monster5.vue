<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Body -->
        <ellipse cx="32" cy="38" rx="20" ry="22" fill="#00CEC9" />
        <!-- Single horn -->
        <path d="M32 6 L28 20 L36 20 Z" fill="#00B4B0" />
        <!-- Eyes -->
        <circle cx="24" cy="32" r="5" fill="white" />
        <circle cx="40" cy="32" r="5" fill="white" />
        <circle cx="24" cy="33" r="3" fill="#2D3436" />
        <circle cx="40" cy="33" r="3" fill="#2D3436" />
        <circle cx="25" cy="32" r="1" fill="white" />
        <circle cx="41" cy="32" r="1" fill="white" />
        <!-- Tongue out -->
        <path d="M28 42 Q32 44 36 42" stroke="#2D3436" stroke-width="1.5" fill="none" stroke-linecap="round" />
        <ellipse cx="32" cy="46" rx="4" ry="4" fill="#FF6B6B" />
        <ellipse cx="32" cy="44" rx="5" ry="3" fill="#00CEC9" />
        <!-- Belly dots -->
        <circle cx="28" cy="48" r="2" fill="#00B4B0" opacity="0.4" />
        <circle cx="36" cy="48" r="2" fill="#00B4B0" opacity="0.4" />
        <circle cx="32" cy="52" r="2" fill="#00B4B0" opacity="0.4" />
        <!-- Feet -->
        <ellipse cx="24" cy="58" rx="5" ry="3" fill="#00B4B0" />
        <ellipse cx="40" cy="58" rx="5" ry="3" fill="#00B4B0" />
    </svg>
</template>
