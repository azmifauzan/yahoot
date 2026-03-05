<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <line x1="32" y1="4" x2="32" y2="14" stroke="#00B894" stroke-width="2" />
        <circle cx="32" cy="4" r="3" fill="#00B894" />
        <rect x="14" y="14" width="36" height="28" rx="6" fill="#00B894" />
        <rect x="18" y="18" width="28" height="20" rx="4" fill="#E8F8F5" />
        <rect x="21" y="23" width="8" height="6" rx="2" fill="#00896B" />
        <rect x="35" y="23" width="8" height="6" rx="2" fill="#00896B" />
        <rect x="23" y="24" width="2" height="2" rx="0.5" fill="#55EFC4" />
        <rect x="37" y="24" width="2" height="2" rx="0.5" fill="#55EFC4" />
        <rect x="26" y="33" width="12" height="3" rx="1.5" fill="#00896B" />
        <rect x="28" y="34" width="2" height="1" fill="#55EFC4" />
        <rect x="32" y="34" width="2" height="1" fill="#55EFC4" />
        <rect x="36" y="34" width="2" height="1" fill="#55EFC4" />
        <rect x="20" y="44" width="24" height="12" rx="4" fill="#00B894" />
        <rect x="28" y="48" width="8" height="4" rx="2" fill="#E8F8F5" />
        <rect x="8" y="44" width="10" height="4" rx="2" fill="#00B894" />
        <rect x="46" y="44" width="10" height="4" rx="2" fill="#00B894" />
    </svg>
</template>
