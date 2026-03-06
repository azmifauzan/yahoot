<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    quiz: Object,
});

function confirmDelete() {
    const msg = props.quiz.deleted_at
        ? `Permanently delete "${props.quiz.title}"?`
        : `Delete "${props.quiz.title}"?`;
    if (confirm(msg)) {
        router.delete(route('admin.quizzes.destroy', props.quiz.id));
    }
}

function restoreQuiz() {
    if (confirm(`Restore "${props.quiz.title}"?`)) {
        router.post(route('admin.quizzes.restore', props.quiz.id));
    }
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <AppLayout title="Quiz Detail">
        <Head :title="`Quiz: ${quiz.title}`" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Breadcrumb -->
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                <Link :href="route('admin.dashboard')" class="hover:text-gray-700 dark:hover:text-gray-300">Admin</Link>
                / <Link :href="route('admin.quizzes.index')" class="hover:text-gray-700 dark:hover:text-gray-300">Quizzes</Link>
                / {{ quiz.title }}
            </p>

            <!-- Quiz Info -->
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-6 mb-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ quiz.title }}</h1>
                        <p v-if="quiz.description" class="text-gray-500 dark:text-gray-400 mt-1">{{ quiz.description }}</p>
                        <div class="flex items-center gap-3 mt-3">
                            <span class="text-sm text-gray-500 dark:text-gray-400">By {{ quiz.user?.name || '—' }}</span>
                            <span v-if="quiz.deleted_at" class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">Trashed</span>
                            <span v-else-if="quiz.is_published" class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Published</span>
                            <span v-else class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">Draft</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button v-if="quiz.deleted_at" @click="restoreQuiz" class="rounded-lg px-3 py-2 text-sm font-medium bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-300 transition">
                            Restore
                        </button>
                        <button @click="confirmDelete" class="rounded-lg px-3 py-2 text-sm font-medium bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 transition">
                            {{ quiz.deleted_at ? 'Permanently Delete' : 'Delete' }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Questions</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ quiz.questions_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Game Sessions</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ quiz.game_sessions_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Visibility</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white capitalize">{{ quiz.visibility }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Created</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatDate(quiz.created_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Questions -->
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Questions ({{ quiz.questions?.length || 0 }})</h2>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div v-for="(question, index) in quiz.questions" :key="question.id" class="px-5 py-4">
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-7 h-7 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-sm font-medium text-purple-700 dark:text-purple-300">
                                {{ index + 1 }}
                            </span>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">{{ question.question_text }}</p>
                                <div class="flex gap-2 mt-1">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ question.type }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ question.time_limit }}s</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ question.points }}</span>
                                </div>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span v-for="answer in question.answers" :key="answer.id"
                                        class="inline-flex px-2 py-1 text-xs rounded-lg"
                                        :class="answer.is_correct ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 font-medium' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                                    >
                                        {{ answer.is_correct ? '✓' : '' }} {{ answer.answer_text }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="!quiz.questions?.length" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">No questions</div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
