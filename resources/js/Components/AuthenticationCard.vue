<template>
    <div class="public-grid relative flex min-h-screen flex-col items-center overflow-hidden bg-[#fbfaff] px-4 pt-8 sm:justify-center sm:pt-6 dark:bg-gray-950">
        <div class="pointer-events-none absolute -left-24 top-1/4 h-72 w-72 rounded-full bg-primary-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 bottom-10 h-72 w-72 rounded-full bg-accent-blue/20 blur-3xl"></div>

        <div class="relative transition duration-300 hover:-rotate-3 hover:scale-105">
            <slot name="logo" />
        </div>

        <div class="relative mt-8 w-full overflow-hidden rounded-[1.7rem] border border-white bg-white/90 px-7 py-7 shadow-2xl shadow-primary-950/10 backdrop-blur sm:max-w-md sm:px-9 dark:border-white/10 dark:bg-gray-900/90">
            <div class="pointer-events-none absolute -right-8 -top-8 h-20 w-20 rounded-full border-[14px] border-primary-100 dark:border-primary-900/40"></div>
            <div class="relative">
            <slot />
            </div>
        </div>

        <p class="relative mt-6 pb-6 text-xs font-medium text-gray-400 dark:text-gray-500">&copy; {{ new Date().getFullYear() }} Yahoot. All rights reserved.</p>
    </div>
</template>
