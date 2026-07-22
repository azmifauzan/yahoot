<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import ImportModal from '@/Components/Quiz/ImportModal.vue';
import Icon from '@/Components/UI/Icon.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { toast, confirm, Swal } = useSwal();

const props = defineProps({
    quizzes: Array,
    filters: Object,
    stats: Object,
    categories: { type: Array, default: () => [] },
    tags: { type: Array, default: () => [] },
});

const search = ref(props.filters.search || '');
const activeFilter = ref(props.filters.filter || 'all');
const categoryFilter = ref(props.filters.category || '');
const tagFilter = ref(props.filters.tag || '');
const viewMode = ref('list');
const showImport = ref(false);

const filterOptions = [
    { value: 'all', label: () => t('dashboard.filter_all') },
    { value: 'draft', label: () => t('dashboard.filter_draft') },
    { value: 'published', label: () => t('dashboard.filter_published') },
];

function applyFilters() {
    router.get(route('dashboard'), {
        filter: activeFilter.value !== 'all' ? activeFilter.value : undefined,
        search: search.value || undefined,
        category: categoryFilter.value || undefined,
        tag: tagFilter.value || undefined,
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

async function playQuiz(quiz) {
    if (!quiz.is_published) {
        toast(t('dashboard.publish_first'), 'warning');
        return;
    }

    // Ask the host which game mode to run.
    const { value: mode } = await Swal.fire({
        title: t('team.choose_mode'),
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: t('team.individual_mode'),
        denyButtonText: t('team.team_mode'),
        cancelButtonText: t('common.cancel'),
        confirmButtonColor: '#6366f1',
        denyButtonColor: '#ec4899',
    }).then((r) => ({ value: r.isConfirmed ? 'individual' : r.isDenied ? 'team' : null }));

    if (!mode) return;

    let teamCount = 2;
    let teamSelection = 'auto';
    if (mode === 'team') {
        const { value } = await Swal.fire({
            title: t('team.team_count'),
            input: 'range',
            inputAttributes: { min: 2, max: 6, step: 1 },
            inputValue: 2,
            confirmButtonText: t('common.confirm'),
            confirmButtonColor: '#ec4899',
        });
        if (!value) return;
        teamCount = value;

        const { value: selection } = await Swal.fire({
            title: t('team.team_selection'),
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: t('team.auto_balance'),
            denyButtonText: t('team.manual_select'),
            cancelButtonText: t('common.cancel'),
            confirmButtonColor: '#6366f1',
            denyButtonColor: '#ec4899',
        }).then((r) => ({ value: r.isConfirmed ? 'auto' : r.isDenied ? 'manual' : null }));
        if (!selection) return;
        teamSelection = selection;
    }

    // Ask which engagement features to enable for this session.
    const { value: settings } = await Swal.fire({
        title: t('host.game_settings'),
        html: `
            <label class="flex items-center gap-2 text-left">
                <input type="checkbox" id="swal-reactions" checked class="w-4 h-4">
                <span>${t('reactions.enabled')}</span>
            </label>
        `,
        confirmButtonText: t('common.confirm'),
        confirmButtonColor: '#6366f1',
        showCancelButton: true,
        cancelButtonText: t('common.cancel'),
        preConfirm: () => ({
            reactions_enabled: document.getElementById('swal-reactions').checked,
        }),
    });
    if (!settings) return;

    // Open the host session in a new tab so the dashboard stays usable
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = route('game.store', quiz.id);
    form.target = '_blank';

    const fields = {
        _token: token,
        mode,
        reactions_enabled: settings.reactions_enabled ? 1 : 0,
    };
    if (mode === 'team') {
        fields.team_count = teamCount;
        fields.team_selection = teamSelection;
    }
    for (const [name, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    form.remove();
}

const placeholderColors = [
    'from-primary-400 to-primary-600',
    'from-accent-blue to-primary-500',
    'from-accent-red to-primary-500',
    'from-primary-500 to-accent-blue',
    'from-accent-green to-primary-500',
    'from-primary-600 to-accent-red',
];

function getPlaceholderColor(index) {
    return placeholderColors[index % placeholderColors.length];
}
</script>

<template>
    <AppLayout :title="t('dashboard.title')">
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="font-display text-2xl font-black leading-tight tracking-tight text-gray-900 dark:text-white">
                    {{ t('dashboard.title') }}
                </h2>
                <div class="flex items-center gap-2">
                    <button
                        @click="showImport = true"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:-translate-y-0.5 hover:border-primary-200 hover:text-primary-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <Icon name="upload" class="h-4 w-4" />
                        <span class="hidden sm:inline">{{ t('import_export.import') }}</span>
                    </button>
                    <Link
                        :href="route('quizzes.create')"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-primary-600/20 transition hover:-translate-y-0.5 hover:bg-primary-700"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ t('dashboard.create_quiz') }}
                    </Link>
                </div>
            </div>
        </template>

        <ImportModal v-if="showImport" @close="showImport = false" />

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Stats -->
                <div class="mb-6 grid grid-cols-2 gap-4">
                    <div class="group rounded-[1.4rem] border border-white bg-white/90 p-5 shadow-lg shadow-primary-950/5 backdrop-blur transition hover:-translate-y-1 dark:border-white/10 dark:bg-gray-900/90">
                        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-100 text-primary-700 transition group-hover:-rotate-6 group-hover:scale-110 dark:bg-primary-950 dark:text-primary-300">
                            <Icon name="document" class="h-5 w-5" />
                        </div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('dashboard.stats_quizzes') }}</p>
                        <p class="mt-1 font-display text-3xl font-black text-gray-900 dark:text-white">{{ stats.total_quizzes }}</p>
                    </div>
                    <div class="group rounded-[1.4rem] border border-white bg-white/90 p-5 shadow-lg shadow-primary-950/5 backdrop-blur transition hover:-translate-y-1 dark:border-white/10 dark:bg-gray-900/90">
                        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-2xl bg-accent-yellow/60 text-amber-800 transition group-hover:rotate-6 group-hover:scale-110 dark:bg-amber-900/30 dark:text-amber-300">
                            <Icon name="bolt" class="h-5 w-5" />
                        </div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('dashboard.stats_games') }}</p>
                        <p class="mt-1 font-display text-3xl font-black text-gray-900 dark:text-white">{{ stats.total_games }}</p>
                    </div>
                </div>

                <!-- Filters & Search -->
                <div class="mb-6 flex flex-col gap-4 rounded-[1.4rem] border border-white bg-white/85 p-4 shadow-lg shadow-primary-950/5 backdrop-blur sm:flex-row sm:items-center sm:justify-between dark:border-white/10 dark:bg-gray-900/85">
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="option in filterOptions"
                            :key="option.value"
                            @click="setFilter(option.value)"
                            :class="[
                                'whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition',
                                activeFilter === option.value
                                    ? 'bg-primary-600 text-white shadow-sm shadow-primary-500/30'
                                    : 'bg-gray-50 text-gray-600 hover:bg-primary-50 hover:text-primary-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-primary-950/40',
                            ]"
                        >
                            {{ option.label() }}
                        </button>
                        <select
                            v-model="categoryFilter"
                            @change="applyFilters"
                            class="rounded-xl border-0 bg-gray-50 text-sm focus:ring-2 focus:ring-primary-400 dark:bg-gray-800 dark:text-gray-200"
                        >
                            <option value="">{{ t('category.all') }}</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                        <select
                            v-model="tagFilter"
                            @change="applyFilters"
                            class="rounded-xl border-0 bg-gray-50 text-sm focus:ring-2 focus:ring-primary-400 dark:bg-gray-800 dark:text-gray-200"
                        >
                            <option value="">{{ t('tag.all') }}</option>
                            <option v-for="tag in tags" :key="tag.id" :value="tag.id">
                                #{{ tag.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                v-model="search"
                                @input="onSearchInput"
                                type="text"
                                :placeholder="t('dashboard.search_placeholder')"
                                class="w-full rounded-xl border-0 bg-gray-50 py-2.5 pl-10 pr-4 text-sm focus:ring-2 focus:ring-primary-400 sm:w-auto dark:bg-gray-800 dark:text-gray-200 dark:placeholder-gray-500"
                            />
                        </div>
                        <!-- View toggle -->
                        <div class="hidden items-center gap-1 rounded-xl bg-gray-50 p-1 sm:flex dark:bg-gray-800">
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
                <div v-if="quizzes.length === 0" class="flex flex-col items-center justify-center rounded-[1.5rem] border border-white bg-white/90 py-16 shadow-xl shadow-primary-950/5 dark:border-white/10 dark:bg-gray-900/90">
                    <div class="mb-4 rounded-full bg-primary-100 dark:bg-primary-900/30 p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p class="mb-4 text-gray-500 dark:text-gray-400">{{ t('dashboard.empty') }}</p>
                    <Link
                        :href="route('quizzes.create')"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
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
                        class="group cursor-pointer overflow-hidden rounded-[1.4rem] border border-white bg-white/90 shadow-lg shadow-primary-950/5 transition hover:-translate-y-1.5 hover:shadow-xl hover:shadow-primary-500/10 dark:border-white/10 dark:bg-gray-900/90 animate-slide-in-up"
                        :style="{ animationDelay: `${Math.min(index * 0.05, 0.4)}s` }"
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
                            <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ t('dashboard.questions_count', { count: quiz.questions_count }) }}
                                &middot;
                                {{ new Date(quiz.created_at).toLocaleDateString() }}
                            </p>
                            <div v-if="quiz.category || quiz.tags?.length" class="mb-3 flex flex-wrap items-center gap-1">
                                <span v-if="quiz.category" class="rounded-full bg-primary-50 dark:bg-primary-900/30 px-2 py-0.5 text-xs text-primary-600 dark:text-primary-400">
                                    {{ quiz.category.name }}
                                </span>
                                <span v-for="tag in quiz.tags" :key="tag.id" class="rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-500 dark:text-gray-400">
                                    #{{ tag.name }}
                                </span>
                            </div>
                            <!-- Actions -->
                            <div class="flex flex-wrap items-center gap-2" @click.stop>
                                <button
                                    v-if="quiz.is_published"
                                    @click="playQuiz(quiz)"
                                    class="flex-1 rounded-lg bg-primary-50 py-1.5 px-2 text-center text-xs font-medium text-primary-600 transition hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-400 dark:hover:bg-primary-900/50"
                                >
                                    <Icon name="play" class="mx-auto h-4 w-4 sm:mx-0" /> <span class="hidden sm:inline">{{ t('dashboard.play') }}</span>
                                </button>
                                <Link
                                    :href="route('quizzes.practice', quiz.id)"
                                    class="flex-1 rounded-lg bg-amber-50 py-1.5 px-2 text-center text-xs font-medium text-amber-600 transition hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50 flex items-center justify-center gap-1"
                                >
                                    <Icon name="target" class="h-4 w-4" /> <span class="hidden sm:inline">{{ t('practice.start_practice') }}</span>
                                </Link>
                                <button
                                    @click="duplicateQuiz(quiz)"
                                    class="flex-1 rounded-lg bg-gray-50 py-1.5 px-2 text-center text-xs font-medium text-gray-600 transition hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                >
                                    <Icon name="copy" class="mx-auto h-4 w-4 sm:hidden" /><span class="hidden sm:inline">{{ t('dashboard.duplicate') }}</span>
                                </button>
                                <Link
                                    :href="route('quizzes.history', quiz.id)"
                                    class="flex-1 rounded-lg bg-gray-50 py-1.5 px-2 text-center text-xs font-medium text-gray-600 transition hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 flex items-center justify-center gap-1"
                                >
                                    <Icon name="clock" class="h-4 w-4" /> <span class="hidden sm:inline">{{ t('host.history') }}</span>
                                </Link>
                                <button
                                    @click="confirmDelete(quiz)"
                                    class="flex-1 rounded-lg bg-red-50 py-1.5 px-2 text-center text-xs font-medium text-red-600 transition hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                                >
                                    <Icon name="trash" class="mx-auto h-4 w-4 sm:hidden" /><span class="hidden sm:inline">{{ t('dashboard.delete') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div v-else class="overflow-hidden rounded-[1.4rem] border border-white bg-white/90 shadow-lg shadow-primary-950/5 dark:border-white/10 dark:bg-gray-900/90">
                    <div
                        v-for="(quiz, index) in quizzes"
                        :key="quiz.id"
                        :class="['flex flex-col sm:flex-row sm:items-center gap-4 p-4 cursor-pointer transition hover:bg-gray-50 dark:hover:bg-gray-800/50', index > 0 ? 'border-t border-gray-100 dark:border-gray-800' : '']"
                        @click="$inertia.visit(route('quizzes.edit', quiz.id))"
                    >
                        <div class="flex items-center gap-4 flex-1">
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
                                    <template v-if="quiz.category">
                                        &middot; {{ quiz.category.name }}
                                    </template>
                                </p>
                            </div>
                            <!-- Status (Desktop) -->
                            <span :class="[
                                'hidden sm:inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium shrink-0',
                                quiz.is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
                            ]">
                                {{ quiz.is_published ? t('dashboard.published') : t('dashboard.draft') }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto mt-2 sm:mt-0">
                            <!-- Status (Mobile) -->
                            <span :class="[
                                'sm:hidden rounded-full px-2.5 py-0.5 text-xs font-medium shrink-0',
                                quiz.is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
                            ]">
                                {{ quiz.is_published ? t('dashboard.published') : t('dashboard.draft') }}
                            </span>

                            <!-- Actions -->
                            <div class="flex flex-wrap items-center justify-end gap-2 shrink-0" @click.stop>
                                <button
                                    v-if="quiz.is_published"
                                    @click="playQuiz(quiz)"
                                    class="rounded-lg bg-primary-50 px-3 py-1.5 text-xs font-medium text-primary-600 transition hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-400 dark:hover:bg-primary-900/50"
                                >
                                    <Icon name="play" class="inline h-4 w-4" /> <span class="hidden md:inline">{{ t('dashboard.play') }}</span>
                                </button>
                                <Link
                                    :href="route('quizzes.practice', quiz.id)"
                                    class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-600 transition hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50"
                                >
                                    <Icon name="target" class="inline h-4 w-4" /> <span class="hidden md:inline">{{ t('practice.start_practice') }}</span>
                                </Link>
                                <button
                                    @click="duplicateQuiz(quiz)"
                                    class="rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                >
                                    <Icon name="copy" class="inline h-4 w-4 md:hidden" /><span class="hidden md:inline">{{ t('dashboard.duplicate') }}</span>
                                </button>
                                <Link
                                    :href="route('quizzes.history', quiz.id)"
                                    class="rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 flex items-center justify-center gap-1"
                                >
                                    <Icon name="clock" class="inline h-4 w-4" /> <span class="hidden md:inline">{{ t('host.history') }}</span>
                                </Link>
                                <button
                                    @click="confirmDelete(quiz)"
                                    class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                                >
                                    <Icon name="trash" class="inline h-4 w-4 md:hidden" /><span class="hidden md:inline">{{ t('dashboard.delete') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
