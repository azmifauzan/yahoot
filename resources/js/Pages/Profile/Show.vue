<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

const { t } = useI18n();

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});

const activeTab = ref('profile');

const tabs = [
    { key: 'profile', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { key: 'security', icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7z' },
    { key: 'sessions', icon: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
    { key: 'account', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z' },
];
</script>

<template>
    <AppLayout :title="t('nav.profile')">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-100">
                {{ t('nav.profile') }}
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Tabs -->
                <div class="mb-6 flex gap-1 rounded-xl bg-white p-1 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
                            'flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition',
                            activeTab === tab.key
                                ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200',
                        ]"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon" />
                        </svg>
                        <span class="hidden sm:inline">{{ t(`profile.tab_${tab.key}`) }}</span>
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
                    <!-- Profile Tab -->
                    <div v-if="activeTab === 'profile'">
                        <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                            <UpdateProfileInformationForm :user="$page.props.auth.user" />
                        </div>
                    </div>

                    <!-- Security Tab -->
                    <div v-if="activeTab === 'security'">
                        <div v-if="$page.props.jetstream.canUpdatePassword">
                            <UpdatePasswordForm />
                        </div>

                        <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication" class="border-t border-gray-100 dark:border-gray-800">
                            <TwoFactorAuthenticationForm
                                :requires-confirmation="confirmsTwoFactorAuthentication"
                            />
                        </div>
                    </div>

                    <!-- Sessions Tab -->
                    <div v-if="activeTab === 'sessions'">
                        <LogoutOtherBrowserSessionsForm :sessions="sessions" />
                    </div>

                    <!-- Account Tab -->
                    <div v-if="activeTab === 'account'">
                        <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
                            <DeleteUserForm />
                        </template>
                        <div v-else class="p-8 text-center text-gray-500 dark:text-gray-400">
                            {{ t('profile.no_account_settings') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
