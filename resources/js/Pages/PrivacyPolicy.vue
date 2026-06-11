<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import ThemeSwitcher from '@/Components/UI/ThemeSwitcher.vue';
import LanguageSwitcher from '@/Components/UI/LanguageSwitcher.vue';

const { t } = useI18n();

defineProps({
    policy: String,
});
</script>

<template>
    <Head :title="t('auth.privacy')" />

    <div class="min-h-screen flex flex-col items-center pt-8 pb-12 sm:pt-16 bg-white dark:bg-gray-950 font-sans antialiased">
        <div class="w-full sm:max-w-3xl flex justify-between items-center px-6 sm:px-0 mb-8">
            <AuthenticationCardLogo />
            
            <div class="flex items-center gap-2">
                <LanguageSwitcher />
                <ThemeSwitcher />
            </div>
        </div>

        <div class="w-full sm:max-w-3xl px-8 py-10 bg-white shadow-xl overflow-hidden rounded-2xl border border-gray-200 dark:bg-gray-900 dark:border-gray-800 prose prose-gray dark:prose-invert max-w-none" v-html="policy" />

        <div class="mt-8 text-center">
            <Link :href="route('landing')" class="text-sm font-medium text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition flex items-center gap-1.5 justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ t('game.back_home') }}
            </Link>
        </div>
    </div>
</template>
