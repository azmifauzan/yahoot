<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/Components/UI/Icon.vue';

const props = defineProps({
    available: { type: Array, default: () => [] }, // remaining power-up keys
    used: { type: String, default: null }, // power-up already used this question
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['use']);

const { t } = useI18n();

const ALL = [
    { key: 'double_points', icon: 'bolt' },
    { key: 'fifty_fifty', icon: 'scissors' },
    { key: 'freeze_timer', icon: 'snowflake' },
];

const items = computed(() =>
    ALL.map((p) => ({
        ...p,
        owned: props.available.includes(p.key),
    }))
);

function use(key) {
    if (props.disabled || props.used) return;
    emit('use', key);
}
</script>

<template>
    <div class="flex items-center justify-center gap-2 sm:gap-3">
        <button
            v-for="p in items"
            :key="p.key"
            type="button"
            :disabled="disabled || !p.owned || !!used"
            class="group relative flex min-w-20 flex-col items-center gap-1.5 rounded-2xl border border-white/10 bg-white/10 px-3 py-2.5 backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/20 active:scale-95 disabled:cursor-not-allowed disabled:opacity-30"
            :class="{ 'ring-2 ring-amber-300': used === p.key }"
            :title="t(`powerups.${p.key}`)"
            @click="use(p.key)"
        >
            <Icon :name="p.icon" class="h-5 w-5 transition-transform group-hover:scale-110" />
            <span class="text-[10px] font-semibold text-white/90">{{ t(`powerups.${p.key}`) }}</span>
        </button>
    </div>
</template>
