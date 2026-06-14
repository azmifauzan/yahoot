<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import LanguageSwitcher from '@/Components/UI/LanguageSwitcher.vue';
import ThemeSwitcher from '@/Components/UI/ThemeSwitcher.vue';
import SidebarNav from '@/Components/Navigation/SidebarNav.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { useSidebar } from '@/Composables/useSidebar';

const { t } = useI18n();
const page = usePage();

defineProps({
    title: String,
    fullscreen: { type: Boolean, default: false },
});

const { collapsed, mobileOpen, toggleCollapsed, toggleMobile, closeMobile } = useSidebar();

const desktopQuery = window.matchMedia('(min-width: 1024px)');
const isDesktop = ref(desktopQuery.matches);
const updateIsDesktop = (event) => { isDesktop.value = event.matches; };
onMounted(() => desktopQuery.addEventListener('change', updateIsDesktop));
onUnmounted(() => desktopQuery.removeEventListener('change', updateIsDesktop));

const effectiveCollapsed = computed(() => isDesktop.value && collapsed.value);

const menuLinks = computed(() => {
    const links = [
        { href: route('dashboard'), label: t('nav.my_quizzes'), icon: 'home', active: route().current('dashboard') },
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
        { href: route('admin.categories.index'), label: t('admin.categories'), icon: 'tag', active: route().current('admin.categories.*') },
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

        <div class="h-screen bg-white dark:bg-gray-950 flex flex-col">
            <!-- Global Top Navbar -->
            <div class="sticky top-0 z-50 flex h-16 shrink-0 items-center justify-between border-b border-gray-200 bg-white/80 px-4 sm:px-6 backdrop-blur-lg dark:border-gray-800 dark:bg-gray-950/80">
                <!-- Left side -->
                <div class="flex items-center gap-3">
                    <button
                        v-if="!fullscreen"
                        @click="toggleMobile"
                        :aria-label="t('nav.menu')"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 lg:hidden dark:text-gray-400 dark:hover:bg-gray-800"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <!-- Logo -->
                    <Link :href="route('dashboard')" class="flex items-center overflow-hidden" @click="closeMobile">
                        <ApplicationMark />
                    </Link>
                </div>

                <!-- Right side (Switchers) -->
                <div class="flex items-center gap-2">
                    <LanguageSwitcher />
                    <ThemeSwitcher />
                </div>
            </div>

            <!-- Fullscreen mode: header only, no sidebar -->
            <template v-if="fullscreen">
                <header v-if="$slots.header" class="bg-white shadow-sm dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 shrink-0">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <main class="animate-page-enter flex-1 overflow-hidden flex flex-col relative">
                    <slot />
                </main>
            </template>

            <!-- Sidebar layout -->
            <div v-else class="flex flex-1 overflow-hidden">
                <!-- Mobile backdrop -->
                <div
                    v-if="mobileOpen"
                    class="fixed inset-0 z-40 bg-black/50 lg:hidden"
                    @click="closeMobile"
                />

                <!-- Sidebar -->
                <aside
                    :class="[
                        'fixed inset-y-0 left-0 z-40 flex flex-col border-r border-gray-200 bg-white transition-transform duration-200 dark:border-gray-800 dark:bg-gray-900',
                        'lg:sticky lg:top-0 lg:h-[calc(100vh-4rem)] lg:translate-x-0 lg:transition-[width]',
                        collapsed ? 'lg:w-20' : 'lg:w-64',
                        mobileOpen ? 'translate-x-0' : '-translate-x-full',
                    ]"
                >
                    <!-- Sidebar collapse toggle -->
                    <div class="flex h-12 shrink-0 items-center justify-end border-b border-gray-100 px-4 dark:border-gray-800">
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
                        <SidebarNav :links="menuLinks" :collapsed="effectiveCollapsed" :label="t('nav.menu')" @navigate="closeMobile" />

                        <div v-if="adminLinks.length">
                            <div class="my-3 border-t border-gray-100 dark:border-gray-800" />
                            <SidebarNav :links="adminLinks" :collapsed="effectiveCollapsed" :label="t('nav.admin_panel')" @navigate="closeMobile" />
                        </div>
                    </nav>

                    <!-- User Dropdown (Profile & Logout) -->
                    <div class="shrink-0 border-t border-gray-100 p-3 dark:border-gray-800">
                        <Dropdown align="top-left" width="48" :contentClasses="['py-1', 'bg-white', 'dark:bg-gray-900']">
                            <template #trigger>
                                <button :class="['flex w-full items-center gap-3 rounded-lg px-2 py-2 hover:bg-gray-50 dark:hover:bg-gray-800 transition text-left', effectiveCollapsed ? 'justify-center' : '']">
                                    <img
                                        v-if="page.props.jetstream.managesProfilePhotos"
                                        class="h-9 w-9 shrink-0 rounded-full object-cover"
                                        :src="page.props.auth.user.profile_photo_url"
                                        :alt="page.props.auth.user.name"
                                    />
                                    <div v-else class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                                        {{ page.props.auth.user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div v-if="!effectiveCollapsed" class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-200">{{ page.props.auth.user.name }}</p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ page.props.auth.user.email }}</p>
                                    </div>
                                    <svg v-if="!effectiveCollapsed" class="h-4 w-4 shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.show')">
                                    {{ t('nav.profile') }}
                                </DropdownLink>
                                <div class="border-t border-gray-200 dark:border-gray-600"></div>
                                <form @submit.prevent="logout">
                                    <DropdownLink as="button">
                                        {{ t('nav.logout') }}
                                    </DropdownLink>
                                </form>
                            </template>
                        </Dropdown>
                    </div>
                </aside>

                <!-- Main column -->
                <div class="flex min-w-0 flex-1 flex-col overflow-y-auto">
                    <!-- Page Heading -->
                    <header v-if="$slots.header" class="bg-white shadow-sm dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 shrink-0">
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
