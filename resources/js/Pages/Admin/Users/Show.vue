<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';

const { t } = useI18n();

const props = defineProps({
    user: Object,
    gameHistory: Array,
});

function toggleAdmin() {
    router.put(route('admin.users.update', props.user.id), { is_admin: !props.user.is_admin });
}
</script>

<template>
    <AppLayout :title="user.name">
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.users.index')" class="text-sm text-gray-500 hover:text-primary-600 dark:text-gray-400">← {{ t('admin.users') }}</Link>
                <h2 class="font-display text-xl font-bold text-gray-800 dark:text-gray-100">{{ user.name }}</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- User info card -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ user.name }}</p>
                            <p class="text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ t('admin.registered_at') }}: {{ new Date(user.created_at).toLocaleDateString() }}</p>
                        </div>
                        <button
                            @click="toggleAdmin"
                            :class="['px-4 py-2 rounded-lg text-sm font-medium transition', user.is_admin ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200']"
                        >
                            {{ user.is_admin ? t('admin.revoke_admin') : t('admin.grant_admin') }}
                        </button>
                    </div>
                </div>

                <!-- Quiz list -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('admin.quizzes') }} ({{ user.quizzes.length }})</h3>
                    </div>
                    <div v-if="user.quizzes.length > 0" class="divide-y divide-gray-50 dark:divide-gray-800">
                        <div v-for="quiz in user.quizzes" :key="quiz.id" class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-sm text-gray-900 dark:text-white">{{ quiz.title }}</p>
                                <p class="text-xs text-gray-500">{{ quiz.questions_count }} {{ t('admin.questions') }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span v-if="quiz.deleted_at" class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">{{ t('admin.deleted') }}</span>
                                <span v-else-if="quiz.is_published" class="text-xs bg-green-100 text-green-600 px-2 py-0.5 rounded-full">{{ t('dashboard.published') }}</span>
                                <span v-else class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ t('dashboard.draft') }}</span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="px-6 py-4 text-sm text-gray-500">{{ t('admin.no_quizzes') }}</p>
                </div>

                <!-- Game history -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('admin.game_history') }}</h3>
                    </div>
                    <div v-if="gameHistory.length > 0" class="divide-y divide-gray-50 dark:divide-gray-800">
                        <div v-for="entry in gameHistory" :key="entry.id" class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-sm text-gray-900 dark:text-white">{{ entry.quiz_title }}</p>
                                <p class="text-xs text-gray-500">{{ t('admin.nickname') }}: {{ entry.nickname }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-primary-600 dark:text-primary-400">{{ entry.score }} pts</p>
                                <p class="text-xs text-gray-400">{{ new Date(entry.played_at).toLocaleDateString() }}</p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="px-6 py-4 text-sm text-gray-500">{{ t('admin.no_game_history') }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
