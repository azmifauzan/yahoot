<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { confirm, toast } = useSwal();

defineProps({
    categories: Array,
});

const form = useForm({
    name: '',
    icon: '',
});

const editingId = ref(null);
const editForm = useForm({
    name: '',
    icon: '',
});

function createCategory() {
    form.post(route('admin.categories.store'), {
        onSuccess: () => {
            form.reset();
            toast(t('common.success'));
        },
    });
}

function startEdit(category) {
    editingId.value = category.id;
    editForm.name = category.name;
    editForm.icon = category.icon || '';
}

function cancelEdit() {
    editingId.value = null;
}

function saveEdit(category) {
    editForm.put(route('admin.categories.update', category.id), {
        onSuccess: () => {
            editingId.value = null;
            toast(t('common.success'));
        },
    });
}

function confirmDelete(category) {
    confirm({
        title: t('admin.delete_category'),
        text: t('admin.confirm_delete_category'),
        confirmText: t('common.delete'),
        cancelText: t('common.cancel'),
        icon: 'warning',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.categories.destroy', category.id), {
                onSuccess: () => toast(t('admin.category_deleted')),
            });
        }
    });
}
</script>

<template>
    <Head :title="t('admin.categories')" />
    <AppLayout :title="t('admin.categories')">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-display text-xl font-bold text-gray-800 dark:text-gray-100">{{ t('admin.categories') }}</h2>
                <Link :href="route('admin.dashboard')" class="text-sm text-gray-500 hover:text-primary-600 dark:text-gray-400">← {{ t('admin.dashboard') }}</Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Add category -->
                <form @submit.prevent="createCategory" class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 p-4 flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[160px]">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ t('category.name') }}</label>
                        <input v-model="form.name" type="text" maxlength="50" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" />
                        <p v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div class="w-24">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ t('category.icon') }}</label>
                        <input v-model="form.icon" type="text" maxlength="10" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" />
                    </div>
                    <button type="submit" :disabled="form.processing || !form.name" class="px-4 py-2 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 disabled:opacity-50 transition">
                        {{ t('category.add') }}
                    </button>
                </form>

                <!-- Categories list -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-800">
                                <th class="px-6 py-3 font-medium">{{ t('category.icon') }}</th>
                                <th class="px-6 py-3 font-medium">{{ t('category.name') }}</th>
                                <th class="px-6 py-3 font-medium text-center">{{ t('dashboard.title') }}</th>
                                <th class="px-6 py-3 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="category in categories" :key="category.id" class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                <template v-if="editingId === category.id">
                                    <td class="px-6 py-3">
                                        <input v-model="editForm.icon" type="text" maxlength="10" class="w-16 rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" />
                                    </td>
                                    <td class="px-6 py-3">
                                        <input v-model="editForm.name" type="text" maxlength="50" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200" />
                                    </td>
                                    <td class="px-6 py-3 text-center text-gray-500">{{ category.quizzes_count }}</td>
                                    <td class="px-6 py-3 flex gap-2 justify-end">
                                        <button @click="saveEdit(category)" class="text-green-600 hover:text-green-800 text-xs">{{ t('common.save') }}</button>
                                        <button @click="cancelEdit" class="text-gray-500 hover:text-gray-700 text-xs">{{ t('common.cancel') }}</button>
                                    </td>
                                </template>
                                <template v-else>
                                    <td class="px-6 py-3 text-xl">{{ category.icon }}</td>
                                    <td class="px-6 py-3 font-medium text-gray-700 dark:text-gray-200">{{ category.name }}</td>
                                    <td class="px-6 py-3 text-center text-gray-500">{{ category.quizzes_count }}</td>
                                    <td class="px-6 py-3 flex gap-2 justify-end">
                                        <button @click="startEdit(category)" class="text-primary-600 hover:text-primary-800 text-xs">{{ t('common.edit') }}</button>
                                        <button @click="confirmDelete(category)" class="text-red-500 hover:text-red-700 text-xs">{{ t('common.delete') }}</button>
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
