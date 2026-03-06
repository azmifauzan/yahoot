<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: Object,
    quizzes: Array,
    gameSessions: Array,
});

function toggleAdmin() {
    const action = props.user.is_admin ? 'remove admin from' : 'make admin';
    if (confirm(`Are you sure you want to ${action} "${props.user.name}"?`)) {
        router.post(route('admin.users.toggle-admin', props.user.id));
    }
}

function confirmDelete() {
    if (confirm(`Are you sure you want to delete "${props.user.name}"? This action cannot be undone.`)) {
        router.delete(route('admin.users.destroy', props.user.id));
    }
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <AppLayout title="User Detail">
        <Head :title="`User: ${user.name}`" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Breadcrumb -->
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                <Link :href="route('admin.dashboard')" class="hover:text-gray-700 dark:hover:text-gray-300">Admin</Link>
                / <Link :href="route('admin.users.index')" class="hover:text-gray-700 dark:hover:text-gray-300">Users</Link>
                / {{ user.name }}
            </p>

            <!-- User Profile Card -->
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-6 mb-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-2xl">
                            {{ user.avatar ? '🎭' : '👤' }}
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ user.name }}</h1>
                            <p class="text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span v-if="user.is_admin" class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">Admin</span>
                                <span class="text-xs text-gray-400">Joined {{ formatDate(user.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="toggleAdmin" class="rounded-lg px-3 py-2 text-sm font-medium bg-purple-100 text-purple-700 hover:bg-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:hover:bg-purple-900/50 transition">
                            {{ user.is_admin ? 'Remove Admin' : 'Make Admin' }}
                        </button>
                        <button @click="confirmDelete" class="rounded-lg px-3 py-2 text-sm font-medium bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/50 transition">
                            Delete User
                        </button>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Quizzes Created</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.quizzes_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Games Hosted</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.hosted_game_sessions_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Locale</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.locale || 'id' }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Quizzes -->
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Recent Quizzes</h2>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div v-for="quiz in quizzes" :key="quiz.id" class="px-5 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ quiz.title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ quiz.questions_count }} questions</p>
                            </div>
                            <span :class="quiz.is_published ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'" class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full">
                                {{ quiz.is_published ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                        <div v-if="!quizzes.length" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">No quizzes</div>
                    </div>
                </div>

                <!-- Game Sessions -->
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Recent Games Hosted</h2>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div v-for="session in gameSessions" :key="session.id" class="px-5 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white font-mono">{{ session.game_code }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ session.quiz?.title || '—' }} · {{ session.players_count }} players</p>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(session.created_at) }}</span>
                        </div>
                        <div v-if="!gameSessions.length" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">No games</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
