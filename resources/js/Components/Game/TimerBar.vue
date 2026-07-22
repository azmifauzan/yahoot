<script setup>
import { computed } from 'vue';

const props = defineProps({
    progress: { type: Number, required: true },
    timeRemaining: { type: Number, default: null },
    showNumber: { type: Boolean, default: false },
    rounded: { type: Boolean, default: false },
});

const timerColor = computed(() => {
    if (props.progress > 50) return 'bg-green-500';
    if (props.progress > 25) return 'bg-yellow-500';
    return 'bg-red-500';
});
</script>

<template>
    <div>
        <div v-if="showNumber" class="mb-3 text-5xl font-black tabular-nums text-white">
            {{ Math.ceil(timeRemaining) }}
        </div>
        <div class="h-2.5 bg-white/10" :class="rounded ? 'w-full rounded-full overflow-hidden' : ''">
            <div
                :class="[timerColor, rounded ? 'rounded-full' : '']"
                class="h-full shadow-[0_0_18px_currentColor] transition-all duration-100 ease-linear"
                :style="{ width: progress + '%' }"
            ></div>
        </div>
    </div>
</template>
