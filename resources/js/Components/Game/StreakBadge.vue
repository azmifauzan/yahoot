<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    streak: { type: Number, default: 0 },
    min: { type: Number, default: 1 },
});

const { t } = useI18n();

const show = computed(() => props.streak > props.min);
</script>

<template>
    <p v-if="show" class="mt-2 text-lg animate-slide-in-up" style="animation-delay: 0.5s">
        🔥 {{ streak }} {{ t('play.streak') }}
    </p>
</template>
