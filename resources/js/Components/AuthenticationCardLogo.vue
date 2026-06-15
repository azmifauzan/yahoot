<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="'/'" class="flex items-center justify-center">
        <img src="/images/logo.png?v=3" alt="Yahoot Logo" class="h-16 sm:h-20 w-auto object-contain dark:hidden" />
        <img src="/images/logo-dark.png?v=1" alt="Yahoot Logo" class="hidden h-16 sm:h-20 w-auto object-contain dark:block" />
    </Link>
</template>
