<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <a
        :href="route('auth.google.redirect')"
        class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
    >
        <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.19-2.27H12v4.51h6.47a5.54 5.54 0 0 1-2.4 3.63v3.02h3.86c2.26-2.08 3.56-5.14 3.56-8.89z" />
            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.07 7.93-2.91l-3.86-3.02c-1.07.72-2.45 1.15-4.07 1.15-3.13 0-5.78-2.11-6.73-4.96H1.27v3.12A11.99 11.99 0 0 0 12 24z" />
            <path fill="#FBBC05" d="M5.27 14.26A7.2 7.2 0 0 1 4.89 12c0-.78.14-1.54.38-2.26V6.62H1.27A11.99 11.99 0 0 0 0 12c0 1.94.46 3.77 1.27 5.38z" />
            <path fill="#EA4335" d="M12 4.77c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0A11.99 11.99 0 0 0 1.27 6.62l4 3.12C6.22 6.88 8.87 4.77 12 4.77z" />
        </svg>
        {{ t('auth.continue_with_google') }}
    </a>
</template>
