<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import LanguageSwitcher from '@/Components/UI/LanguageSwitcher.vue';
import ThemeSwitcher from '@/Components/UI/ThemeSwitcher.vue';

const { t } = useI18n();

defineProps({
    title: String,
});
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-white dark:bg-gray-950 flex flex-col">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 border-b border-gray-200 bg-white/80 backdrop-blur-lg dark:border-gray-800 dark:bg-gray-950/80">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
                <!-- Logo -->
                <Link href="/" class="group flex items-center">
                    <img src="/images/logo.png?v=4" alt="Yahoot Logo" class="h-8 sm:h-10 w-auto transition group-hover:-rotate-3 object-contain" />
                </Link>

                <!-- Right side -->
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('explore')"
                        class="hidden sm:inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        🔍 {{ t('nav.explore') || 'Explore' }}
                    </Link>
                    <Link
                        :href="route('leaderboard')"
                        class="hidden sm:inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        🏆 {{ t('nav.leaderboard') }}
                    </Link>
                    <a
                        href="https://github.com/azmifauzan/yahoot"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="hidden sm:inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
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
                            class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
                        >
                            {{ t('nav.dashboard') }}
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                            >
                                {{ t('nav.login') }}
                            </Link>
                            <Link
                                v-if="route().has('register')"
                                :href="route('register')"
                                class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700"
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
                    <img src="/images/logo.png?v=4" alt="Yahoot Logo" class="h-8 sm:h-10 w-auto object-contain" />
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
