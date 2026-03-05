<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <line x1="32" y1="4" x2="32" y2="14" stroke="#FF6B6B" stroke-width="2" />
        <circle cx="32" cy="4" r="3" fill="#FF6B6B" />
        <rect x="14" y="14" width="36" height="28" rx="6" fill="#FF6B6B" />
        <rect x="18" y="18" width="28" height="20" rx="4" fill="#FFF5F5" />
        <circle cx="25" cy="27" r="4" fill="#D63031" />
        <circle cx="39" cy="27" r="4" fill="#D63031" />
        <circle cx="26" cy="26" r="1.5" fill="white" />
        <circle cx="40" cy="26" r="1.5" fill="white" />
        <path d="M25 35 Q32 40 39 35" stroke="#D63031" stroke-width="2" fill="none" stroke-linecap="round" />
        <rect x="20" y="44" width="24" height="12" rx="4" fill="#FF6B6B" />
        <rect x="28" y="48" width="8" height="4" rx="2" fill="#FFF5F5" />
        <rect x="8" y="44" width="10" height="4" rx="2" fill="#FF6B6B" />
        <rect x="46" y="44" width="10" height="4" rx="2" fill="#FF6B6B" />
    </svg>
</template>
