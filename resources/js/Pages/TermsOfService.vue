<script setup>
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const { t } = useI18n();

defineProps({
    terms: String,
});
</script>

<template>
    <GuestLayout :title="t('auth.terms')">
        <div class="py-12 sm:py-16 flex justify-center w-full px-4 sm:px-6">
            <div class="w-full sm:max-w-4xl p-8 bg-white shadow-xl overflow-hidden rounded-2xl border border-gray-200 dark:bg-gray-900 dark:border-gray-800 prose prose-gray dark:prose-invert max-w-none" v-html="terms" />
        </div>
    </GuestLayout>
</template>
