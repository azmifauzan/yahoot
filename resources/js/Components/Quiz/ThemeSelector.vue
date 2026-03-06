<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    themes: Array,
    currentTheme: {
        type: String,
        default: 'standard',
    },
});

const emit = defineEmits(['select']);

const themeIcons = {
    standard: '🎨',
    ocean: '🌊',
    sunset: '🌅',
    forest: '🌲',
    galaxy: '🌌',
    candy: '🍬',
};

const themePreviewGradients = {
    standard: 'bg-gradient-to-br from-indigo-500 to-purple-600',
    ocean: 'bg-gradient-to-br from-cyan-500 to-blue-700',
    sunset: 'bg-gradient-to-br from-orange-400 to-rose-500',
    forest: 'bg-gradient-to-br from-emerald-500 to-teal-700',
    galaxy: 'bg-gradient-to-br from-violet-600 to-fuchsia-800',
    candy: 'bg-gradient-to-br from-pink-400 to-rose-500',
};
</script>

<template>
    <div>
        <h3 class="mb-3 text-sm font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">
            {{ t('quiz.theme') }}
        </h3>
        <div class="grid grid-cols-2 gap-2">
            <button
                v-for="theme in themes"
                :key="theme.value"
                @click="emit('select', theme.value)"
                :class="[
                    'relative rounded-xl overflow-hidden border-2 transition-all hover:scale-105',
                    currentTheme === theme.value
                        ? 'border-gray-900 ring-2 ring-gray-900/20 dark:border-white dark:ring-white/20'
                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-500',
                ]"
            >
                <div
                    :class="[themePreviewGradients[theme.value], 'h-16 flex items-center justify-center']"
                >
                    <span class="text-2xl">{{ themeIcons[theme.value] }}</span>
                </div>
                <div class="px-2 py-1.5 text-center bg-white dark:bg-gray-900">
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ theme.label }}</span>
                </div>
                <!-- Checkmark for selected -->
                <div
                    v-if="currentTheme === theme.value"
                    class="absolute top-1 right-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-full w-5 h-5 flex items-center justify-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
            </button>
        </div>
    </div>
</template>
