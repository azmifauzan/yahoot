<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Body - squat -->
        <ellipse cx="32" cy="40" rx="24" ry="18" fill="#FDCB6E" />
        <!-- Eye stalks -->
        <line x1="24" y1="24" x2="24" y2="16" stroke="#F9CA24" stroke-width="3" />
        <line x1="40" y1="24" x2="40" y2="16" stroke="#F9CA24" stroke-width="3" />
        <circle cx="24" cy="14" r="5" fill="white" />
        <circle cx="40" cy="14" r="5" fill="white" />
        <circle cx="24" cy="14" r="2.5" fill="#2D3436" />
        <circle cx="40" cy="14" r="2.5" fill="#2D3436" />
        <circle cx="25" cy="13" r="1" fill="white" />
        <circle cx="41" cy="13" r="1" fill="white" />
        <!-- Smile -->
        <path d="M24 44 Q32 50 40 44" stroke="#2D3436" stroke-width="2" fill="none" stroke-linecap="round" />
        <!-- Spots -->
        <circle cx="20" cy="38" r="3" fill="#F0B800" opacity="0.5" />
        <circle cx="38" cy="34" r="2" fill="#F0B800" opacity="0.5" />
        <circle cx="44" cy="42" r="2.5" fill="#F0B800" opacity="0.5" />
        <!-- Feet -->
        <ellipse cx="22" cy="56" rx="7" ry="3" fill="#F9CA24" />
        <ellipse cx="42" cy="56" rx="7" ry="3" fill="#F9CA24" />
    </svg>
</template>
