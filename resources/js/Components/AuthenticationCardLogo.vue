<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="'/'" class="flex items-center gap-0.5">
        <img src="/images/logo.png" alt="Yahoot Logo" class="h-12 w-12 object-contain rounded-xl shadow-sm" />
        <span class="text-2xl font-bold text-gray-900 dark:text-white">ahoot</span>
    </Link>
</template>
