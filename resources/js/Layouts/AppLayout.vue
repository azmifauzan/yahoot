<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import LanguageSwitcher from '@/Components/UI/LanguageSwitcher.vue';
import ThemeSwitcher from '@/Components/UI/ThemeSwitcher.vue';

const { t } = useI18n();

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
            <nav class="bg-white border-b border-gray-100 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden sm:ms-8 sm:flex sm:items-center">
                                <Link
                                    :href="route('dashboard')"
                                    class="rounded-lg px-3 py-2 text-sm font-medium transition"
                                    :class="route().current('dashboard') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white'"
                                >
                                    {{ t('nav.dashboard') }}
                                </Link>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:gap-3">
                            <!-- Language Toggle -->
                            <LanguageSwitcher />

                            <!-- Theme Toggle -->
                            <ThemeSwitcher />

                            <!-- Settings Dropdown -->
                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-primary-300 transition">
                                            <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-lg">
                                            <button type="button" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none dark:text-gray-300 dark:hover:bg-gray-700">
                                                {{ $page.props.auth.user.name }}
                                                <svg class="size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-600">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $page.props.auth.user.name }}</p>
                                            <p class="text-xs text-gray-500 truncate dark:text-gray-400">{{ $page.props.auth.user.email }}</p>
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            {{ t('nav.profile') }}
                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                                            API Tokens
                                        </DropdownLink>

                                        <div class="border-t border-gray-100" />

                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                {{ t('nav.logout') }}
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button class="inline-flex items-center justify-center p-2 rounded-lg text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition dark:text-gray-500 dark:hover:text-gray-400 dark:hover:bg-gray-700 dark:focus:bg-gray-700" @click="showingNavigationDropdown = !showingNavigationDropdown">
                                <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden border-t border-gray-100 dark:border-gray-700">
                    <div class="pt-2 pb-3 space-y-1 px-4">
                        <Link
                            :href="route('dashboard')"
                            class="block rounded-lg px-3 py-2 text-sm font-medium transition"
                            :class="route().current('dashboard') ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700'"
                        >
                            {{ t('nav.dashboard') }}
                        </Link>
                    </div>

                    <div class="pt-4 pb-3 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between px-4">
                            <div class="flex items-center">
                                <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                    <img class="size-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                </div>
                                <div>
                                    <div class="font-medium text-sm text-gray-800 dark:text-gray-200">{{ $page.props.auth.user.name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $page.props.auth.user.email }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <LanguageSwitcher />
                                <ThemeSwitcher />
                            </div>
                        </div>

                        <div class="mt-3 space-y-1 px-4">
                            <Link :href="route('profile.show')" class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                {{ t('nav.profile') }}
                            </Link>

                            <form method="POST" @submit.prevent="logout">
                                <button type="submit" class="block w-full text-left rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                    {{ t('nav.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow-sm dark:bg-gray-800">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
