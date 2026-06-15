<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';
import { usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { useSwal } from '@/Composables/useSwal';
import axios from 'axios';

const { t } = useI18n();
const { toast } = useSwal();
const page = usePage();

const props = defineProps({
    show: Boolean,
    quizId: Number,
});

const emit = defineEmits(['close', 'generated']);

const topic = ref('');
const count = ref(10);
const difficulty = ref('medium');
const type = ref('multiple_choice');
const generating = ref(false);

const userId = page.props.auth.user.id;

onMounted(() => {
    if (window.Echo) {
        window.Echo.private(`App.Models.User.${userId}`)
            .listen('QuestionsGenerated', (e) => {
                if (e.quizId === props.quizId) {
                    generating.value = false;
                    if (e.success) {
                        toast(t('common.success'), 'success');
                        emit('generated', e.questions);
                        emit('close');
                    } else {
                        toast(e.error || t('ai.generation_failed'), 'error');
                    }
                }
            });
    }
});

onBeforeUnmount(() => {
    if (window.Echo) {
        window.Echo.private(`App.Models.User.${userId}`).stopListening('QuestionsGenerated');
    }
});

function generate() {
    if (!topic.value) {
        toast('Please enter a topic', 'warning');
        return;
    }

    generating.value = true;

    axios.post(route('quizzes.ai-generate', props.quizId), {
        topic: topic.value,
        count: count.value,
        difficulty: difficulty.value,
        type: type.value,
    })
    .then((response) => {
        if (response.data.status === 'success') {
            toast(t('ai.success'), 'success');
        } else {
            generating.value = false;
            toast(response.data.message || t('ai.generation_failed'), 'error');
        }
    })
    .catch((error) => {
        generating.value = false;
        const msg = error.response?.data?.message || t('ai.generation_failed');
        toast(msg, 'error');
    });
}
</script>

<template>
    <Modal :show="show" @close="!generating && $emit('close')" max-width="md">
        <div class="p-6">
            <h2 class="font-display text-2xl font-bold text-gray-900 dark:text-white mb-4">
                {{ t('ai.generate_title') }}
            </h2>

            <!-- Loading screen -->
            <div v-if="generating" class="flex flex-col items-center justify-center py-12 text-center">
                <!-- Glowing Pulsing Ring Spinner -->
                <div class="relative flex items-center justify-center mb-6">
                    <div class="absolute h-16 w-16 animate-ping rounded-full bg-primary-400 opacity-20 dark:bg-primary-500"></div>
                    <div class="relative h-14 w-14 rounded-full border-4 border-primary-200 border-t-primary-600 animate-spin dark:border-primary-950 dark:border-t-primary-400"></div>
                </div>
                <h3 class="font-display text-lg font-bold text-gray-900 dark:text-white mb-2">
                    {{ t('common.loading') }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs leading-relaxed animate-pulse">
                    {{ t('ai.generating') }}
                </p>
            </div>

            <!-- Form -->
            <form v-else @submit.prevent="generate" class="space-y-4">
                <!-- Topic -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        {{ t('ai.topic_label') }}
                    </label>
                    <input
                        v-model="topic"
                        type="text"
                        required
                        :placeholder="t('ai.topic_placeholder')"
                        class="w-full rounded-xl border-gray-200 py-2.5 px-4 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:placeholder-gray-500"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Count -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ t('ai.count_label') }}
                        </label>
                        <select
                            v-model="count"
                            class="w-full rounded-xl border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        >
                            <option :value="5">5</option>
                            <option :value="10">10</option>
                            <option :value="15">15</option>
                            <option :value="20">20</option>
                        </select>
                    </div>

                    <!-- Difficulty -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ t('ai.difficulty_label') }}
                        </label>
                        <select
                            v-model="difficulty"
                            class="w-full rounded-xl border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                        >
                            <option value="easy">{{ t('ai.difficulty_easy') }}</option>
                            <option value="medium">{{ t('ai.difficulty_medium') }}</option>
                            <option value="hard">{{ t('ai.difficulty_hard') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        {{ t('ai.type_label') }}
                    </label>
                    <select
                        v-model="type"
                        class="w-full rounded-xl border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                        <option value="multiple_choice">{{ t('ai.type_multiple_choice') }}</option>
                        <option value="true_false">{{ t('ai.type_true_false') }}</option>
                        <option value="all">{{ t('ai.type_all') }}</option>
                    </select>
                </div>

                <!-- Disclaimer -->
                <p class="text-xs text-gray-400 dark:text-gray-500 leading-normal bg-gray-50 dark:bg-gray-950 p-3 rounded-lg border border-gray-100 dark:border-gray-850">
                    ⚠️ {{ t('ai.disclaimer') }}
                </p>

                <!-- Actions -->
                <div class="mt-6 flex justify-end gap-3 border-t border-gray-50 pt-4 dark:border-gray-850">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-850 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        {{ t('common.cancel') }}
                    </button>
                    <button
                        type="submit"
                        class="rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-primary-700 shadow-md shadow-primary-500/10"
                    >
                        {{ t('ai.generate_btn') }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
