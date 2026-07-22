<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LanguageSwitcher from '@/Components/UI/LanguageSwitcher.vue';
import ThemeSwitcher from '@/Components/UI/ThemeSwitcher.vue';

const { t } = useI18n();

defineProps({
    title: String,
});

const mobileMenuOpen = ref(false);
</script>

<template>
    <Head :title="title" />

    <div class="public-grid flex min-h-screen flex-col bg-[#fbfaff] text-gray-900 dark:bg-gray-950 dark:text-white">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 border-b border-white/70 bg-white/75 shadow-sm shadow-primary-950/5 backdrop-blur-xl dark:border-white/10 dark:bg-gray-950/75">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <!-- Left: Logo + nav links -->
                <div class="flex items-center gap-6">
                    <Link href="/" class="group flex items-center">
                        <img src="/images/logo.png?v=3" alt="Yahoot" class="h-9 w-auto object-contain transition duration-300 group-hover:-rotate-3 group-hover:scale-105 sm:h-10 dark:hidden" />
                        <img src="/images/logo-dark.png?v=1" alt="Yahoot" class="hidden h-9 w-auto object-contain transition duration-300 group-hover:-rotate-3 group-hover:scale-105 sm:h-10 dark:block" />
                    </Link>

                    <!-- Desktop nav links -->
                    <div class="hidden sm:flex items-center gap-1">
                        <Link
                            :href="route('explore')"
                            class="rounded-xl px-3 py-2 text-sm font-semibold transition"
                            :class="route().current('explore') ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300' : 'text-gray-600 hover:bg-white hover:text-primary-700 dark:text-gray-300 dark:hover:bg-gray-800'"
                        >
                            {{ t('nav.explore') || 'Explore' }}
                        </Link>
                        <Link
                            :href="route('leaderboard')"
                            class="rounded-xl px-3 py-2 text-sm font-semibold transition"
                            :class="route().current('leaderboard') ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300' : 'text-gray-600 hover:bg-white hover:text-primary-700 dark:text-gray-300 dark:hover:bg-gray-800'"
                        >
                            {{ t('nav.leaderboard') }}
                        </Link>
                    </div>
                </div>

                <!-- Right: utilities + auth + hamburger -->
                <div class="flex items-center gap-2">
                    <a
                        href="https://github.com/azmifauzan/yahoot"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="hidden items-center gap-1.5 rounded-xl border border-gray-200 bg-white/80 px-3 py-2 text-sm font-semibold text-gray-600 transition hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-sm lg:inline-flex dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-300"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 16 16" fill="currentColor"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/></svg>
                        GitHub
                    </a>
                    <LanguageSwitcher />
                    <ThemeSwitcher />

                    <template v-if="route().has('login')">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="hidden rounded-xl bg-primary-600 px-4 py-2 text-sm font-bold text-white shadow-md shadow-primary-500/20 transition hover:-translate-y-0.5 hover:bg-primary-700 sm:inline-flex"
                        >
                            {{ t('nav.dashboard') }}
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="hidden rounded-xl px-3 py-2 text-sm font-semibold text-gray-600 transition hover:bg-white hover:text-primary-700 sm:inline-flex dark:text-gray-300 dark:hover:bg-gray-800"
                            >
                                {{ t('nav.login') }}
                            </Link>
                            <Link
                                v-if="route().has('register')"
                                :href="route('register')"
                                class="hidden rounded-xl bg-primary-600 px-4 py-2 text-sm font-bold text-white shadow-md shadow-primary-500/20 transition hover:-translate-y-0.5 hover:bg-primary-700 sm:inline-flex"
                            >
                                {{ t('nav.register') }}
                            </Link>
                        </template>
                    </template>

                    <!-- Hamburger (mobile only) -->
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="sm:hidden flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                        :aria-label="t('nav.menu')"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path v-if="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <div v-if="mobileMenuOpen" class="space-y-1 border-t border-gray-100 bg-white/90 px-4 py-3 shadow-lg backdrop-blur-xl sm:hidden dark:border-gray-800 dark:bg-gray-950/90">
                <Link
                    :href="route('explore')"
                    @click="mobileMenuOpen = false"
                    class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    {{ t('nav.explore') || 'Explore' }}
                </Link>
                <Link
                    :href="route('leaderboard')"
                    @click="mobileMenuOpen = false"
                    class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    {{ t('nav.leaderboard') }}
                </Link>
                <a
                    href="https://github.com/azmifauzan/yahoot"
                    target="_blank"
                    rel="noopener noreferrer"
                    @click="mobileMenuOpen = false"
                    class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    GitHub
                </a>
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                    <template v-if="route().has('login')">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            @click="mobileMenuOpen = false"
                            class="block rounded-lg px-3 py-2 text-sm font-semibold text-primary-600 transition hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-900/20"
                        >
                            {{ t('nav.dashboard') }}
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                @click="mobileMenuOpen = false"
                                class="block rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                            >
                                {{ t('nav.login') }}
                            </Link>
                            <Link
                                v-if="route().has('register')"
                                :href="route('register')"
                                @click="mobileMenuOpen = false"
                                class="block rounded-lg px-3 py-2 text-sm font-semibold text-primary-600 transition hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-900/20"
                            >
                                {{ t('nav.register') }}
                            </Link>
                        </template>
                    </template>
                </div>
            </div>
        </nav>

        <main class="flex-grow">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t border-primary-100/70 bg-white/70 py-12 backdrop-blur dark:border-white/10 dark:bg-gray-950/70">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 text-center sm:px-6 md:grid-cols-[1fr_auto] md:items-end md:text-left lg:px-8">
                <div>
                    <div class="mb-4 flex items-center justify-center opacity-80 grayscale transition hover:grayscale-0 md:justify-start">
                    <img src="/images/logo.png?v=3" alt="Yahoot Logo" class="h-8 sm:h-10 w-auto object-contain dark:hidden" />
                    <img src="/images/logo-dark.png?v=1" alt="Yahoot Logo" class="hidden h-8 sm:h-10 w-auto object-contain dark:block" />
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ t('landing.footer') }} &copy; {{ new Date().getFullYear() }} Yahoot.
                    </p>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-sm font-medium text-gray-500 dark:text-gray-400 md:justify-end">
                    <Link :href="route('explore')" class="transition hover:text-primary-600 dark:hover:text-primary-400">{{ t('nav.explore') }}</Link>
                    <Link :href="route('leaderboard')" class="transition hover:text-primary-600 dark:hover:text-primary-400">{{ t('nav.leaderboard') }}</Link>
                    <Link :href="route('terms.show')" class="hover:text-primary-600 dark:hover:text-primary-400 transition">{{ t('auth.terms') || 'Terms of Service' }}</Link>
                    <Link :href="route('policy.show')" class="hover:text-primary-600 dark:hover:text-primary-400 transition">{{ t('auth.privacy') || 'Privacy Policy' }}</Link>
                </div>
            </div>
        </footer>
    </div>
</template>
