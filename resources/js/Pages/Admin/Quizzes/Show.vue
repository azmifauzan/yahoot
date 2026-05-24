<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { confirm, toast } = useSwal();

const props = defineProps({
    quiz: Object,
});

function confirmDelete() {
    confirm({
        title: t('admin.force_delete_quiz'),
        text: t('admin.confirm_force_delete'),
        confirmText: t('admin.force_delete'),
        cancelText: t('common.cancel'),
        icon: 'warning',
    }).then(result => {
        if (result.isConfirmed) {
            router.delete(route('admin.quizzes.destroy', props.quiz.id), {
                onSuccess: () => router.visit(route('admin.quizzes.index')),
            });
        }
    });
}

function restore() {
    router.post(route('admin.quizzes.restore', props.quiz.id), {}, {
        onSuccess: () => toast(t('admin.quiz_restored')),
    });
}
</script>

<template>
    <AppLayout :title="quiz.title">
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.quizzes.index')" class="text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400">← {{ t('admin.quizzes') }}</Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">{{ quiz.title }}</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Meta card -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-bold text-lg text-gray-900 dark:text-white">{{ quiz.title }}</p>
                            <p class="text-gray-500 text-sm">{{ t('admin.owner') }}: {{ quiz.user?.name }}</p>
                            <p class="text-gray-400 text-xs mt-1">{{ new Date(quiz.created_at).toLocaleDateString() }}</p>
                            <p v-if="quiz.description" class="text-gray-600 dark:text-gray-300 text-sm mt-2">{{ quiz.description }}</p>
                        </div>
                        <div class="flex flex-col gap-2 items-end">
                            <span v-if="quiz.deleted_at" class="bg-red-100 text-red-600 text-xs rounded-full px-2 py-0.5">{{ t('admin.deleted') }}</span>
                            <span v-else-if="quiz.is_published" class="bg-green-100 text-green-600 text-xs rounded-full px-2 py-0.5">{{ t('dashboard.published') }}</span>
                            <span v-else class="bg-gray-100 text-gray-600 text-xs rounded-full px-2 py-0.5">{{ t('dashboard.draft') }}</span>
                            <div class="flex gap-2 mt-2">
                                <button v-if="quiz.deleted_at" @click="restore" class="px-3 py-1.5 bg-green-100 text-green-700 rounded-lg text-sm hover:bg-green-200">{{ t('admin.restore') }}</button>
                                <button @click="confirmDelete" class="px-3 py-1.5 bg-red-100 text-red-600 rounded-lg text-sm hover:bg-red-200">{{ t('admin.force_delete') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Questions -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('admin.questions') }} ({{ quiz.questions.length }})</h3>
                    </div>
                    <div class="divide-y divide-gray-50 dark:divide-gray-800">
                        <div v-for="(question, idx) in quiz.questions" :key="question.id" class="px-6 py-4">
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ idx + 1 }}. {{ question.question_text }}</p>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <div
                                    v-for="answer in question.answers"
                                    :key="answer.id"
                                    :class="['rounded-lg px-3 py-1.5 text-xs', answer.is_correct ? 'bg-green-100 text-green-700 font-medium' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300']"
                                >
                                    {{ answer.answer_text }}
                                    <span v-if="answer.is_correct"> ✓</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
