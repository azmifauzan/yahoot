<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { toast } = useSwal();

const props = defineProps({
    settings: Object,
});

const form = useForm({
    app_name: props.settings.app_name ?? 'Yahoot',
    default_language: props.settings.default_language ?? 'id',
    allow_registration: props.settings.allow_registration === '1',
    require_email_verification: props.settings.require_email_verification === '1',
    allow_guest_play: props.settings.allow_guest_play === '1',
    maintenance_mode: props.settings.maintenance_mode === '1',
});

function save() {
    form.put(route('admin.settings.update'), {
        onSuccess: () => toast(t('common.success')),
    });
}
</script>

<template>
    <AppLayout :title="t('admin.settings')">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">{{ t('admin.settings') }}</h2>
                <Link :href="route('admin.dashboard')" class="text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400">← {{ t('admin.dashboard') }}</Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="save" class="space-y-6">
                    <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 p-6 space-y-5">
                        <!-- App name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('admin.setting_app_name') }}</label>
                            <input
                                v-model="form.app_name"
                                type="text"
                                class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            />
                        </div>

                        <!-- Default language -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('admin.setting_language') }}</label>
                            <select v-model="form.default_language" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                <option value="id">Bahasa Indonesia</option>
                                <option value="en">English</option>
                            </select>
                        </div>

                        <!-- Toggles -->
                        <div class="space-y-4 pt-2 border-t border-gray-100 dark:border-gray-800">
                            <label v-for="key in ['allow_registration', 'require_email_verification', 'allow_guest_play', 'maintenance_mode']" :key="key" class="flex items-center justify-between">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ t(`admin.setting_${key}`) }}</span>
                                <button
                                    type="button"
                                    @click="form[key] = !form[key]"
                                    :class="['relative inline-flex h-6 w-11 rounded-full transition', form[key] ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700']"
                                >
                                    <span :class="['absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform', form[key] ? 'translate-x-5' : 'translate-x-0']" />
                                </button>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition"
                        >
                            {{ t('common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
