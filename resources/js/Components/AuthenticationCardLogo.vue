<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="'/'" class="flex items-center gap-2">
        <svg viewBox="0 0 40 40" class="h-12 w-12" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" rx="10" fill="#6C5CE7"/>
            <text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="white" font-size="22" font-weight="bold" font-family="Inter, sans-serif">Y</text>
        </svg>
        <span class="text-2xl font-bold text-gray-900 dark:text-white">ahoot</span>
    </Link>
</template>
