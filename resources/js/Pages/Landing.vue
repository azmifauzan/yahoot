<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import LanguageSwitcher from '@/Components/UI/LanguageSwitcher.vue';
import ThemeSwitcher from '@/Components/UI/ThemeSwitcher.vue';
import AvatarDisplay from '@/Components/Avatar/AvatarDisplay.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

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
    { key: 'free', color: '#A8E6CF', icon: 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7' },
    { key: 'realtime', color: '#FFE66D', icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
    { key: 'easy', color: '#FF6B6B', icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z' },
    { key: 'multilang', color: '#4ECDC4', icon: 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9' },
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
    <GuestLayout :title="t('app.tagline')">


        <!-- Hero Section -->
        <section class="relative overflow-hidden py-20 sm:py-32">
            <!-- Decorative gradient blobs -->
            <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
                <div class="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-primary-400/30 blur-3xl dark:bg-primary-600/20"></div>
                <div class="absolute -right-16 top-12 h-80 w-80 rounded-full bg-accent-blue/20 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-accent-yellow/20 blur-3xl"></div>
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

                    <h1 class="mb-6 font-display text-5xl font-extrabold tracking-tight text-gray-900 sm:text-7xl dark:text-white">
                        {{ t('landing.hero_title') }}
                    </h1>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-500 dark:text-gray-400">
                        {{ t('landing.hero_subtitle') }}
                    </p>

                    <!-- CTA -->
                    <div class="flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                        <!-- Join Game Input (glass) -->
                        <form @submit.prevent="joinFromLanding" class="flex overflow-hidden rounded-2xl border-2 border-primary-200 bg-white/70 shadow-lg shadow-primary-500/10 backdrop-blur transition focus-within:border-primary-400 dark:border-primary-900/60 dark:bg-gray-900/70">
                            <input
                                v-model="gameCode"
                                type="text"
                                inputmode="numeric"
                                :placeholder="t('landing.enter_code')"
                                maxlength="6"
                                class="w-40 border-0 bg-transparent px-4 py-3.5 text-center font-mono text-lg tracking-widest placeholder-gray-400 focus:outline-none focus:ring-0 dark:text-white sm:w-48"
                            />
                            <button
                                type="submit"
                                class="bg-primary-600 px-6 py-3.5 font-semibold text-white transition hover:bg-primary-700 disabled:opacity-50"
                                :disabled="gameCode.length < 6"
                            >
                                {{ t('landing.join_game') }}
                            </button>
                        </form>

                        <span class="text-sm text-gray-400 dark:text-gray-500">{{ t('common.or') }}</span>

                        <Link
                            v-if="canLogin"
                            :href="$page.props.auth.user ? '#' : route('register')"
                            class="rounded-2xl border-2 border-primary-200 px-8 py-3.5 font-semibold text-primary-700 transition hover:bg-primary-50 dark:border-primary-900/60 dark:text-primary-300 dark:hover:bg-primary-950/40"
                        >
                            {{ t('landing.create_quiz') }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="border-t border-gray-100 py-20 dark:border-gray-800">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <h2 class="mb-12 text-center font-display text-3xl font-extrabold text-gray-900 sm:text-4xl dark:text-white">
                    {{ t('landing.features_title') }}
                </h2>

                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="feature in features"
                        :key="feature.key"
                        class="group rounded-2xl border border-gray-200 bg-white p-6 transition hover:-translate-y-1 hover:shadow-lg hover:shadow-primary-500/10 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div
                            class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl shadow-sm transition group-hover:scale-110"
                            :style="{ backgroundColor: feature.color }"
                        >
                            <svg class="h-5 w-5 text-gray-900/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
                            </svg>
                        </div>
                        <h3 class="mb-2 font-display text-lg font-bold text-gray-900 dark:text-white">
                            {{ t(`landing.feature_${feature.key}`) }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ t(`landing.feature_${feature.key}_desc`) }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How to Play -->
        <section class="border-t border-gray-100 bg-gray-50 py-20 dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <h2 class="mb-12 text-center font-display text-3xl font-extrabold text-gray-900 sm:text-4xl dark:text-white">
                    {{ t('landing.how_title') }}
                </h2>

                <div class="grid gap-8 sm:grid-cols-3">
                    <div
                        v-for="(step, index) in steps"
                        :key="step.key"
                        class="relative text-center"
                    >
                        <!-- Step number -->
                        <div class="relative mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 shadow-lg shadow-primary-500/30">
                            <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="step.icon" />
                            </svg>
                            <span class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-accent-yellow font-display text-sm font-extrabold text-gray-900 shadow">{{ index + 1 }}</span>
                        </div>
                        <h3 class="mb-2 font-display text-lg font-bold text-gray-900 dark:text-white">
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
        <section class="relative overflow-hidden border-t border-gray-100 py-20 dark:border-gray-800">
            <div class="pointer-events-none absolute inset-0 -z-10">
                <div class="absolute left-1/2 top-1/2 h-64 w-[36rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-primary-400/20 blur-3xl dark:bg-primary-600/15"></div>
            </div>
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
                <h2 class="mb-4 font-display text-3xl font-extrabold text-gray-900 sm:text-4xl dark:text-white">
                    {{ t('landing.cta_title') }}
                </h2>
                <p class="mb-8 text-lg text-gray-500 dark:text-gray-400">
                    {{ t('landing.cta_subtitle') }}
                </p>
                <Link
                    v-if="canLogin && canRegister"
                    :href="$page.props.auth.user ? '#' : route('register')"
                    class="inline-block rounded-2xl bg-primary-600 px-10 py-4 text-lg font-semibold text-white shadow-lg shadow-primary-500/30 transition hover:-translate-y-0.5 hover:bg-primary-700"
                >
                    {{ t('landing.create_quiz') }} →
                </Link>
            </div>
        </section>
    </GuestLayout>
</template>
