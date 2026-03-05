<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Body - round -->
        <circle cx="32" cy="36" r="22" fill="#FF6B6B" />
        <!-- Eyes -->
        <circle cx="24" cy="30" r="6" fill="white" />
        <circle cx="40" cy="30" r="6" fill="white" />
        <circle cx="24" cy="30" r="3.5" fill="#2D3436" />
        <circle cx="40" cy="30" r="3.5" fill="#2D3436" />
        <circle cx="25" cy="29" r="1.5" fill="white" />
        <circle cx="41" cy="29" r="1.5" fill="white" />
        <!-- Wide grin -->
        <path d="M20 40 Q32 52 44 40 Z" fill="#2D3436" />
        <path d="M20 40 Q32 46 44 40" fill="#FF4757" />
        <!-- Teeth -->
        <rect x="24" y="40" width="3" height="3" rx="0.5" fill="white" />
        <rect x="29" y="40" width="3" height="3" rx="0.5" fill="white" />
        <rect x="34" y="40" width="3" height="3" rx="0.5" fill="white" />
        <!-- Tiny horns -->
        <circle cx="18" cy="16" r="4" fill="#EE5A5A" />
        <circle cx="46" cy="16" r="4" fill="#EE5A5A" />
        <!-- Stubby feet -->
        <ellipse cx="22" cy="56" rx="6" ry="3" fill="#EE5A5A" />
        <ellipse cx="42" cy="56" rx="6" ry="3" fill="#EE5A5A" />
    </svg>
</template>
