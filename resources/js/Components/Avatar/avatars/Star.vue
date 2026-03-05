<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Star shape -->
        <path
            d="M32 4 L38 24 L58 24 L42 36 L48 56 L32 44 L16 56 L22 36 L6 24 L26 24 Z"
            fill="#FDCB6E"
        />
        <path
            d="M32 10 L36 24 L50 24 L39 33 L43 48 L32 40 L21 48 L25 33 L14 24 L28 24 Z"
            fill="#FFEAA7"
        />
        <!-- Face -->
        <circle cx="27" cy="28" r="2" fill="#2D3436" />
        <circle cx="37" cy="28" r="2" fill="#2D3436" />
        <circle cx="28" cy="27" r="0.8" fill="white" />
        <circle cx="38" cy="27" r="0.8" fill="white" />
        <path d="M28 33 Q32 36 36 33" stroke="#2D3436" stroke-width="1.5" fill="none" stroke-linecap="round" />
        <!-- Blush -->
        <ellipse cx="23" cy="31" rx="2.5" ry="1.5" fill="#FFB347" opacity="0.5" />
        <ellipse cx="41" cy="31" rx="2.5" ry="1.5" fill="#FFB347" opacity="0.5" />
    </svg>
</template>
