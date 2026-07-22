<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/Components/UI/Icon.vue';

const { t } = useI18n();

const props = defineProps({
    score: { type: Number, required: true },
    correctCount: { type: Number, required: true },
    totalQuestions: { type: Number, required: true },
    avgTimeMs: { type: Number, default: 0 },
});

defineEmits(['retry', 'home']);

const accuracy = computed(() =>
    props.totalQuestions > 0 ? Math.round((props.correctCount / props.totalQuestions) * 100) : 0
);

const avgTimeSeconds = computed(() => (props.avgTimeMs / 1000).toFixed(1));
</script>

<template>
    <div class="mx-auto max-w-sm p-8 text-center text-white">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-white/15 shadow-xl animate-pop-bounce">
            <Icon name="flag" class="h-8 w-8" />
        </div>
        <h2 class="text-3xl font-extrabold mb-6 animate-slide-in-up">{{ t('practice.summary') }}</h2>

        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur animate-slide-in-up">
                <div class="text-3xl font-extrabold">{{ score }}</div>
                <div class="text-sm text-white/70">{{ t('practice.your_score') }}</div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur animate-slide-in-up" style="animation-delay: 0.1s">
                <div class="text-3xl font-extrabold">{{ accuracy }}%</div>
                <div class="text-sm text-white/70">{{ t('practice.accuracy') }}</div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur animate-slide-in-up" style="animation-delay: 0.2s">
                <div class="text-3xl font-extrabold">{{ correctCount }}/{{ totalQuestions }}</div>
                <div class="text-sm text-white/70">{{ t('practice.correct') }}</div>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur animate-slide-in-up" style="animation-delay: 0.3s">
                <div class="text-3xl font-extrabold">{{ avgTimeSeconds }}s</div>
                <div class="text-sm text-white/70">{{ t('practice.avg_time') }}</div>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <button
                @click="$emit('retry')"
                class="rounded-xl bg-white px-8 py-3 font-bold text-primary-600 transition-all hover:-translate-y-0.5 hover:bg-gray-100 active:scale-95"
            >
                {{ t('practice.try_again') }}
            </button>
            <button
                @click="$emit('home')"
                class="px-8 py-3 bg-white/20 text-white font-bold rounded-xl hover:bg-white/30 transition-all"
            >
                {{ t('practice.back_home') }}
            </button>
        </div>
    </div>
</template>
