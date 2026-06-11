<script setup>
defineProps({
    iconOnly: { type: Boolean, default: false },
});
</script>

<template>
    <div class="flex items-center gap-0.5">
        <img src="/images/logo.png" alt="Yahoot Logo" class="h-8 w-8 object-contain rounded-lg shadow-sm" />
        <span v-if="!iconOnly" class="text-lg font-bold text-gray-900 dark:text-white">ahoot</span>
    </div>
</template>
