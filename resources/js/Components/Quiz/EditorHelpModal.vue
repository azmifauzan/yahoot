<script setup>
import { useI18n } from 'vue-i18n';

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
    <teleport to="body">
        <!-- Click-away catcher -->
        <div v-if="show" class="fixed inset-0 z-40" @click="emit('close')"></div>

        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div
                v-if="show"
                class="fixed bottom-24 left-4 z-50 flex max-h-[70vh] w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900 sm:left-6 sm:w-96"
            >
                <!-- Header -->
                <div class="flex items-start justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ t('editor_help.title') }}</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ t('editor_help.intro') }}</p>
                    </div>
                    <button
                        type="button"
                        @click="emit('close')"
                        :aria-label="t('common.cancel')"
                        class="-mr-1 ml-2 flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Scrollable content -->
                <div class="space-y-4 overflow-y-auto px-4 py-3">
                    <section v-for="section in sections" :key="section.title">
                        <h4 class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-400">
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
            </div>
        </transition>
    </teleport>
</template>
