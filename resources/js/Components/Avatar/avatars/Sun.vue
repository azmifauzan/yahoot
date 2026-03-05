<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Rays -->
        <line x1="32" y1="2" x2="32" y2="12" stroke="#FDCB6E" stroke-width="3" stroke-linecap="round" />
        <line x1="32" y1="52" x2="32" y2="62" stroke="#FDCB6E" stroke-width="3" stroke-linecap="round" />
        <line x1="2" y1="32" x2="12" y2="32" stroke="#FDCB6E" stroke-width="3" stroke-linecap="round" />
        <line x1="52" y1="32" x2="62" y2="32" stroke="#FDCB6E" stroke-width="3" stroke-linecap="round" />
        <line x1="10" y1="10" x2="17" y2="17" stroke="#FDCB6E" stroke-width="3" stroke-linecap="round" />
        <line x1="47" y1="47" x2="54" y2="54" stroke="#FDCB6E" stroke-width="3" stroke-linecap="round" />
        <line x1="10" y1="54" x2="17" y2="47" stroke="#FDCB6E" stroke-width="3" stroke-linecap="round" />
        <line x1="47" y1="17" x2="54" y2="10" stroke="#FDCB6E" stroke-width="3" stroke-linecap="round" />
        <!-- Sun body -->
        <circle cx="32" cy="32" r="16" fill="#FDCB6E" />
        <circle cx="32" cy="32" r="13" fill="#FFEAA7" />
        <!-- Face -->
        <circle cx="26" cy="30" r="2.5" fill="#2D3436" />
        <circle cx="38" cy="30" r="2.5" fill="#2D3436" />
        <circle cx="27" cy="29" r="1" fill="white" />
        <circle cx="39" cy="29" r="1" fill="white" />
        <path d="M26 37 Q32 42 38 37" stroke="#2D3436" stroke-width="2" fill="none" stroke-linecap="round" />
        <!-- Blush -->
        <ellipse cx="21" cy="34" rx="3" ry="2" fill="#FFB347" opacity="0.4" />
        <ellipse cx="43" cy="34" rx="3" ry="2" fill="#FFB347" opacity="0.4" />
    </svg>
</template>
