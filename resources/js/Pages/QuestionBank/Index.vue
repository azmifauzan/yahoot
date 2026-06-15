<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { confirm, toast } = useSwal();

const props = defineProps({
    items: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const categoryFilter = ref(props.filters.category || '');

function applyFilters() {
    router.get(route('question-bank.index'), {
        search: search.value || undefined,
        type: typeFilter.value || undefined,
        category: categoryFilter.value || undefined,
    }, { preserveState: true, replace: true });
}

let searchTimeout = null;
function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
}

function removeItem(item) {
    confirm({
        title: t('question_bank.delete'),
        text: t('question_bank.confirm_delete'),
        confirmText: t('common.delete'),
        cancelText: t('common.cancel'),
        icon: 'warning',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('question-bank.destroy', item.id), {
                preserveScroll: true,
                onSuccess: () => toast(t('question_bank.deleted')),
            });
        }
    });
}

const typeLabels = {
    multiple_choice: () => t('quiz.multiple_choice'),
    true_false: () => t('quiz.true_false'),
    poll: () => t('quiz.poll'),
};
</script>

<template>
    <AppLayout :title="t('question_bank.title')">
        <template #header>
            <h2 class="font-display text-xl font-bold leading-tight text-gray-800 dark:text-gray-100">
                {{ t('question_bank.title') }}
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 flex flex-col gap-3 sm:flex-row">
                    <input
                        v-model="search"
                        @input="onSearchInput"
                        type="text"
                        :placeholder="t('question_bank.search_placeholder')"
                        class="flex-1 rounded-lg border-gray-200 text-sm dark:border-gray-700 dark:bg-gray-800"
                    />
                    <select v-model="typeFilter" @change="applyFilters" class="rounded-lg border-gray-200 text-sm dark:border-gray-700 dark:bg-gray-800">
                        <option value="">{{ t('question_bank.all_types') }}</option>
                        <option value="multiple_choice">{{ t('quiz.multiple_choice') }}</option>
                        <option value="true_false">{{ t('quiz.true_false') }}</option>
                        <option value="poll">{{ t('quiz.poll') }}</option>
                    </select>
                    <select v-model="categoryFilter" @change="applyFilters" class="rounded-lg border-gray-200 text-sm dark:border-gray-700 dark:bg-gray-800">
                        <option value="">{{ t('question_bank.all_categories') }}</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>

                <!-- Empty -->
                <div v-if="items.length === 0" class="flex flex-col items-center justify-center rounded-xl bg-white py-16 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                    <span class="text-4xl mb-3">🗂️</span>
                    <p class="text-gray-500 dark:text-gray-400">{{ t('question_bank.empty') }}</p>
                </div>

                <!-- Grid -->
                <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="flex flex-col rounded-xl bg-white p-4 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800"
                    >
                        <div class="mb-2 flex items-center justify-between">
                            <span class="rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-medium text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                                {{ typeLabels[item.type]?.() ?? item.type }}
                            </span>
                            <span v-if="item.category" class="text-xs text-gray-400">{{ item.category.icon }} {{ item.category.name }}</span>
                        </div>
                        <p class="flex-1 text-sm font-medium text-gray-900 dark:text-white">{{ item.question_text }}</p>
                        <ul class="mt-3 space-y-1">
                            <li
                                v-for="(answer, idx) in item.answers"
                                :key="idx"
                                class="flex items-center gap-1.5 text-xs"
                                :class="answer.is_correct ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-gray-500 dark:text-gray-400'"
                            >
                                <span>{{ answer.is_correct ? '✓' : '·' }}</span>
                                <span class="truncate">{{ answer.answer_text }}</span>
                            </li>
                        </ul>
                        <div class="mt-3 flex justify-end">
                            <button
                                @click="removeItem(item)"
                                class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400"
                            >
                                {{ t('common.delete') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
