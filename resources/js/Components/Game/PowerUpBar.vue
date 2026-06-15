<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    available: { type: Array, default: () => [] }, // remaining power-up keys
    used: { type: String, default: null }, // power-up already used this question
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['use']);

const { t } = useI18n();

const ALL = [
    { key: 'double_points', icon: '2️⃣' },
    { key: 'fifty_fifty', icon: '✂️' },
    { key: 'freeze_timer', icon: '❄️' },
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
    <div class="flex items-center justify-center gap-3">
        <button
            v-for="p in items"
            :key="p.key"
            type="button"
            :disabled="disabled || !p.owned || !!used"
            class="relative flex flex-col items-center gap-1 px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 active:scale-95 transition disabled:opacity-30 disabled:cursor-not-allowed"
            :class="{ 'ring-2 ring-yellow-300': used === p.key }"
            :title="t(`powerups.${p.key}`)"
            @click="use(p.key)"
        >
            <span class="text-2xl">{{ p.icon }}</span>
            <span class="text-[10px] font-semibold text-white/90">{{ t(`powerups.${p.key}`) }}</span>
        </button>
    </div>
</template>
