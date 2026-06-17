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

    <div class="min-h-screen bg-white dark:bg-gray-950 flex flex-col">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 border-b border-gray-200 bg-white/80 backdrop-blur-lg dark:border-gray-800 dark:bg-gray-950/80">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
                <!-- Left: Logo + nav links -->
                <div class="flex items-center gap-6">
                    <Link href="/" class="group flex items-center">
                        <img src="/images/logo.png?v=3" alt="Yahoot Logo" class="h-8 sm:h-10 w-auto transition group-hover:-rotate-3 object-contain dark:hidden" />
                        <img src="/images/logo-dark.png?v=1" alt="Yahoot Logo" class="hidden h-8 sm:h-10 w-auto transition group-hover:-rotate-3 object-contain dark:block" />
                    </Link>

                    <!-- Desktop nav links -->
                    <div class="hidden sm:flex items-center gap-1">
                        <Link
                            :href="route('explore')"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            {{ t('nav.explore') || 'Explore' }}
                        </Link>
                        <Link
                            :href="route('leaderboard')"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            {{ t('nav.leaderboard') }}
                        </Link>
                        <a
                            href="https://github.com/azmifauzan/yahoot"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            GitHub
                        </a>
                    </div>
                </div>

                <!-- Right: utilities + auth + hamburger -->
                <div class="flex items-center gap-2">
                    <LanguageSwitcher />
                    <ThemeSwitcher />

                    <template v-if="route().has('login')">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="hidden sm:inline-flex rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
                        >
                            {{ t('nav.dashboard') }}
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="hidden sm:inline-flex rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                            >
                                {{ t('nav.login') }}
                            </Link>
                            <Link
                                v-if="route().has('register')"
                                :href="route('register')"
                                class="hidden sm:inline-flex rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
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
            <div v-if="mobileMenuOpen" class="sm:hidden border-t border-gray-100 dark:border-gray-800 px-4 py-3 space-y-1">
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
        <footer class="border-t border-gray-200 bg-white py-12 dark:border-gray-800 dark:bg-gray-950">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <div class="mb-4 flex items-center justify-center opacity-80 grayscale transition hover:grayscale-0">
                    <img src="/images/logo.png?v=3" alt="Yahoot Logo" class="h-8 sm:h-10 w-auto object-contain dark:hidden" />
                    <img src="/images/logo-dark.png?v=1" alt="Yahoot Logo" class="hidden h-8 sm:h-10 w-auto object-contain dark:block" />
                </div>
                <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                    {{ t('landing.footer') }} &copy; {{ new Date().getFullYear() }} Yahoot.
                </p>
                <div class="flex items-center justify-center gap-4 text-sm text-gray-400 dark:text-gray-500">
                    <Link :href="route('terms.show')" class="hover:text-primary-600 dark:hover:text-primary-400 transition">{{ t('auth.terms') || 'Terms of Service' }}</Link>
                    <span>&middot;</span>
                    <Link :href="route('policy.show')" class="hover:text-primary-600 dark:hover:text-primary-400 transition">{{ t('auth.privacy') || 'Privacy Policy' }}</Link>
                </div>
            </div>
        </footer>
    </div>
</template>
