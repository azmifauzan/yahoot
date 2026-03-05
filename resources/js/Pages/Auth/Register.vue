<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import LanguageSwitcher from '@/Components/UI/LanguageSwitcher.vue';

const { t } = useI18n();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head :title="t('nav.register')" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">{{ t('nav.register') }}</h2>
            <LanguageSwitcher />
        </div>

        <form @submit.prevent="submit">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.name') }}</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    required
                    autofocus
                    autocomplete="name"
                    :placeholder="t('auth.name_placeholder')"
                />
                <InputError class="mt-1.5" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    required
                    autocomplete="username"
                    placeholder="nama@email.com"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.confirm_password') }}</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <label for="terms" class="flex items-start gap-2">
                    <Checkbox id="terms" v-model:checked="form.terms" name="terms" required class="mt-0.5" />
                    <span class="text-sm text-gray-600">
                        {{ t('auth.agree_to') }}
                        <a target="_blank" :href="route('terms.show')" class="font-medium text-primary-600 hover:text-primary-700">{{ t('auth.terms') }}</a>
                        {{ t('common.and') }}
                        <a target="_blank" :href="route('policy.show')" class="font-medium text-primary-600 hover:text-primary-700">{{ t('auth.privacy') }}</a>
                    </span>
                </label>
                <InputError class="mt-1.5" :message="form.errors.terms" />
            </div>

            <button
                type="submit"
                class="mt-6 w-full rounded-lg bg-primary-500 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ t('nav.register') }}
            </button>

            <p class="mt-4 text-center text-sm text-gray-600">
                {{ t('auth.has_account') }}
                <Link :href="route('login')" class="font-semibold text-primary-600 hover:text-primary-700">
                    {{ t('nav.login') }}
                </Link>
            </p>
        </form>
    </AuthenticationCard>
</template>
