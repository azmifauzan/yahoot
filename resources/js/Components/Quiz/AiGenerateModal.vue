<script setup>
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import DialogModal from '@/Components/DialogModal.vue';

const { t } = useI18n();

const props = defineProps({
    show: Boolean,
    quizId: [Number, String],
});

const emit = defineEmits(['close', 'generated']);

const form = useForm({
    topic: '',
    count: 5,
    difficulty: 'medium',
    type: 'multiple_choice',
});

function submit() {
    form.post(route('quizzes.ai-generate', props.quizId), {
        preserveScroll: true,
        onSuccess: (page) => {
            emit('generated', page.props.quiz?.questions || []);
            form.reset('topic');
        },
    });
}

function close() {
    form.clearErrors();
    emit('close');
}
</script>

<template>
    <DialogModal :show="show" @close="close">
        <template #title>
            {{ t('quiz.ai_generate_title') }}
        </template>
        <template #content>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('quiz.ai_topic') }}</label>
                    <input
                        v-model="form.topic"
                        type="text"
                        :placeholder="t('quiz.ai_topic_placeholder')"
                        class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                    />
                    <p v-if="form.errors.topic" class="mt-1 text-sm text-red-600">{{ form.errors.topic }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('quiz.ai_count') }}</label>
                        <input
                            v-model.number="form.count"
                            type="number"
                            min="1"
                            max="10"
                            class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                        />
                        <p v-if="form.errors.count" class="mt-1 text-sm text-red-600">{{ form.errors.count }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('quiz.ai_difficulty') }}</label>
                        <select v-model="form.difficulty" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                            <option value="easy">{{ t('quiz.ai_difficulty_easy') }}</option>
                            <option value="medium">{{ t('quiz.ai_difficulty_medium') }}</option>
                            <option value="hard">{{ t('quiz.ai_difficulty_hard') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('quiz.ai_question_type') }}</label>
                    <select v-model="form.type" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                        <option value="multiple_choice">{{ t('quiz.multiple_choice') }}</option>
                        <option value="true_false">{{ t('quiz.true_false') }}</option>
                    </select>
                </div>

                <p class="text-xs text-gray-400">{{ t('quiz.ai_disclaimer') }}</p>
                <p v-if="form.errors.ai" class="text-sm text-red-600">{{ form.errors.ai }}</p>
            </div>
        </template>
        <template #footer>
            <button type="button" @click="close" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 mr-2">
                {{ t('common.cancel') }}
            </button>
            <button
                type="button"
                @click="submit"
                :disabled="form.processing || !form.topic"
                class="px-4 py-2 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 disabled:opacity-50"
            >
                {{ form.processing ? t('quiz.ai_generating') : t('quiz.ai_generate_button') }}
            </button>
        </template>
    </DialogModal>
</template>
