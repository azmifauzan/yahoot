<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <line x1="32" y1="4" x2="32" y2="14" stroke="#6C5CE7" stroke-width="2" />
        <circle cx="32" cy="4" r="3" fill="#A29BFE" />
        <rect x="14" y="14" width="36" height="28" rx="6" fill="#6C5CE7" />
        <rect x="18" y="18" width="28" height="20" rx="4" fill="#E8E4FF" />
        <ellipse cx="25" cy="27" rx="4" ry="5" fill="#4834D4" />
        <ellipse cx="39" cy="27" rx="4" ry="5" fill="#4834D4" />
        <circle cx="26" cy="26" r="1.5" fill="#A29BFE" />
        <circle cx="40" cy="26" r="1.5" fill="#A29BFE" />
        <rect x="27" y="34" width="10" height="2" rx="1" fill="#4834D4" />
        <rect x="20" y="44" width="24" height="12" rx="4" fill="#6C5CE7" />
        <rect x="28" y="48" width="8" height="4" rx="2" fill="#E8E4FF" />
        <rect x="8" y="44" width="10" height="4" rx="2" fill="#6C5CE7" />
        <rect x="46" y="44" width="10" height="4" rx="2" fill="#6C5CE7" />
    </svg>
</template>
