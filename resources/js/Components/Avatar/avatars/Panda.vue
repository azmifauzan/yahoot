<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Body -->
        <circle cx="32" cy="36" r="22" fill="white" />
        <!-- Ears -->
        <circle cx="14" cy="18" r="8" fill="#2D3436" />
        <circle cx="50" cy="18" r="8" fill="#2D3436" />
        <!-- Eye patches -->
        <ellipse cx="22" cy="33" rx="9" ry="8" fill="#2D3436" />
        <ellipse cx="42" cy="33" rx="9" ry="8" fill="#2D3436" />
        <!-- Eyes -->
        <ellipse cx="22" cy="33" rx="4" ry="4.5" fill="white" />
        <ellipse cx="42" cy="33" rx="4" ry="4.5" fill="white" />
        <circle cx="23" cy="32" r="2" fill="#2D3436" />
        <circle cx="43" cy="32" r="2" fill="#2D3436" />
        <circle cx="24" cy="31" r="1" fill="white" />
        <circle cx="44" cy="31" r="1" fill="white" />
        <!-- Nose -->
        <ellipse cx="32" cy="40" rx="3" ry="2" fill="#2D3436" />
        <!-- Mouth -->
        <path d="M28 43 Q32 47 36 43" stroke="#2D3436" stroke-width="1.5" fill="none" stroke-linecap="round" />
    </svg>
</template>
