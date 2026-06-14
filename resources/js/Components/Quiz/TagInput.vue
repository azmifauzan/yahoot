<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    max: { type: Number, default: 5 },
});

const emit = defineEmits(['update:modelValue']);

const input = ref('');

function addTag() {
    const value = input.value.trim();
    input.value = '';

    if (!value || props.modelValue.length >= props.max) return;
    if (props.modelValue.some((tag) => tag.toLowerCase() === value.toLowerCase())) return;

    emit('update:modelValue', [...props.modelValue, value]);
}

function removeTag(index) {
    emit('update:modelValue', props.modelValue.filter((_, i) => i !== index));
}

function onKeydown(event) {
    if (event.key === 'Enter' || event.key === ',') {
        event.preventDefault();
        addTag();
    } else if (event.key === 'Backspace' && !input.value && props.modelValue.length > 0) {
        removeTag(props.modelValue.length - 1);
    }
}
</script>

<template>
    <div>
        <h3 class="mb-3 text-sm font-semibold text-gray-500 uppercase tracking-wide dark:text-gray-400">
            {{ t('tag.label') }}
        </h3>
        <div class="flex flex-wrap items-center gap-2 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2 focus-within:border-primary-500 focus-within:ring-2 focus-within:ring-primary-500/30 transition-all">
            <span
                v-for="(tag, index) in modelValue"
                :key="tag"
                class="inline-flex items-center gap-1 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 text-xs font-medium px-2.5 py-1"
            >
                {{ tag }}
                <button type="button" @click="removeTag(index)" class="hover:text-primary-900 dark:hover:text-primary-100">×</button>
            </span>
            <input
                v-model="input"
                type="text"
                :placeholder="modelValue.length < max ? t('tag.placeholder') : ''"
                :disabled="modelValue.length >= max"
                maxlength="30"
                @keydown="onKeydown"
                @blur="addTag"
                class="flex-1 min-w-[100px] border-none bg-transparent text-sm text-gray-800 dark:text-white focus:outline-none focus:ring-0 p-0"
            />
        </div>
        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ t('tag.hint') }}</p>
    </div>
</template>
