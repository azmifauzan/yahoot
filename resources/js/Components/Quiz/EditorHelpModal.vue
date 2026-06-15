<script setup>
import { useI18n } from 'vue-i18n';
import DialogModal from '@/Components/DialogModal.vue';

const { t, tm, rt } = useI18n();

defineProps({
    show: Boolean,
});

const emit = defineEmits(['close']);

const sections = [
    { title: 'editor_help.types_title', items: 'editor_help.types_items' },
    { title: 'editor_help.sidebar_title', items: 'editor_help.sidebar_items' },
    { title: 'editor_help.question_title', items: 'editor_help.question_items' },
    { title: 'editor_help.properties_title', items: 'editor_help.properties_items' },
    { title: 'editor_help.settings_title', items: 'editor_help.settings_items' },
    { title: 'editor_help.ai_title', items: 'editor_help.ai_items' },
    { title: 'editor_help.save_title', items: 'editor_help.save_items' },
];
</script>

<template>
    <DialogModal :show="show" max-width="2xl" @close="emit('close')">
        <template #title>
            {{ t('editor_help.title') }}
        </template>
        <template #content>
            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">{{ t('editor_help.intro') }}</p>

            <div class="max-h-[60vh] space-y-5 overflow-y-auto pr-1">
                <section v-for="section in sections" :key="section.title">
                    <h4 class="mb-1.5 text-sm font-semibold text-primary-600 dark:text-primary-400">
                        {{ t(section.title) }}
                    </h4>
                    <ul class="space-y-1">
                        <li
                            v-for="(item, index) in tm(section.items)"
                            :key="index"
                            class="flex gap-2 text-sm text-gray-700 dark:text-gray-300"
                        >
                            <span class="mt-1.5 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-primary-400"></span>
                            <span>{{ rt(item) }}</span>
                        </li>
                    </ul>
                </section>
            </div>
        </template>
        <template #footer>
            <button
                type="button"
                @click="emit('close')"
                class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
            >
                {{ t('editor_help.got_it') }}
            </button>
        </template>
    </DialogModal>
</template>
