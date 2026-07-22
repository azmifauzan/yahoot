<script setup>
import Icon from '@/Components/UI/Icon.vue';

const props = defineProps({
    emojis: {
        type: Array,
        default: () => ['celebrate', 'laugh', 'surprised', 'love', 'applause', 'fire', 'sad', 'like'],
    },
    disabled: { type: Boolean, default: false },
});

const reactionIcons = {
    celebrate: 'star',
    laugh: 'smile',
    surprised: 'surprise',
    love: 'heart',
    applause: 'applause',
    fire: 'fire',
    sad: 'sad',
    like: 'like',
};

const emit = defineEmits(['react']);

function send(emoji) {
    if (props.disabled) return;
    emit('react', emoji);
}
</script>

<template>
    <div class="flex flex-wrap items-center justify-center gap-2">
        <button
            v-for="emoji in emojis"
            :key="emoji"
            type="button"
            :disabled="disabled"
            :aria-label="emoji"
            :title="emoji"
            class="group flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-white/10 transition hover:-translate-y-0.5 hover:bg-white/25 active:scale-90 disabled:cursor-not-allowed disabled:opacity-40"
            @click="send(emoji)"
        >
            <Icon :name="reactionIcons[emoji] ?? 'star'" class="h-5 w-5 transition-transform group-hover:scale-110" />
        </button>
    </div>
</template>
