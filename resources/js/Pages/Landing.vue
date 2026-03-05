<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import LanguageSwitcher from '@/Components/UI/LanguageSwitcher.vue';
import ThemeSwitcher from '@/Components/UI/ThemeSwitcher.vue';
import AvatarDisplay from '@/Components/Avatar/AvatarDisplay.vue';

const { t } = useI18n();

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const gameCode = ref('');
const showcaseAvatars = ['fox', 'robot_blue', 'monster_1', 'star', 'panda', 'robot_pink'];

const features = [
    { key: 'free', icon: 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7' },
    { key: 'realtime', icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
    { key: 'easy', icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z' },
    { key: 'multilang', icon: 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9' },
];

const steps = [
    { key: 'step1', icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' },
    { key: 'step2', icon: 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1' },
    { key: 'step3', icon: 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
];

function joinFromLanding() {
    if (gameCode.value.length === 6) {
        router.visit(`/play?code=${gameCode.value}`);
    }
}
</script>

<template>
    <Head :title="t('app.tagline')" />

    <div class="min-h-screen bg-white dark:bg-gray-950">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 border-b border-gray-200 bg-white/80 backdrop-blur-lg dark:border-gray-800 dark:bg-gray-950/80">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-0.5">
                    <svg viewBox="0 0 32 32" class="h-8 w-8" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="32" height="32" rx="8" class="fill-gray-900 dark:fill-white"/>
                        <text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" class="fill-white dark:fill-gray-900" font-size="18" font-weight="bold" font-family="Inter, sans-serif">Y</text>
                    </svg>
                    <span class="text-xl font-bold text-gray-900 dark:text-white">ahoot</span>
                </Link>

                <!-- Right side -->
                <div class="flex items-center gap-3">
                    <!-- GitHub Stars -->
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

                    <template v-if="canLogin">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
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
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
                            >
                                {{ t('nav.register') }}
                            </Link>
                        </template>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative overflow-hidden py-20 sm:py-32">
            <div class="relative mx-auto max-w-6xl px-4 sm:px-6">
                <div class="text-center">
                    <!-- Floating avatars -->
                    <div class="mb-8 flex items-center justify-center gap-3">
                        <div
                            v-for="(avatar, i) in showcaseAvatars"
                            :key="avatar"
                            class="animate-bounce"
                            :style="{ animationDelay: `${i * 0.15}s`, animationDuration: '2s' }"
                        >
                            <AvatarDisplay :name="avatar" :size="48" />
                        </div>
                    </div>

                    <h1 class="mb-6 text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl dark:text-white">
                        {{ t('landing.hero_title') }}
                    </h1>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-500 dark:text-gray-400">
                        {{ t('landing.hero_subtitle') }}
                    </p>

                    <!-- CTA -->
                    <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                        <!-- Join Game Input -->
                        <form @submit.prevent="joinFromLanding" class="flex overflow-hidden rounded-xl border-2 border-gray-200 bg-white shadow-sm transition focus-within:border-gray-400 dark:border-gray-700 dark:bg-gray-900">
                            <input
                                v-model="gameCode"
                                type="text"
                                inputmode="numeric"
                                :placeholder="t('landing.enter_code')"
                                maxlength="6"
                                class="w-40 border-0 bg-transparent px-4 py-3 text-center text-lg font-mono tracking-widest placeholder-gray-400 focus:outline-none focus:ring-0 dark:text-white sm:w-48"
                            />
                            <button
                                type="submit"
                                class="bg-gray-900 px-6 py-3 font-semibold text-white transition hover:bg-gray-800 disabled:opacity-50 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
                                :disabled="gameCode.length < 6"
                            >
                                {{ t('landing.join_game') }}
                            </button>
                        </form>

                        <span class="text-sm text-gray-400 dark:text-gray-500">{{ t('common.or') }}</span>

                        <Link
                            v-if="canLogin"
                            :href="$page.props.auth.user ? '#' : route('register')"
                            class="rounded-xl border-2 border-gray-300 px-8 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            {{ t('landing.create_quiz') }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <!-- <section class="border-t border-gray-100 py-20 dark:border-gray-800">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <h2 class="mb-12 text-center text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                    {{ t('landing.features_title') }}
                </h2>

                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="feature in features"
                        :key="feature.key"
                        class="group rounded-2xl border border-gray-200 bg-white p-6 transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                            <svg class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ t(`landing.feature_${feature.key}`) }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ t(`landing.feature_${feature.key}_desc`) }}
                        </p>
                    </div>
                </div>
            </div>
        </section> -->

        <!-- How to Play -->
        <section class="border-t border-gray-100 bg-gray-50 py-20 dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <h2 class="mb-12 text-center text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                    {{ t('landing.how_title') }}
                </h2>

                <div class="grid gap-8 sm:grid-cols-3">
                    <div
                        v-for="(step, index) in steps"
                        :key="step.key"
                        class="relative text-center"
                    >
                        <!-- Step number -->
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-sm dark:bg-gray-800">
                            <svg class="h-7 w-7 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="step.icon" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ t(`landing.how_${step.key}_title`) }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ t(`landing.how_${step.key}_desc`) }}
                        </p>

                        <!-- Connector arrow -->
                        <div
                            v-if="index < steps.length - 1"
                            class="absolute right-0 top-8 hidden -translate-y-1/2 translate-x-1/2 sm:block"
                        >
                            <svg class="h-5 w-5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="border-t border-gray-100 py-20 dark:border-gray-800">
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
                <h2 class="mb-4 text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                    {{ t('landing.cta_title') }}
                </h2>
                <p class="mb-8 text-lg text-gray-500 dark:text-gray-400">
                    {{ t('landing.cta_subtitle') }}
                </p>
                <Link
                    v-if="canLogin && canRegister"
                    :href="$page.props.auth.user ? '#' : route('register')"
                    class="inline-block rounded-xl bg-gray-900 px-10 py-4 text-lg font-semibold text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
                >
                    {{ t('landing.create_quiz') }} →
                </Link>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-200 py-8 dark:border-gray-800">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    © {{ new Date().getFullYear() }} Yahoot. Free & Open Source.
                </p>
            </div>
        </footer>
    </div>
</template>
