<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps({
    submitting: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'submit']);

const items = ref([]);
const selected = ref([]);
const loading = ref(true);

async function load() {
    loading.value = true;
    try {
        const { data } = await window.axios.get(route('question-bank.list'));
        items.value = data.items ?? [];
    } finally {
        loading.value = false;
    }
}

function toggle(id) {
    const idx = selected.value.indexOf(id);
    if (idx === -1) selected.value.push(id);
    else selected.value.splice(idx, 1);
}

function submit() {
    if (selected.value.length === 0) return;
    emit('submit', [...selected.value]);
}

onMounted(load);
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="emit('close')">
        <div class="flex max-h-[80vh] w-full max-w-lg flex-col rounded-2xl bg-white shadow-xl dark:bg-gray-800">
            <div class="flex items-center justify-between border-b border-gray-100 p-5 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ t('question_bank.add_from_bank') }}</h3>
                <button @click="emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto p-5">
                <div v-if="loading" class="py-10 text-center text-gray-400">{{ t('common.loading') }}</div>
                <div v-else-if="items.length === 0" class="py-10 text-center text-gray-400">{{ t('question_bank.empty') }}</div>
                <ul v-else class="space-y-2">
                    <li
                        v-for="item in items"
                        :key="item.id"
                        @click="toggle(item.id)"
                        :class="[
                            'cursor-pointer rounded-lg border p-3 transition',
                            selected.includes(item.id)
                                ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                                : 'border-gray-200 hover:border-gray-300 dark:border-gray-700',
                        ]"
                    >
                        <div class="flex items-start gap-2">
                            <input type="checkbox" :checked="selected.includes(item.id)" class="mt-1" @click.stop="toggle(item.id)" />
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ item.question_text }}</p>
                                <p class="text-xs text-gray-400">{{ item.answers?.length ?? 0 }} {{ t('question_bank.answers') }}</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-100 p-5 dark:border-gray-700">
                <button
                    @click="emit('close')"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200"
                >
                    {{ t('common.cancel') }}
                </button>
                <button
                    @click="submit"
                    :disabled="selected.length === 0 || submitting"
                    class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {{ t('question_bank.add_selected', { count: selected.length }) }}
                </button>
            </div>
        </div>
    </div>
</template>
