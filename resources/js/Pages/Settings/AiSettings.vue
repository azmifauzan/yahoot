<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useSwal } from '@/Composables/useSwal';

const { t } = useI18n();
const { toast } = useSwal();

const props = defineProps({
    llmSetting: Object,
    providerDefaults: Object,
});

const form = useForm({
    provider: props.llmSetting.provider,
    model: props.llmSetting.model,
    base_url: props.llmSetting.base_url,
    api_key: '',
});

const hasApiKey = ref(props.llmSetting.has_api_key);
const changingApiKey = ref(!hasApiKey.value);

function onProviderChange() {
    const defaults = props.providerDefaults[form.provider];
    if (!form.model) {
        form.model = defaults.model;
    }
}

function save() {
    form.put(route('settings.ai.update'), {
        onSuccess: () => {
            toast(t('ai.saved'));
            if (form.api_key) {
                hasApiKey.value = true;
                changingApiKey.value = false;
            }
            form.api_key = '';
        },
    });
}
</script>

<template>
    <AppLayout :title="t('ai.title')">
        <template #header>
            <h2 class="font-display text-xl font-bold text-gray-800 dark:text-gray-100">{{ t('ai.title') }}</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="save" class="space-y-6">
                    <div class="rounded-xl bg-white shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800 p-6 space-y-5">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('ai.description') }}</p>

                        <!-- Provider -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('ai.provider') }}</label>
                            <select
                                v-model="form.provider"
                                @change="onProviderChange"
                                class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            >
                                <option value="openai">{{ t('ai.provider_openai') }}</option>
                                <option value="anthropic">{{ t('ai.provider_anthropic') }}</option>
                            </select>
                            <p v-if="form.errors.provider" class="mt-1 text-sm text-red-600">{{ form.errors.provider }}</p>
                        </div>

                        <!-- Model -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('ai.model') }}</label>
                            <input
                                v-model="form.model"
                                type="text"
                                :placeholder="t('ai.model_placeholder')"
                                class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            />
                            <p v-if="form.errors.model" class="mt-1 text-sm text-red-600">{{ form.errors.model }}</p>
                        </div>

                        <!-- Base URL -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('ai.base_url') }}</label>
                            <input
                                v-model="form.base_url"
                                type="text"
                                :placeholder="t('ai.base_url_placeholder', { url: providerDefaults[form.provider].base_url })"
                                class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            />
                            <p v-if="form.errors.base_url" class="mt-1 text-sm text-red-600">{{ form.errors.base_url }}</p>
                        </div>

                        <!-- API Key -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('ai.api_key') }}</label>

                            <div v-if="hasApiKey && !changingApiKey" class="flex items-center justify-between rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm">
                                <span class="text-gray-600 dark:text-gray-300">✓ {{ t('ai.api_key_configured') }}</span>
                                <button type="button" @click="changingApiKey = true" class="text-primary-600 hover:underline">
                                    {{ t('ai.api_key_change') }}
                                </button>
                            </div>
                            <input
                                v-else
                                v-model="form.api_key"
                                type="password"
                                autocomplete="off"
                                :placeholder="t('ai.api_key_placeholder')"
                                class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                            />
                            <p class="mt-1 text-xs text-gray-400">{{ t('ai.api_key_hint') }}</p>
                            <p v-if="form.errors.api_key" class="mt-1 text-sm text-red-600">{{ form.errors.api_key }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 disabled:opacity-50 transition"
                        >
                            {{ t('common.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
