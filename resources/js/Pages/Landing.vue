<script setup>
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
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


        <section class="relative overflow-hidden pb-20 pt-14 sm:pb-28 sm:pt-20">
            <div class="pointer-events-none absolute -left-32 top-16 h-80 w-80 rounded-full bg-primary-300/30 blur-3xl dark:bg-primary-700/20"></div>
            <div class="pointer-events-none absolute -right-24 bottom-16 h-72 w-72 rounded-full bg-accent-blue/25 blur-3xl dark:bg-accent-blue/10"></div>

            <div class="relative mx-auto grid max-w-7xl items-center gap-14 px-4 sm:px-6 lg:grid-cols-[1.04fr_.96fr] lg:px-8">
                <div class="text-center lg:text-left">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-primary-200 bg-white/80 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.16em] text-primary-700 shadow-sm backdrop-blur dark:border-primary-800 dark:bg-gray-900/80 dark:text-primary-300">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent-blue opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-accent-blue"></span>
                        </span>
                        {{ t('landing.feature_realtime') }}
                    </div>

                    <h1 class="font-display text-5xl font-black leading-[.95] tracking-[-0.04em] text-gray-950 sm:text-7xl dark:text-white">
                        {{ t('landing.hero_title') }}
                    </h1>
                    <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-gray-600 lg:mx-0 dark:text-gray-300">
                        {{ t('landing.hero_subtitle') }}
                    </p>

                    <form @submit.prevent="joinFromLanding" class="mx-auto mt-9 flex max-w-lg flex-col gap-3 rounded-[1.35rem] border border-white bg-white/85 p-2 shadow-2xl shadow-primary-900/10 backdrop-blur sm:flex-row lg:mx-0 dark:border-white/10 dark:bg-gray-900/85">
                        <label for="landing-code" class="sr-only">{{ t('landing.enter_code') }}</label>
                        <input
                            id="landing-code"
                            v-model="gameCode"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            :placeholder="t('landing.enter_code')"
                            maxlength="6"
                            class="min-w-0 flex-1 rounded-2xl border-0 bg-gray-50 px-5 py-3.5 text-center font-mono text-lg font-bold tracking-[.18em] text-gray-900 placeholder:font-sans placeholder:text-sm placeholder:font-medium placeholder:tracking-normal focus:ring-2 focus:ring-primary-400 dark:bg-gray-800 dark:text-white sm:text-left"
                        />
                        <button
                            type="submit"
                            class="rounded-2xl bg-primary-600 px-6 py-3.5 font-bold text-white shadow-lg shadow-primary-600/25 transition hover:-translate-y-0.5 hover:bg-primary-700 disabled:translate-y-0 disabled:cursor-not-allowed disabled:opacity-40"
                            :disabled="gameCode.length !== 6"
                        >
                            {{ t('landing.join_game') }} →
                        </button>
                    </form>

                    <div class="mt-5 flex flex-wrap items-center justify-center gap-x-5 gap-y-3 text-sm lg:justify-start">
                        <Link
                            v-if="$page.props.auth.user || canRegister"
                            :href="$page.props.auth.user ? route('dashboard') : route('register')"
                            class="font-bold text-primary-700 underline decoration-primary-300 decoration-2 underline-offset-4 transition hover:text-primary-900 dark:text-primary-300 dark:hover:text-white"
                        >
                            {{ t('landing.create_quiz') }} →
                        </Link>
                        <Link :href="route('explore')" class="font-semibold text-gray-500 transition hover:text-primary-700 dark:text-gray-400 dark:hover:text-primary-300">
                            {{ t('nav.explore') }}
                        </Link>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-xl lg:mx-0">
                    <div class="animate-drift absolute -left-7 top-20 z-20 hidden rotate-[-8deg] rounded-2xl border border-white/80 bg-white/90 p-3 shadow-xl backdrop-blur sm:block dark:border-white/10 dark:bg-gray-900/90">
                        <AvatarDisplay name="fox" :size="52" />
                        <span class="mt-1 block text-center text-xs font-bold text-gray-600 dark:text-gray-300">+850</span>
                    </div>
                    <div class="animate-drift absolute -right-5 bottom-16 z-20 hidden rotate-6 rounded-2xl border border-white/80 bg-white/90 p-3 shadow-xl backdrop-blur sm:block dark:border-white/10 dark:bg-gray-900/90" style="animation-delay: -2s">
                        <AvatarDisplay name="robot_pink" :size="52" />
                        <span class="mt-1 block text-center text-xs font-bold text-gray-600 dark:text-gray-300">🔥 3</span>
                    </div>

                    <div class="relative rotate-1 overflow-hidden rounded-[2rem] border border-white/80 bg-gray-950 p-3 shadow-2xl shadow-primary-900/25 transition duration-500 hover:rotate-0 hover:scale-[1.01] dark:border-white/10">
                        <div class="rounded-[1.4rem] bg-gradient-to-br from-primary-700 via-primary-600 to-[#dc568b] p-5 sm:p-7">
                            <div class="mb-5 flex items-center justify-between text-xs font-bold text-white/80">
                                <span class="rounded-full bg-white/15 px-3 py-1.5 backdrop-blur">02 / 10</span>
                                <span class="flex items-center gap-2"><span class="h-2 w-2 animate-pulse rounded-full bg-accent-yellow"></span> LIVE</span>
                                <span class="rounded-full bg-gray-950/25 px-3 py-1.5">18s</span>
                            </div>
                            <div class="rounded-2xl bg-white p-5 text-center shadow-lg sm:p-7 dark:bg-gray-950">
                                <p class="text-xs font-bold uppercase tracking-[.16em] text-primary-500">{{ t('quiz.question_number', { number: 2 }) }}</p>
                                <p class="mt-3 font-display text-xl font-extrabold text-gray-900 sm:text-2xl dark:text-white">{{ t('landing.preview_question') }}</p>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-3 text-left text-sm font-bold text-white sm:text-base">
                                <div class="rounded-2xl bg-accent-red p-4 shadow-lg transition hover:-translate-y-1 hover:rotate-1">▲ {{ t('landing.preview_answer_1') }}</div>
                                <div class="rounded-2xl bg-accent-blue p-4 text-gray-950 shadow-lg transition hover:-translate-y-1 hover:-rotate-1">◆ {{ t('landing.preview_answer_2') }}</div>
                                <div class="rounded-2xl bg-primary-900/70 p-4 shadow-lg transition hover:-translate-y-1 hover:-rotate-1">● {{ t('landing.preview_answer_3') }}</div>
                                <div class="rounded-2xl bg-[#e5b63f] p-4 text-gray-950 shadow-lg transition hover:-translate-y-1 hover:rotate-1">■ {{ t('landing.preview_answer_4') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="mb-12 text-center font-display text-4xl font-black tracking-tight text-gray-950 sm:text-5xl dark:text-white">
                    {{ t('landing.features_title') }}
                </h2>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="feature in features"
                        :key="feature.key"
                        class="group rounded-[1.6rem] border border-white bg-white/80 p-6 shadow-lg shadow-primary-900/5 backdrop-blur transition duration-300 hover:-translate-y-2 hover:rotate-1 hover:shadow-xl dark:border-white/10 dark:bg-gray-900/80"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl shadow-sm transition duration-300 group-hover:-rotate-6 group-hover:scale-110"
                            :style="{ backgroundColor: feature.color }"
                        >
                            <svg class="h-5 w-5 text-gray-900/80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
                            </svg>
                        </div>
                        <h3 class="mb-2 font-display text-xl font-extrabold text-gray-900 dark:text-white">
                            {{ t(`landing.feature_${feature.key}`) }}
                        </h3>
                        <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">
                            {{ t(`landing.feature_${feature.key}_desc`) }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How to Play -->
        <section class="relative overflow-hidden bg-gray-950 py-20 text-white sm:py-24">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-primary-950/80 via-transparent to-accent-blue/10"></div>
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="mb-14 text-center font-display text-4xl font-black tracking-tight sm:text-5xl">
                    {{ t('landing.how_title') }}
                </h2>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div
                        v-for="(step, index) in steps"
                        :key="step.key"
                        class="group relative rounded-[1.6rem] border border-white/10 bg-white/5 p-7 text-left backdrop-blur transition hover:-translate-y-1 hover:bg-white/10"
                    >
                        <!-- Step number -->
                        <div class="relative mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 shadow-lg shadow-primary-500/30 transition group-hover:-rotate-6 group-hover:scale-110">
                            <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="step.icon" />
                            </svg>
                            <span class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-accent-yellow font-display text-sm font-extrabold text-gray-900 shadow">{{ index + 1 }}</span>
                        </div>
                        <h3 class="mb-2 font-display text-xl font-extrabold text-white">
                            {{ t(`landing.how_${step.key}_title`) }}
                        </h3>
                        <p class="text-sm leading-6 text-gray-400">
                            {{ t(`landing.how_${step.key}_desc`) }}
                        </p>

                        <!-- Connector arrow -->
                        <div
                            v-if="index < steps.length - 1"
                            class="absolute -right-3 top-1/2 z-10 hidden -translate-y-1/2 sm:block"
                        >
                            <svg class="h-6 w-6 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="px-4 py-20 sm:px-6 sm:py-24">
            <div class="relative mx-auto max-w-6xl overflow-hidden rounded-[2rem] bg-gradient-to-br from-primary-600 via-primary-700 to-[#d6518a] px-6 py-14 text-center shadow-2xl shadow-primary-900/25 sm:px-12 sm:py-16">
                <div class="pointer-events-none absolute -left-12 -top-12 h-44 w-44 rounded-full border-[28px] border-white/10"></div>
                <div class="pointer-events-none absolute -bottom-20 -right-12 h-56 w-56 rounded-full border-[34px] border-accent-yellow/20"></div>
                <div class="relative">
                <h2 class="mb-4 font-display text-4xl font-black tracking-tight text-white sm:text-5xl">
                    {{ t('landing.cta_title') }}
                </h2>
                <p class="mb-8 text-lg text-white/75">
                    {{ t('landing.cta_subtitle') }}
                </p>
                <Link
                    v-if="$page.props.auth.user || (canLogin && canRegister)"
                    :href="$page.props.auth.user ? route('dashboard') : route('register')"
                    class="inline-block rounded-2xl bg-white px-10 py-4 text-lg font-extrabold text-primary-700 shadow-xl transition hover:-translate-y-1 hover:shadow-2xl"
                >
                    {{ t('landing.create_quiz') }} →
                </Link>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>
