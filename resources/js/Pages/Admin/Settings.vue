<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    settings: Object,
});
</script>

<template>
    <AppLayout title="System Settings">
        <Head title="System Settings" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">System Settings</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        <Link :href="route('admin.dashboard')" class="hover:text-gray-700 dark:hover:text-gray-300">Admin</Link>
                        / Settings
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div class="px-6 py-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Application Name</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">The name of your application</p>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-lg">
                            {{ settings.app_name }}
                        </span>
                    </div>

                    <div class="px-6 py-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Default Locale</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Default language for the application</p>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-lg uppercase">
                            {{ settings.app_locale }}
                        </span>
                    </div>

                    <div class="px-6 py-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">Registration</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Allow new user registration</p>
                        </div>
                        <span :class="settings.registration_enabled ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'"
                            class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full">
                            {{ settings.registration_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 rounded-b-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        System settings are configured via environment variables. Update the <code class="font-mono bg-gray-200 dark:bg-gray-700 px-1 rounded">.env</code> file and restart the application to apply changes.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
