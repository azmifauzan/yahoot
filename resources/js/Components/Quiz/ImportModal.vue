<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Icon from '@/Components/UI/Icon.vue';

const { t } = useI18n();
const emit = defineEmits(['close']);

const form = useForm({ file: null });
const fileName = ref('');

function onFileChange(event) {
    const file = event.target.files[0] ?? null;
    form.file = file;
    fileName.value = file?.name ?? '';
}

function submit() {
    form.post(route('quizzes.import'), {
        forceFormData: true,
        onSuccess: () => emit('close'),
    });
}

const sampleTemplate = {
    title: 'My Quiz',
    description: 'Optional description',
    theme: 'standard',
    questions: [
        {
            type: 'multiple_choice',
            question_text: 'What is 2 + 2?',
            time_limit: 20,
            points: 'standard',
            answers: [
                { answer_text: '4', is_correct: true },
                { answer_text: '5', is_correct: false },
            ],
        },
    ],
};

function downloadTemplate() {
    const blob = new Blob([JSON.stringify(sampleTemplate, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'yahoot-template.json';
    link.click();
    URL.revokeObjectURL(url);
}
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="emit('close')">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ t('import_export.import_title') }}</h3>
                <button @click="emit('close')" :aria-label="t('common.cancel')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">{{ t('import_export.import_help') }}</p>

            <label
                class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 p-6 text-center transition hover:border-primary-400 dark:border-gray-600"
            >
                <Icon name="document" class="h-9 w-9 text-primary-500" />
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ fileName || t('import_export.choose_file') }}
                </span>
                <input type="file" accept=".json,.csv,.txt" class="hidden" @change="onFileChange" />
            </label>

            <p v-if="form.errors.file" class="mt-2 text-sm text-red-500">{{ form.errors.file }}</p>

            <button
                type="button"
                @click="downloadTemplate"
                class="mt-3 text-xs font-medium text-primary-600 hover:underline dark:text-primary-400"
            >
                <Icon name="download" class="mr-1 inline h-4 w-4" /> {{ t('import_export.download_template') }}
            </button>

            <div class="mt-6 flex justify-end gap-2">
                <button
                    @click="emit('close')"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                >
                    {{ t('common.cancel') }}
                </button>
                <button
                    @click="submit"
                    :disabled="!form.file || form.processing"
                    class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ t('import_export.import') }}
                </button>
            </div>
        </div>
    </div>
</template>
