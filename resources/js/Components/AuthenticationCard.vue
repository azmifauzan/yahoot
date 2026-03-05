<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-primary-50 via-white to-primary-100">
        <div>
            <slot name="logo" />
        </div>

        <div class="w-full sm:max-w-md mt-8 px-8 py-6 bg-white shadow-xl overflow-hidden rounded-2xl border border-gray-100">
            <slot />
        </div>

        <p class="mt-6 text-xs text-gray-400">&copy; {{ new Date().getFullYear() }} Yahoot. All rights reserved.</p>
    </div>
</template>
