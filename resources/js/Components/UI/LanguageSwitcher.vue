<script setup>
import { useI18n } from 'vue-i18n';
import { router } from '@inertiajs/vue3';

const { locale } = useI18n();

function toggleLocale() {
    const newLocale = locale.value === 'id' ? 'en' : 'id';
    locale.value = newLocale;
    document.cookie = `locale=${newLocale};path=/;max-age=${60 * 60 * 24 * 365}`;
    router.reload({ only: ['locale'] });
}
</script>

<template>
    <button
        type="button"
        class="relative inline-flex h-8 w-16 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent bg-gray-200 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-700"
        :class="{ '!bg-gray-900 dark:!bg-white': locale === 'en' }"
        role="switch"
        :aria-checked="locale === 'en'"
        @click="toggleLocale"
    >
        <span class="sr-only">Switch language</span>
        <span
            class="pointer-events-none inline-flex h-6 w-6 items-center justify-center rounded-full bg-white shadow ring-0 transition-transform duration-200 ease-in-out text-xs font-bold"
            :class="locale === 'en' ? 'translate-x-8' : 'translate-x-0.5'"
        >
            {{ locale === 'id' ? '🇮🇩' : '🇬🇧' }}
        </span>
        <span
            class="absolute text-[10px] font-semibold transition-opacity duration-150"
            :class="locale === 'id' ? 'right-2 text-gray-500' : 'left-2 text-white'"
        >
            {{ locale === 'id' ? 'EN' : 'ID' }}
        </span>
    </button>
</template>
