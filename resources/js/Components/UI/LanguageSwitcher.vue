<script setup>
import { useI18n } from 'vue-i18n';
import { router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const { locale } = useI18n();
const page = usePage();
const isOpen = ref(false);

const languages = [
    { code: 'id', label: '🇮🇩 Indonesia' },
    { code: 'en', label: '🇬🇧 English' },
];

const currentLabel = () => languages.find((l) => l.code === locale.value)?.label || '🇮🇩 Indonesia';

function switchLocale(code) {
    locale.value = code;
    isOpen.value = false;

    document.cookie = `locale=${code};path=/;max-age=${60 * 60 * 24 * 365}`;

    router.reload({ only: ['locale'] });
}
</script>

<template>
    <div class="relative">
        <button
            type="button"
            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
            @click="isOpen = !isOpen"
        >
            <span>{{ currentLabel() }}</span>
            <svg
                class="h-4 w-4 transition-transform"
                :class="{ 'rotate-180': isOpen }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 z-50 mt-2 w-40 origin-top-right rounded-lg bg-white py-1 shadow-lg ring-1 ring-black/5 dark:bg-gray-800"
            >
                <button
                    v-for="lang in languages"
                    :key="lang.code"
                    class="flex w-full items-center px-4 py-2 text-left text-sm transition hover:bg-gray-50 dark:hover:bg-gray-700"
                    :class="{
                        'font-semibold text-primary-600 dark:text-primary-400': locale === lang.code,
                        'text-gray-700 dark:text-gray-300': locale !== lang.code,
                    }"
                    @click="switchLocale(lang.code)"
                >
                    {{ lang.label }}
                </button>
            </div>
        </Transition>

        <!-- Backdrop -->
        <div v-if="isOpen" class="fixed inset-0 z-40" @click="isOpen = false" />
    </div>
</template>
