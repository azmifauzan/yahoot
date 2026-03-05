<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Body -->
        <circle cx="32" cy="38" r="18" fill="#DFE6E9" />
        <!-- Ears -->
        <ellipse cx="20" cy="10" rx="6" ry="14" fill="#FFB8C6" />
        <ellipse cx="44" cy="10" rx="6" ry="14" fill="#FFB8C6" />
        <ellipse cx="20" cy="10" rx="3.5" ry="10" fill="#FF9BB5" />
        <ellipse cx="44" cy="10" rx="3.5" ry="10" fill="#FF9BB5" />
        <!-- Eyes -->
        <ellipse cx="25" cy="35" rx="4" ry="5" fill="#FF6B81" />
        <ellipse cx="39" cy="35" rx="4" ry="5" fill="#FF6B81" />
        <circle cx="25" cy="34" r="2" fill="#2D3436" />
        <circle cx="39" cy="34" r="2" fill="#2D3436" />
        <circle cx="26" cy="33" r="1" fill="white" />
        <circle cx="40" cy="33" r="1" fill="white" />
        <!-- Nose -->
        <ellipse cx="32" cy="40" rx="2" ry="1.5" fill="#FF6B81" />
        <!-- Mouth -->
        <path d="M29 43 Q32 46 35 43" stroke="#2D3436" stroke-width="1.2" fill="none" stroke-linecap="round" />
    </svg>
</template>
