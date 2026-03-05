<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Rainbow arcs -->
        <path d="M6 52 A26 26 0 0 1 58 52" stroke="#FF6B6B" stroke-width="4" fill="none" />
        <path d="M10 52 A22 22 0 0 1 54 52" stroke="#FFB347" stroke-width="4" fill="none" />
        <path d="M14 52 A18 18 0 0 1 50 52" stroke="#FFEAA7" stroke-width="4" fill="none" />
        <path d="M18 52 A14 14 0 0 1 46 52" stroke="#55EFC4" stroke-width="4" fill="none" />
        <path d="M22 52 A10 10 0 0 1 42 52" stroke="#74B9FF" stroke-width="4" fill="none" />
        <path d="M26 52 A6 6 0 0 1 38 52" stroke="#A29BFE" stroke-width="4" fill="none" />
        <!-- Face on top -->
        <circle cx="32" cy="24" r="12" fill="#FFEAA7" />
        <circle cx="28" cy="22" r="2" fill="#2D3436" />
        <circle cx="36" cy="22" r="2" fill="#2D3436" />
        <circle cx="29" cy="21" r="0.8" fill="white" />
        <circle cx="37" cy="21" r="0.8" fill="white" />
        <path d="M28 28 Q32 32 36 28" stroke="#2D3436" stroke-width="1.5" fill="none" stroke-linecap="round" />
        <!-- Blush -->
        <ellipse cx="24" cy="26" rx="2.5" ry="1.5" fill="#FFB8C6" opacity="0.5" />
        <ellipse cx="40" cy="26" rx="2.5" ry="1.5" fill="#FFB8C6" opacity="0.5" />
        <!-- Clouds at base -->
        <circle cx="8" cy="54" r="6" fill="white" />
        <circle cx="56" cy="54" r="6" fill="white" />
    </svg>
</template>
