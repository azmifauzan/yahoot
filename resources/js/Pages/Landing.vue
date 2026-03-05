<script setup>
import { Head, Link } from '@inertiajs/vue3';
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
    { key: 'free', icon: '🎉' },
    { key: 'realtime', icon: '⚡' },
    { key: 'easy', icon: '✨' },
    { key: 'multilang', icon: '🌍' },
];

const steps = [
    { key: 'step1', icon: '📝', color: 'bg-accent-red/10 text-accent-red' },
    { key: 'step2', icon: '🔗', color: 'bg-accent-blue/10 text-accent-blue' },
    { key: 'step3', icon: '🎮', color: 'bg-accent-green/10 text-accent-green' },
];
</script>

<template>
    <Head :title="t('app.tagline')" />

    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-white to-primary-50 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 border-b border-gray-100 bg-white/80 backdrop-blur-lg dark:border-gray-800 dark:bg-gray-900/80">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-2">
                    <span class="text-2xl font-bold text-primary-500">Y</span>
                    <span class="text-xl font-bold text-gray-900 dark:text-white">ahoot</span>
                </Link>

                <!-- Right side -->
                <div class="flex items-center gap-3">
                    <LanguageSwitcher />
                    <ThemeSwitcher />

                    <template v-if="canLogin">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-600"
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
                                class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-600"
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
            <!-- Background decoration -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-primary-200/30 blur-3xl" />
                <div class="absolute -bottom-20 -left-20 h-72 w-72 rounded-full bg-accent-yellow/20 blur-3xl" />
            </div>

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
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-600 dark:text-gray-400">
                        {{ t('landing.hero_subtitle') }}
                    </p>

                    <!-- CTA -->
                    <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                        <!-- Join Game Input -->
                        <div class="flex overflow-hidden rounded-xl border-2 border-primary-200 bg-white shadow-lg transition focus-within:border-primary-500 dark:border-gray-700 dark:bg-gray-800">
                            <input
                                v-model="gameCode"
                                type="text"
                                :placeholder="t('landing.enter_code')"
                                maxlength="6"
                                class="w-40 border-0 bg-transparent px-4 py-3 text-center text-lg font-mono tracking-widest placeholder-gray-400 focus:outline-none focus:ring-0 dark:text-white sm:w-48"
                            />
                            <button
                                class="bg-primary-500 px-6 py-3 font-semibold text-white transition hover:bg-primary-600 disabled:opacity-50"
                                :disabled="gameCode.length < 6"
                            >
                                {{ t('landing.join_game') }}
                            </button>
                        </div>

                        <span class="text-sm text-gray-400 dark:text-gray-500">{{ t('common.or') }}</span>

                        <Link
                            v-if="canLogin"
                            :href="$page.props.auth.user ? '#' : route('register')"
                            class="rounded-xl border-2 border-primary-500 bg-primary-50 px-8 py-3 font-semibold text-primary-600 transition hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/40"
                        >
                            {{ t('landing.create_quiz') }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <h2 class="mb-12 text-center text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                    {{ t('landing.features_title') }}
                </h2>

                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="feature in features"
                        :key="feature.key"
                        class="group rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div class="mb-4 text-4xl">{{ feature.icon }}</div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ t(`landing.feature_${feature.key}`) }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ t(`landing.feature_${feature.key}_desc`) }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How to Play -->
        <section class="bg-primary-500/5 py-20 dark:bg-primary-900/10">
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
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl text-3xl"
                            :class="step.color"
                        >
                            {{ step.icon }}
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ t(`landing.how_${step.key}_title`) }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ t(`landing.how_${step.key}_desc`) }}
                        </p>

                        <!-- Connector arrow -->
                        <div
                            v-if="index < steps.length - 1"
                            class="absolute right-0 top-8 hidden -translate-y-1/2 translate-x-1/2 text-2xl text-gray-300 sm:block dark:text-gray-600"
                        >
                            →
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20">
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
                <h2 class="mb-4 text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                    {{ t('landing.cta_title') }}
                </h2>
                <p class="mb-8 text-lg text-gray-600 dark:text-gray-400">
                    {{ t('landing.cta_subtitle') }}
                </p>
                <Link
                    v-if="canLogin && canRegister"
                    :href="$page.props.auth.user ? '#' : route('register')"
                    class="inline-block rounded-xl bg-primary-500 px-10 py-4 text-lg font-semibold text-white shadow-lg shadow-primary-500/30 transition hover:bg-primary-600 hover:shadow-xl hover:shadow-primary-500/40"
                >
                    {{ t('landing.create_quiz') }} →
                </Link>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-100 py-8 dark:border-gray-800">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    © {{ new Date().getFullYear() }} Yahoot. Free & Open Source.
                </p>
            </div>
        </footer>
    </div>
</template>
