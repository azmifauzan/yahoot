<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { toast, confirm } = useSwal();

const props = defineProps({
    quizzes: Array,
    filters: Object,
    stats: Object,
});

const search = ref(props.filters.search || '');
const activeFilter = ref(props.filters.filter || 'all');
const viewMode = ref('grid');

const filterOptions = [
    { value: 'all', label: () => t('dashboard.filter_all') },
    { value: 'draft', label: () => t('dashboard.filter_draft') },
    { value: 'published', label: () => t('dashboard.filter_published') },
];

function applyFilters() {
    router.get(route('dashboard'), {
        filter: activeFilter.value !== 'all' ? activeFilter.value : undefined,
        search: search.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}

function setFilter(filter) {
    activeFilter.value = filter;
    applyFilters();
}

let searchTimeout = null;
function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
}

function confirmDelete(quiz) {
    confirm({
        title: t('dashboard.delete'),
        text: t('dashboard.confirm_delete'),
        confirmText: t('common.delete'),
        cancelText: t('common.cancel'),
        icon: 'warning',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('quizzes.destroy', quiz.id), {
                onSuccess: () => toast(t('dashboard.deleted_success')),
            });
        }
    });
}

function duplicateQuiz(quiz) {
    router.post(route('quizzes.duplicate', quiz.id), {}, {
        onSuccess: () => toast(t('dashboard.duplicated_success')),
    });
}

function playQuiz(quiz) {
    if (!quiz.is_published) {
        toast(t('dashboard.publish_first'), 'warning');
        return;
    }
    router.post(route('game.store', quiz.id));
}

const placeholderColors = [
    'from-gray-400 to-gray-600',
    'from-gray-500 to-gray-700',
    'from-gray-300 to-gray-500',
    'from-gray-600 to-gray-800',
    'from-gray-400 to-gray-600',
    'from-gray-500 to-gray-700',
];

function getPlaceholderColor(index) {
    return placeholderColors[index % placeholderColors.length];
}
</script>

