<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { toast, Swal } = useSwal();
const page = usePage();

const props = defineProps({
    quizzes: Object,
    categories: Array,
    tags: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category || '');
const tagFilter = ref(props.filters.tag || '');
const sort = ref(props.filters.sort || 'newest');

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

function applyFilters() {
    router.get(route('explore'), {
        search: search.value || undefined,
        category: categoryFilter.value || undefined,
        tag: tagFilter.value || undefined,
        sort: sort.value !== 'newest' ? sort.value : undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}

let searchTimeout = null;
function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
}

function toggleFavorite(quiz) {
    if (!page.props.auth.user) {
        router.visit(route('login'));
        return;
    }

    router.post(route('quizzes.favorite', quiz.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast(t('explore.favorite_success'), 'success');
        }
    });
}

function duplicateQuiz(quiz) {
    if (!page.props.auth.user) {
        router.visit(route('login'));
        return;
    }

    router.post(route('quizzes.duplicate', quiz.id), {}, {
        onSuccess: () => {
            toast(t('dashboard.duplicated_success'), 'success');
        }
    });
}

async function hostQuiz(quiz) {
    if (!page.props.auth.user) {
        router.visit(route('login'));
        return;
    }

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
            input: 'select',
            inputOptions: { 2: '2', 3: '3', 4: '4' },
            inputValue: 2,
            showCancelButton: true,
            confirmButtonText: t('common.confirm'),
            confirmButtonColor: '#6366f1',
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
</script>

<template>
    <Head :title="t('explore.title')" />

    <GuestLayout :title="t('explore.title')">
        <!-- Header Section -->
        <div class="relative overflow-hidden bg-gradient-to-b from-primary-50 via-white to-white py-16 dark:from-gray-900 dark:via-gray-950 dark:to-gray-950">
            <!-- Decorative blurred blobs -->
            <div class="absolute -left-20 top-0 h-72 w-72 rounded-full bg-primary-200/40 blur-3xl dark:bg-primary-900/15"></div>
            <div class="absolute -right-20 bottom-0 h-72 w-72 rounded-full bg-accent-blue/20 blur-3xl dark:bg-accent-blue/10"></div>

            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6 relative z-10">
                <h1 class="font-display text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl dark:text-white">
                    🔍 {{ t('explore.title') }}
                </h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400">
                    {{ t('explore.subtitle') }}
                </p>
            </div>
        </div>

        <div class="mx-auto max-w-6xl px-4 pb-20 sm:px-6">
            <!-- Search & Filters Bar -->
            <div class="mb-8 flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:flex-row sm:items-center">
                <!-- Search Input -->
                <div class="relative flex-grow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        v-model="search"
                        @input="onSearchInput"
                        type="text"
                        :placeholder="t('explore.search_placeholder')"
                        class="w-full rounded-xl border-gray-200 py-2.5 pl-10 pr-4 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-850 dark:text-gray-200 dark:placeholder-gray-500"
                    />
                </div>

                <!-- Category Filter -->
                <div class="w-full sm:w-48">
                    <select
                        v-model="categoryFilter"
                        @change="applyFilters"
                        class="w-full rounded-xl border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-850 dark:text-gray-200"
                    >
                        <option value="">{{ t('explore.all_categories') }}</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                            {{ cat.name }}
                        </option>
                    </select>
                </div>

                <!-- Tag Filter -->
                <div class="w-full sm:w-48">
                    <select
                        v-model="tagFilter"
                        @change="applyFilters"
                        class="w-full rounded-xl border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-850 dark:text-gray-200"
                    >
                        <option value="">{{ t('explore.all_tags') }}</option>
                        <option v-for="tag in tags" :key="tag.id" :value="tag.slug">
                            #{{ tag.name }}
                        </option>
                    </select>
                </div>

                <!-- Sorting -->
                <div class="w-full sm:w-48">
                    <select
                        v-model="sort"
                        @change="applyFilters"
                        class="w-full rounded-xl border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-850 dark:text-gray-200"
                    >
                        <option value="newest">{{ t('explore.sort_newest') }}</option>
                        <option value="popular">{{ t('explore.sort_popular') }}</option>
                        <option value="featured">{{ t('explore.sort_featured') }}</option>
                    </select>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="quizzes.data.length === 0" class="flex flex-col items-center justify-center rounded-2xl bg-white py-16 text-center shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                <div class="mb-4 rounded-full bg-primary-100 dark:bg-primary-900/30 p-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary-500 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 max-w-sm">{{ t('explore.empty_state') }}</p>
            </div>

            <!-- Grid View -->
            <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(quiz, index) in quizzes.data"
                    :key="quiz.id"
                    class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:shadow-xl hover:shadow-primary-500/5 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div>
                        <!-- Cover Image / Gradient -->
                        <div class="relative h-40 w-full overflow-hidden">
                            <img v-if="quiz.cover_image" :src="quiz.cover_image" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" alt="Quiz Cover" />
                            <div v-else :class="['h-full w-full bg-gradient-to-br transition-transform duration-300 group-hover:scale-105', getPlaceholderColor(index)]" class="flex items-center justify-center">
                                <span class="font-display text-5xl font-black text-white/20">Y!</span>
                            </div>

                            <!-- Badges -->
                            <div class="absolute left-3 top-3 flex flex-wrap gap-1.5">
                                <span class="rounded-lg bg-black/60 px-2.5 py-1 text-xs font-bold text-white backdrop-blur-sm">
                                    {{ t('explore.questions_count', { count: quiz.questions_count }) }}
                                </span>
                                <span v-if="quiz.featured" class="rounded-lg bg-yellow-500 px-2.5 py-1 text-xs font-bold text-white shadow-sm">
                                    ⭐ {{ t('explore.featured_badge') }}
                                </span>
                            </div>

                            <!-- Favorite button -->
                            <button
                                @click.stop="toggleFavorite(quiz)"
                                class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 shadow-md backdrop-blur-sm transition-transform hover:scale-110 active:scale-95 dark:bg-gray-800/90"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    :class="quiz.has_favorited ? 'fill-accent-red text-accent-red' : 'text-gray-400 hover:text-accent-red'"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </button>
                        </div>

                        <!-- Card Content -->
                        <div class="p-5">
                            <!-- Category -->
                            <div class="mb-2 flex items-center justify-between">
                                <span v-if="quiz.category" class="rounded-md bg-primary-50 px-2 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
                                    {{ quiz.category.name }}
                                </span>
                                <span v-else class="text-xs text-gray-400 dark:text-gray-500">Uncategorized</span>
                            </div>

                            <h3 class="font-display text-xl font-bold text-gray-900 group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-450 transition-colors">
                                {{ quiz.title }}
                            </h3>

                            <p class="mt-2 line-clamp-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ quiz.description || 'No description provided.' }}
                            </p>

                            <!-- Tags -->
                            <div v-if="quiz.tags && quiz.tags.length > 0" class="mt-3 flex flex-wrap gap-1">
                                <span v-for="tag in quiz.tags" :key="tag.id" class="text-xs font-medium text-gray-400 dark:text-gray-500">
                                    #{{ tag.name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer & Actions -->
                    <div class="border-t border-gray-50 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-gray-900/50">
                        <div class="flex items-center justify-between mb-4">
                            <!-- Author -->
                            <div class="flex items-center gap-2">
                                <img
                                    v-if="quiz.user?.profile_photo_url"
                                    :src="quiz.user.profile_photo_url"
                                    class="h-6 w-6 rounded-full object-cover"
                                    alt="Author"
                                />
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                    {{ t('explore.by_author') }} {{ quiz.user?.name || 'Community' }}
                                </span>
                            </div>

                            <!-- Plays count -->
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                {{ t('explore.plays', { count: quiz.plays_count || 0 }) }}
                            </span>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-2">
                            <Link
                                :href="route('quizzes.practice', quiz.id)"
                                class="flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-gray-200 bg-white py-2 text-xs font-bold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                            >
                                🎮 {{ t('explore.play_solo') }}
                            </Link>

                            <button
                                @click="duplicateQuiz(quiz)"
                                class="flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                :title="t('explore.use_template')"
                            >
                                📋 <span class="hidden lg:inline ml-1.5">{{ t('explore.use_template') }}</span>
                            </button>

                            <button
                                v-if="page.props.auth.user"
                                @click="hostQuiz(quiz)"
                                class="flex items-center justify-center rounded-xl bg-primary-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-primary-700 shadow-sm"
                                title="Host Game"
                            >
                                ⚡ <span class="hidden lg:inline ml-1.5">Host</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="quizzes.links && quizzes.links.length > 3" class="mt-10 flex justify-center gap-1.5">
                <Link
                    v-for="link in quizzes.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    :class="[
                        'rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-200',
                        link.active
                            ? 'bg-primary-600 text-white shadow-md shadow-primary-500/20'
                            : link.url
                                ? 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800'
                                : 'text-gray-400 cursor-not-allowed dark:text-gray-600'
                    ]"
                />
            </div>
        </div>
    </GuestLayout>
</template>
