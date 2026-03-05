<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-white dark:bg-gray-950">
        <div>
            <slot name="logo" />
        </div>

        <div class="w-full sm:max-w-md mt-8 px-8 py-6 bg-white shadow-xl overflow-hidden rounded-2xl border border-gray-200 dark:bg-gray-900 dark:border-gray-800">
            <slot />
        </div>

        <p class="mt-6 text-xs text-gray-400 dark:text-gray-500">&copy; {{ new Date().getFullYear() }} Yahoot. All rights reserved.</p>
    </div>
</template>
