<script setup>
defineProps({
    iconOnly: { type: Boolean, default: false },
});
</script>

<template>
    <div class="flex items-center">
        <img src="/images/logo.png?v=3" alt="Yahoot Logo" class="h-8 sm:h-10 w-auto object-contain dark:hidden" />
        <img src="/images/logo-dark.png?v=1" alt="Yahoot Logo" class="hidden h-8 sm:h-10 w-auto object-contain dark:block" />
    </div>
</template>
