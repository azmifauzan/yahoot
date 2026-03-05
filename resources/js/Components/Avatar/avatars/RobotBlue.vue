<script setup>
defineProps({
    size: { type: Number, default: 64 },
});
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Antenna -->
        <line x1="32" y1="4" x2="32" y2="14" stroke="#74B9FF" stroke-width="2" />
        <circle cx="32" cy="4" r="3" fill="#74B9FF" />
        <!-- Head -->
        <rect x="14" y="14" width="36" height="28" rx="6" fill="#74B9FF" />
        <!-- Face plate -->
        <rect x="18" y="18" width="28" height="20" rx="4" fill="#DFE6E9" />
        <!-- Eyes -->
        <rect x="22" y="24" width="7" height="7" rx="2" fill="#0984E3" />
        <rect x="35" y="24" width="7" height="7" rx="2" fill="#0984E3" />
        <rect x="24" y="25" width="2" height="2" rx="0.5" fill="white" />
        <rect x="37" y="25" width="2" height="2" rx="0.5" fill="white" />
        <!-- Mouth -->
        <rect x="24" y="34" width="16" height="2" rx="1" fill="#0984E3" />
        <!-- Body -->
        <rect x="20" y="44" width="24" height="12" rx="4" fill="#74B9FF" />
        <rect x="28" y="48" width="8" height="4" rx="2" fill="#DFE6E9" />
        <!-- Arms -->
        <rect x="8" y="44" width="10" height="4" rx="2" fill="#74B9FF" />
        <rect x="46" y="44" width="10" height="4" rx="2" fill="#74B9FF" />
    </svg>
</template>
