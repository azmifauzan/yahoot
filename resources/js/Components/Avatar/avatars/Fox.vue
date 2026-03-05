<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Body -->
        <circle cx="32" cy="36" r="20" fill="#E17055" />
        <!-- Ears -->
        <path d="M14 24 L8 12 L22 20 Z" fill="#E17055" />
        <path d="M50 24 L56 12 L42 20 Z" fill="#E17055" />
        <!-- White belly -->
        <ellipse cx="32" cy="44" rx="12" ry="10" fill="#FFEAA7" />
        <!-- Eyes -->
        <ellipse cx="24" cy="32" rx="3" ry="4" fill="#2D3436" />
        <ellipse cx="40" cy="32" rx="3" ry="4" fill="#2D3436" />
        <circle cx="25" cy="31" r="1.5" fill="white" />
        <circle cx="41" cy="31" r="1.5" fill="white" />
        <!-- Nose -->
        <ellipse cx="32" cy="38" rx="2" ry="1.5" fill="#2D3436" />
        <!-- Sly smile -->
        <path d="M26 41 Q32 44 38 41" stroke="#2D3436" stroke-width="1.5" fill="none" stroke-linecap="round" />
    </svg>
</template>
