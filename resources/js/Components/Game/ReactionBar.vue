<script setup>
const props = defineProps({
    emojis: {
        type: Array,
        default: () => ['🎉', '😂', '😮', '😍', '👏', '🔥', '😢', '👍'],
    },
    disabled: { type: Boolean, default: false },
});

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
            class="text-2xl rounded-full bg-white/10 hover:bg-white/25 active:scale-90 transition w-12 h-12 flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed"
            @click="send(emoji)"
        >
            {{ emoji }}
        </button>
    </div>
</template>
