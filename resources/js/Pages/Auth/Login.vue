<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import GoogleAuthButton from '@/Components/UI/GoogleAuthButton.vue';
import LanguageSwitcher from '@/Components/UI/LanguageSwitcher.vue';
import ThemeSwitcher from '@/Components/UI/ThemeSwitcher.vue';

const { t } = useI18n();

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const fillDemo = () => {
    form.email = 'demo@yahoot.app';
    form.password = 'password';
    form.clearErrors();
};
</script>

<template>
    <Head :title="t('nav.login')" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ t('nav.login') }}</h2>
            <div class="flex items-center gap-2">
                <LanguageSwitcher />
                <ThemeSwitcher />
            </div>
        </div>

        <div v-if="status" class="mb-4 rounded-lg bg-green-50 p-3 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <button
            type="button"
            @click="fillDemo"
            class="mb-4 flex w-full items-start gap-3 rounded-lg border border-dashed border-primary-300 bg-primary-50 p-3 text-left transition hover:bg-primary-100 dark:border-primary-700 dark:bg-primary-900/20 dark:hover:bg-primary-900/40"
        >
            <span class="text-lg leading-none">✨</span>
            <span class="flex-1">
                <span class="block text-sm font-semibold text-primary-700 dark:text-primary-300">{{ t('auth.demo_title') }}</span>
                <span class="block text-xs text-primary-600/80 dark:text-primary-400/80">{{ t('auth.demo_hint') }}</span>
                <span class="mt-1 block font-mono text-xs text-gray-500 dark:text-gray-400">demo@yahoot.app · password</span>
            </span>
        </button>

        <GoogleAuthButton />

        <div class="my-6 flex items-center gap-3">
            <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
            <span class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">{{ t('auth.or') }}</span>
            <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
        </div>

        <form @submit.prevent="submit">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:focus:border-primary-400 dark:focus:ring-primary-400"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nama@email.com"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300">Password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:focus:border-primary-400 dark:focus:ring-primary-400"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-300">{{ t('auth.remember_me') }}</span>
                </label>
                <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-gray-700 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400 font-medium">
                    {{ t('auth.forgot_password') }}
                </Link>
            </div>

            <button
                type="submit"
                class="mt-6 w-full rounded-lg bg-primary-600 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ t('nav.login') }}
            </button>

            <p class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
                {{ t('auth.no_account') }}
                <Link :href="route('register')" class="font-semibold text-gray-700 hover:text-primary-600 dark:text-gray-300 dark:hover:text-primary-400">
                    {{ t('nav.register') }}
                </Link>
            </p>
        </form>


    </AuthenticationCard>
</template>
