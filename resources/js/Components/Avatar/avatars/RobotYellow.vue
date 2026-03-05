<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <line x1="32" y1="4" x2="32" y2="14" stroke="#FDCB6E" stroke-width="2" />
        <circle cx="32" cy="4" r="3" fill="#FDCB6E" />
        <rect x="14" y="14" width="36" height="28" rx="6" fill="#FDCB6E" />
        <rect x="18" y="18" width="28" height="20" rx="4" fill="#FFEAA7" />
        <circle cx="25" cy="27" r="4" fill="#E17055" />
        <circle cx="39" cy="27" r="4" fill="#E17055" />
        <circle cx="26" cy="26" r="1.5" fill="white" />
        <circle cx="40" cy="26" r="1.5" fill="white" />
        <path d="M26 34 L32 37 L38 34" stroke="#E17055" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
        <rect x="20" y="44" width="24" height="12" rx="4" fill="#FDCB6E" />
        <circle cx="32" cy="50" r="3" fill="#FFEAA7" />
        <rect x="8" y="44" width="10" height="4" rx="2" fill="#FDCB6E" />
        <rect x="46" y="44" width="10" height="4" rx="2" fill="#FDCB6E" />
    </svg>
</template>
