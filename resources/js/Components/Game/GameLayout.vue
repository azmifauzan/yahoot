<script setup>
defineProps({
    shake: { type: Boolean, default: false },
});
</script>

<template>
    <div class="min-h-screen flex flex-col" :class="{ 'animate-shake': shake }">
        <slot />
    </div>
</template>
