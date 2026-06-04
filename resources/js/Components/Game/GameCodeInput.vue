<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    length: { type: Number, default: 6 },
});

const emit = defineEmits(['update:modelValue', 'complete']);

const digits = ref(Array.from({ length: props.length }, (_, i) => props.modelValue[i] || ''));

watch(() => props.modelValue, (val) => {
    for (let i = 0; i < props.length; i++) {
        digits.value[i] = val[i] || '';
    }
});

function emitValue() {
    const code = digits.value.join('');
    emit('update:modelValue', code);
    if (code.length === props.length) {
        emit('complete', code);
    }
}

function onInput(index, event) {
    const value = event.target.value.replace(/\D/g, '');
    digits.value[index] = value.slice(0, 1);

    if (value && index < props.length - 1) {
        const next = document.querySelector(`#code-${index + 1}`);
        if (next) next.focus();
    }

    emitValue();
}

function onKeydown(index, event) {
    if (event.key === 'Backspace' && !digits.value[index] && index > 0) {
        const prev = document.querySelector(`#code-${index - 1}`);
        if (prev) prev.focus();
    }
}

function onPaste(event) {
    const pasted = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, props.length);
    for (let i = 0; i < props.length; i++) {
        digits.value[i] = pasted[i] || '';
    }
    emitValue();
    event.preventDefault();
}
</script>

<template>
    <div class="flex justify-center gap-2" @paste="onPaste">
        <input
            v-for="(_, index) in length"
            :key="index"
            :id="`code-${index}`"
            type="text"
            inputmode="numeric"
            maxlength="1"
            :value="digits[index]"
            @input="onInput(index, $event)"
            @keydown="onKeydown(index, $event)"
            class="w-12 h-14 text-center text-2xl font-bold rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 outline-none transition-all"
        />
    </div>
</template>
