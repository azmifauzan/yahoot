<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/UI/Icon.vue';
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
            <h2 class="font-display text-2xl font-black leading-tight tracking-tight text-gray-900 dark:text-white">
                {{ t('question_bank.title') }}
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 flex flex-col gap-3 rounded-[1.4rem] border border-white bg-white/85 p-4 shadow-lg shadow-primary-950/5 sm:flex-row dark:border-white/10 dark:bg-gray-900/85">
                    <input
                        v-model="search"
                        @input="onSearchInput"
                        type="text"
                        :placeholder="t('question_bank.search_placeholder')"
                        class="flex-1 rounded-xl border-0 bg-gray-50 text-sm focus:ring-2 focus:ring-primary-400 dark:bg-gray-800"
                    />
                    <select v-model="typeFilter" @change="applyFilters" class="rounded-xl border-0 bg-gray-50 text-sm focus:ring-2 focus:ring-primary-400 dark:bg-gray-800">
                        <option value="">{{ t('question_bank.all_types') }}</option>
                        <option value="multiple_choice">{{ t('quiz.multiple_choice') }}</option>
                        <option value="true_false">{{ t('quiz.true_false') }}</option>
                        <option value="poll">{{ t('quiz.poll') }}</option>
                    </select>
                    <select v-model="categoryFilter" @change="applyFilters" class="rounded-xl border-0 bg-gray-50 text-sm focus:ring-2 focus:ring-primary-400 dark:bg-gray-800">
                        <option value="">{{ t('question_bank.all_categories') }}</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>

                <!-- Empty -->
                <div v-if="items.length === 0" class="flex flex-col items-center justify-center rounded-[1.5rem] border border-white bg-white/90 py-16 shadow-xl shadow-primary-950/5 dark:border-white/10 dark:bg-gray-900/90">
                    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                        <Icon name="folder" class="h-7 w-7" />
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">{{ t('question_bank.empty') }}</p>
                </div>

                <!-- Grid -->
                <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="flex flex-col rounded-[1.4rem] border border-white bg-white/90 p-5 shadow-lg shadow-primary-950/5 transition hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-gray-900/90"
                    >
                        <div class="mb-2 flex items-center justify-between">
                            <span class="rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-medium text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                                {{ typeLabels[item.type]?.() ?? item.type }}
                            </span>
                            <span v-if="item.category" class="text-xs text-gray-400">{{ item.category.name }}</span>
                        </div>
                        <p class="flex-1 text-sm font-medium text-gray-900 dark:text-white">{{ item.question_text }}</p>
                        <ul class="mt-3 space-y-1">
                            <li
                                v-for="(answer, idx) in item.answers"
                                :key="idx"
                                class="flex items-center gap-1.5 text-xs"
                                :class="answer.is_correct ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-gray-500 dark:text-gray-400'"
                            >
                                <Icon v-if="answer.is_correct" name="check" class="h-3.5 w-3.5 shrink-0" />
                                <span v-else class="h-1.5 w-1.5 shrink-0 rounded-full bg-current opacity-50"></span>
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
