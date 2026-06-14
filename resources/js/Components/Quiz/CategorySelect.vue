<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({
    categories: { type: Array, default: () => [] },
    modelValue: { type: [Number, String, null], default: null },
});

const emit = defineEmits(['update:modelValue']);

function onChange(event) {
    const value = event.target.value;
    emit('update:modelValue', value === '' ? null : Number(value));
}
</script>

<template>
    <div>
        <h3 class="mb-3 text-sm font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">
            {{ t('category.label') }}
        </h3>
        <select
            :value="modelValue ?? ''"
            @change="onChange"
            class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white px-4 py-2.5 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/30 outline-none transition-all"
        >
            <option value="">{{ t('category.none') }}</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.icon ? `${category.icon} ` : '' }}{{ category.name }}
            </option>
        </select>
    </div>
</template>
