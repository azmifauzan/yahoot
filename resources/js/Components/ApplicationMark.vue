<script setup>
defineProps({
    iconOnly: { type: Boolean, default: false },
});
</script>

<template>
    <div class="flex items-center">
        <img src="/images/logo.png" alt="Yahoot Logo" class="h-8 w-auto object-contain" />
    </div>
</template>
