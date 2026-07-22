<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/Components/UI/Icon.vue';

const props = defineProps({
    streak: { type: Number, default: 0 },
    min: { type: Number, default: 1 },
});

const { t } = useI18n();

const show = computed(() => props.streak > props.min);
</script>

<template>
    <p v-if="show" class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-base font-bold animate-slide-in-up" style="animation-delay: 0.5s">
        <Icon name="fire" class="h-5 w-5" />
        {{ streak }} {{ t('play.streak') }}
    </p>
</template>
