<script setup>
defineProps({
    iconOnly: { type: Boolean, default: false },
});
</script>

<template>
    <div class="flex items-center">
        <img src="/images/logo.png" alt="Yahoot Logo" class="w-32 sm:w-40 h-auto" />
    </div>
</template>
