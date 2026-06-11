<script setup>
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import LanguageSwitcher from '@/Components/UI/LanguageSwitcher.vue';
import ThemeSwitcher from '@/Components/UI/ThemeSwitcher.vue';
import SidebarNav from '@/Components/Navigation/SidebarNav.vue';
import { useSidebar } from '@/Composables/useSidebar';

const { t } = useI18n();
const page = usePage();

defineProps({
    title: String,
    fullscreen: { type: Boolean, default: false },
});

const { collapsed, mobileOpen, toggleCollapsed, toggleMobile, closeMobile } = useSidebar();

const menuLinks = computed(() => {
    const links = [
        { href: route('dashboard'), label: t('nav.my_quizzes'), icon: 'home', active: route().current('dashboard') },
        { href: route('profile.show'), label: t('nav.profile'), icon: 'user', active: route().current('profile.show') },
    ];

    if (page.props.jetstream.hasApiFeatures) {
        links.push({ href: route('api-tokens.index'), label: t('nav.api_tokens'), icon: 'key', active: route().current('api-tokens.index') });
    }

    return links;
});

const adminLinks = computed(() => {
    if (!page.props.auth.user?.is_admin) {
        return [];
    }

    return [
        { href: route('admin.dashboard'), label: t('admin.dashboard'), icon: 'shield', active: route().current('admin.dashboard') },
        { href: route('admin.users.index'), label: t('admin.users'), icon: 'users', active: route().current('admin.users.*') },
        { href: route('admin.quizzes.index'), label: t('admin.quizzes'), icon: 'document', active: route().current('admin.quizzes.*') },
        { href: route('admin.games.index'), label: t('admin.games'), icon: 'bolt', active: route().current('admin.games.*') },
        { href: route('admin.settings.index'), label: t('admin.settings'), icon: 'cog', active: route().current('admin.settings.*') },
    ];
});

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-white dark:bg-gray-950">
            <!-- Fullscreen mode: header only, no sidebar -->
            <template v-if="fullscreen">
                <header v-if="$slots.header" class="bg-white shadow-sm dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <main class="animate-page-enter">
                    <slot />
                </main>
            </template>

            <!-- Sidebar layout -->
            <div v-else class="flex">
                <!-- Mobile backdrop -->
                <div
                    v-if="mobileOpen"
                    class="fixed inset-0 z-40 bg-black/50 lg:hidden"
                    @click="closeMobile"
                />

                <!-- Sidebar -->
                <aside
                    :class="[
                        'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform duration-200 dark:border-gray-800 dark:bg-gray-900',
                        'lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:transition-[width]',
                        collapsed ? 'lg:w-20' : 'lg:w-64',
                        mobileOpen ? 'translate-x-0' : '-translate-x-full',
                    ]"
                >
                    <!-- Logo + collapse toggle -->
                    <div class="flex h-16 shrink-0 items-center justify-between border-b border-gray-100 px-4 dark:border-gray-800">
                        <Link :href="route('dashboard')" class="flex items-center overflow-hidden">
                            <ApplicationMark :icon-only="collapsed" />
                        </Link>
                        <button
                            @click="toggleCollapsed"
                            class="hidden h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 lg:flex dark:hover:bg-gray-800 dark:hover:text-gray-300"
                            :title="collapsed ? t('nav.expand') : t('nav.collapse')"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path v-if="collapsed" stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Nav -->
                    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4">
                        <SidebarNav :links="menuLinks" :collapsed="collapsed" :label="t('nav.menu')" @navigate="closeMobile" />

                        <div v-if="adminLinks.length">
                            <div class="my-3 border-t border-gray-100 dark:border-gray-800" />
                            <SidebarNav :links="adminLinks" :collapsed="collapsed" :label="t('nav.admin_panel')" @navigate="closeMobile" />
                        </div>
                    </nav>

                    <!-- User card + switchers + logout -->
                    <div class="shrink-0 border-t border-gray-100 p-3 dark:border-gray-800">
                        <div :class="['flex items-center gap-3 rounded-lg px-2 py-2', collapsed ? 'justify-center' : '']">
                            <img
                                v-if="page.props.jetstream.managesProfilePhotos"
                                class="h-9 w-9 shrink-0 rounded-full object-cover"
                                :src="page.props.auth.user.profile_photo_url"
                                :alt="page.props.auth.user.name"
                            />
                            <div v-else class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                                {{ page.props.auth.user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div v-if="!collapsed" class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-200">{{ page.props.auth.user.name }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ page.props.auth.user.email }}</p>
                            </div>
                        </div>

                        <div :class="['mt-2 flex items-center gap-2', collapsed ? 'flex-col px-0' : 'px-2']">
                            <LanguageSwitcher v-if="!collapsed" />
                            <ThemeSwitcher />
                        </div>

                        <form @submit.prevent="logout" class="mt-2">
                            <button
                                type="submit"
                                :title="t('nav.logout')"
                                :class="[
                                    'flex w-full items-center gap-3 rounded-lg px-2 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 hover:text-red-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-red-400',
                                    collapsed ? 'justify-center' : '',
                                ]"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span v-if="!collapsed">{{ t('nav.logout') }}</span>
                            </button>
                        </form>
                    </div>
                </aside>

                <!-- Main column -->
                <div class="flex min-w-0 flex-1 flex-col">
                    <!-- Mobile topbar -->
                    <div class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-gray-200 bg-white/80 px-4 backdrop-blur-lg lg:hidden dark:border-gray-800 dark:bg-gray-950/80">
                        <button
                            @click="toggleMobile"
                            class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <ApplicationMark />
                    </div>

                    <!-- Page Heading -->
                    <header v-if="$slots.header" class="bg-white shadow-sm dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
                        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                            <slot name="header" />
                        </div>
                    </header>

                    <!-- Page Content -->
                    <main class="flex-1 animate-page-enter">
                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>
