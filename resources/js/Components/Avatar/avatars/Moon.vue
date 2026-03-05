<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Crescent moon -->
        <circle cx="32" cy="32" r="24" fill="#FFEAA7" />
        <circle cx="42" cy="26" r="20" fill="#F8F9FA" />
        <!-- Face on the crescent -->
        <circle cx="22" cy="30" r="2" fill="#2D3436" />
        <circle cx="30" cy="34" r="2" fill="#2D3436" />
        <circle cx="23" cy="29" r="0.8" fill="white" />
        <circle cx="31" cy="33" r="0.8" fill="white" />
        <path d="M20 38 Q25 42 30 40" stroke="#2D3436" stroke-width="1.5" fill="none" stroke-linecap="round" />
        <!-- Blush -->
        <ellipse cx="18" cy="35" rx="2" ry="1.5" fill="#FFB347" opacity="0.4" />
        <!-- Stars around -->
        <path d="M50 12 L51.5 16 L56 16 L52.5 18.5 L53.5 23 L50 20.5 L46.5 23 L47.5 18.5 L44 16 L48.5 16 Z" fill="#FDCB6E" />
        <path d="M54 40 L55 43 L58 43 L55.5 45 L56.5 48 L54 46 L51.5 48 L52.5 45 L50 43 L53 43 Z" fill="#FDCB6E" />
    </svg>
</template>
