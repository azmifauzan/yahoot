<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Body -->
        <circle cx="32" cy="36" r="20" fill="#B4846C" />
        <!-- Ears -->
        <ellipse cx="16" cy="22" rx="8" ry="10" fill="#B4846C" />
        <ellipse cx="48" cy="22" rx="8" ry="10" fill="#B4846C" />
        <ellipse cx="16" cy="22" rx="5" ry="7" fill="#E8C5A8" />
        <ellipse cx="48" cy="22" rx="5" ry="7" fill="#E8C5A8" />
        <!-- Eyes -->
        <ellipse cx="24" cy="33" rx="4" ry="4.5" fill="#2D3436" />
        <ellipse cx="40" cy="33" rx="4" ry="4.5" fill="#2D3436" />
        <circle cx="25.5" cy="31.5" r="1.5" fill="white" />
        <circle cx="41.5" cy="31.5" r="1.5" fill="white" />
        <!-- Nose -->
        <ellipse cx="32" cy="39" rx="3" ry="2.5" fill="#2D3436" />
        <ellipse cx="32" cy="38.5" rx="1" ry="0.5" fill="#636e72" />
        <!-- Tongue -->
        <ellipse cx="32" cy="45" rx="4" ry="3" fill="#FF6B6B" />
        <ellipse cx="32" cy="44" rx="4" ry="2" fill="#B4846C" />
    </svg>
</template>
