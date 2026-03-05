<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <line x1="32" y1="4" x2="32" y2="14" stroke="#FD79A8" stroke-width="2" />
        <circle cx="32" cy="4" r="3" fill="#FD79A8" />
        <rect x="14" y="14" width="36" height="28" rx="6" fill="#FD79A8" />
        <rect x="18" y="18" width="28" height="20" rx="4" fill="#FFE8F0" />
        <circle cx="25" cy="26" r="3.5" fill="#E84393" />
        <circle cx="39" cy="26" r="3.5" fill="#E84393" />
        <circle cx="26" cy="25" r="1.5" fill="white" />
        <circle cx="40" cy="25" r="1.5" fill="white" />
        <!-- Cute blush -->
        <ellipse cx="19" cy="31" rx="3" ry="2" fill="#FFB8C6" opacity="0.6" />
        <ellipse cx="45" cy="31" rx="3" ry="2" fill="#FFB8C6" opacity="0.6" />
        <path d="M27 34 Q32 38 37 34" stroke="#E84393" stroke-width="2" fill="none" stroke-linecap="round" />
        <rect x="20" y="44" width="24" height="12" rx="4" fill="#FD79A8" />
        <circle cx="32" cy="50" r="3" fill="#FFE8F0" />
        <rect x="8" y="44" width="10" height="4" rx="2" fill="#FD79A8" />
        <rect x="46" y="44" width="10" height="4" rx="2" fill="#FD79A8" />
    </svg>
</template>
