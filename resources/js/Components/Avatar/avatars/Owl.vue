<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Body -->
        <circle cx="32" cy="36" r="20" fill="#A29BFE" />
        <!-- Ear tufts -->
        <path d="M16 22 L10 10 L20 16 Z" fill="#786FA6" />
        <path d="M48 22 L54 10 L44 16 Z" fill="#786FA6" />
        <!-- Eyes - big owl eyes -->
        <circle cx="24" cy="32" r="8" fill="#FFEAA7" />
        <circle cx="40" cy="32" r="8" fill="#FFEAA7" />
        <circle cx="24" cy="32" r="4.5" fill="#2D3436" />
        <circle cx="40" cy="32" r="4.5" fill="#2D3436" />
        <circle cx="25.5" cy="30.5" r="2" fill="white" />
        <circle cx="41.5" cy="30.5" r="2" fill="white" />
        <!-- Beak -->
        <path d="M30 38 L32 42 L34 38 Z" fill="#E17055" />
        <!-- Belly -->
        <ellipse cx="32" cy="48" rx="10" ry="7" fill="#D4C5FF" />
        <!-- Wing marks -->
        <path d="M14 40 Q12 34 18 36" stroke="#786FA6" stroke-width="2" fill="none" stroke-linecap="round" />
        <path d="M50 40 Q52 34 46 36" stroke="#786FA6" stroke-width="2" fill="none" stroke-linecap="round" />
    </svg>
</template>
