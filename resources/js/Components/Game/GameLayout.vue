<script setup>
defineProps({
    shake: { type: Boolean, default: false },
});
</script>

<template>
    <div
        class="relative min-h-screen overflow-hidden bg-slate-950 text-white"
        :class="{ 'animate-shake': shake }"
    >
        <div class="public-grid pointer-events-none absolute inset-0 opacity-25"></div>
        <div class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 rounded-full bg-primary-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-40 -right-24 h-[30rem] w-[30rem] rounded-full bg-fuchsia-500/15 blur-3xl"></div>
        <main class="relative z-10 flex min-h-screen flex-col">
            <slot />
        </main>
    </div>
</template>
