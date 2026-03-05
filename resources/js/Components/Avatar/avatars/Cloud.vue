<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Cloud shape -->
        <circle cx="22" cy="34" r="14" fill="#DFE6E9" />
        <circle cx="38" cy="30" r="16" fill="#DFE6E9" />
        <circle cx="50" cy="36" r="12" fill="#DFE6E9" />
        <rect x="16" y="34" width="40" height="16" rx="8" fill="#DFE6E9" />
        <!-- Face -->
        <circle cx="28" cy="34" r="2.5" fill="#2D3436" />
        <circle cx="40" cy="34" r="2.5" fill="#2D3436" />
        <circle cx="29" cy="33" r="1" fill="white" />
        <circle cx="41" cy="33" r="1" fill="white" />
        <path d="M30 40 Q34 44 38 40" stroke="#2D3436" stroke-width="1.5" fill="none" stroke-linecap="round" />
        <!-- Blush -->
        <ellipse cx="23" cy="38" rx="3" ry="2" fill="#74B9FF" opacity="0.3" />
        <ellipse cx="45" cy="38" rx="3" ry="2" fill="#74B9FF" opacity="0.3" />
    </svg>
</template>