<template>
    <AppLayout :title="t('dashboard.title')">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                    {{ t('dashboard.title') }}
                </h2>
                <Link
                    :href="route('quizzes.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ t('dashboard.create_quiz') }}
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Stats -->
                <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.stats_quizzes') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_quizzes }}</p>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('dashboard.stats_games') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_games }}</p>
                    </div>
                </div>

                <!-- Filters & Search -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex gap-2">
                        <button
                            v-for="option in filterOptions"
                            :key="option.value"
                            @click="setFilter(option.value)"
                            :class="[
                                'rounded-lg px-4 py-2 text-sm font-medium transition',
                                activeFilter === option.value
                                    ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                                    : 'bg-white text-gray-600 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800',
                            ]"
                        >
                            {{ option.label() }}
                        </button>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                v-model="search"
                                @input="onSearchInput"
                                type="text"
                                :placeholder="t('dashboard.search_placeholder')"
                                class="rounded-lg border-gray-300 py-2 pl-10 pr-4 text-sm focus:border-gray-900 focus:ring-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:placeholder-gray-500 dark:focus:border-gray-400 dark:focus:ring-gray-400"
                            />
                        </div>
                        <!-- View toggle -->
                        <div class="hidden sm:flex items-center gap-1 rounded-lg bg-white p-1 border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                            <button
                                @click="viewMode = 'grid'"
                                :class="['rounded-md p-1.5 transition', viewMode === 'grid' ? 'bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-600']"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                            </button>
                            <button
                                @click="viewMode = 'list'"
                                :class="['rounded-md p-1.5 transition', viewMode === 'list' ? 'bg-gray-200 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-600']"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="quizzes.length === 0" class="flex flex-col items-center justify-center rounded-xl bg-white py-16 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                    <div class="mb-4 rounded-full bg-gray-100 dark:bg-gray-800 p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p class="mb-4 text-gray-500 dark:text-gray-400">{{ t('dashboard.empty') }}</p>
                    <Link
                        :href="route('quizzes.create')"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ t('dashboard.create_quiz') }}
                    </Link>
                </div>

                <!-- Grid View -->
                <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(quiz, index) in quizzes"
                        :key="quiz.id"
                        class="group cursor-pointer overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100 transition hover:shadow-md dark:bg-gray-900 dark:border-gray-800"
                        @click="$inertia.visit(route('quizzes.edit', quiz.id))"
                    >
                        <!-- Cover -->
                        <div class="relative h-32 overflow-hidden">
                            <img v-if="quiz.cover_image" :src="quiz.cover_image" class="h-full w-full object-cover" alt="" />
                            <div v-else :class="['h-full w-full bg-gradient-to-br', getPlaceholderColor(index)]" class="flex items-center justify-center">
                                <span class="text-4xl font-bold text-white/40">{{ quiz.title.charAt(0).toUpperCase() }}</span>
                            </div>
                            <div class="absolute right-2 top-2">
                                <span :class="[
                                    'rounded-full px-2.5 py-0.5 text-xs font-medium',
                                    quiz.is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
                                ]">
                                    {{ quiz.is_published ? t('dashboard.published') : t('dashboard.draft') }}
                                </span>
                            </div>
                        </div>
                        <!-- Content -->
                        <div class="p-4">
                            <h3 class="mb-1 truncate text-base font-semibold text-gray-900 dark:text-white">{{ quiz.title }}</h3>
                            <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ t('dashboard.questions_count', { count: quiz.questions_count }) }}
                                &middot;
                                {{ new Date(quiz.created_at).toLocaleDateString() }}
                            </p>
                            <!-- Actions -->
                            <div class="flex items-center gap-2" @click.stop>
                                <button
                                    v-if="quiz.is_published"
                                    @click="playQuiz(quiz)"
                                    class="flex-1 rounded-lg bg-indigo-50 py-1.5 text-center text-xs font-medium text-indigo-600 transition hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50"
                                >
                                    ▶ {{ t('dashboard.play') }}
                                </button>
                                <button
                                    @click="duplicateQuiz(quiz)"
                                    class="flex-1 rounded-lg bg-gray-50 py-1.5 text-center text-xs font-medium text-gray-600 transition hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                >
                                    {{ t('dashboard.duplicate') }}
                                </button>
                                <button
                                    @click="confirmDelete(quiz)"
                                    class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                                >
                                    {{ t('dashboard.delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div v-else class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                    <div
                        v-for="(quiz, index) in quizzes"
                        :key="quiz.id"
                        :class="['flex items-center gap-4 p-4 cursor-pointer transition hover:bg-gray-50 dark:hover:bg-gray-800/50', index > 0 ? 'border-t border-gray-100 dark:border-gray-800' : '']"
                        @click="$inertia.visit(route('quizzes.edit', quiz.id))"
                    >
                        <!-- Mini cover -->
                        <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-lg">
                            <img v-if="quiz.cover_image" :src="quiz.cover_image" class="h-full w-full object-cover" alt="" />
                            <div v-else :class="['h-full w-full bg-gradient-to-br', getPlaceholderColor(index)]" class="flex items-center justify-center">
                                <span class="text-lg font-bold text-white/60">{{ quiz.title.charAt(0).toUpperCase() }}</span>
                            </div>
                        </div>
                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <h3 class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ quiz.title }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ t('dashboard.questions_count', { count: quiz.questions_count }) }}
                                &middot;
                                {{ new Date(quiz.created_at).toLocaleDateString() }}
                            </p>
                        </div>
                        <!-- Status -->
                        <span :class="[
                            'rounded-full px-2.5 py-0.5 text-xs font-medium',
                            quiz.is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
                        ]">
                            {{ quiz.is_published ? t('dashboard.published') : t('dashboard.draft') }}
                        </span>
                        <!-- Actions -->
                        <div class="flex items-center gap-2" @click.stop>
                            <button
                                v-if="quiz.is_published"
                                @click="playQuiz(quiz)"
                                class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-600 transition hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50"
                            >
                                ▶ {{ t('dashboard.play') }}
                            </button>
                            <button
                                @click="duplicateQuiz(quiz)"
                                class="rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                            >
                                {{ t('dashboard.duplicate') }}
                            </button>
                            <button
                                @click="confirmDelete(quiz)"
                                class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                            >
                                {{ t('dashboard.delete') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
