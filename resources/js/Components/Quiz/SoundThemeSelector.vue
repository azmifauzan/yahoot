<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({
    currentTheme: {
        type: String,
        default: 'classic',
    },
});

const emit = defineEmits(['select']);

const soundThemes = ['classic', 'chill', 'energetic', 'none'];

const soundThemeIcons = {
    classic: '🎮',
    chill: '🌙',
    energetic: '⚡',
    none: '🔇',
};
</script>

<template>
    <div>
        <h3 class="mb-3 text-sm font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">
            {{ t('sound.sound_theme') }}
        </h3>
        <div class="grid grid-cols-2 gap-2">
            <button
                v-for="value in soundThemes"
                :key="value"
                type="button"
                @click="emit('select', value)"
                :class="[
                    'flex items-center gap-2 rounded-xl border-2 px-3 py-2 text-sm font-medium transition-all hover:scale-105',
                    currentTheme === value
                        ? 'border-primary-500 ring-2 ring-primary-500/30 dark:border-primary-400 dark:ring-primary-400/30 text-primary-700 dark:text-primary-300'
                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-500 text-gray-700 dark:text-gray-300',
                ]"
            >
                <span class="text-lg">{{ soundThemeIcons[value] }}</span>
                <span>{{ t(`sound.theme_${value}`) }}</span>
            </button>
        </div>
    </div>
</template>
