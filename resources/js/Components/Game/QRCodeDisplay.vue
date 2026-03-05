<script setup>
import { ref, watch, onMounted } from 'vue';
import QRCode from 'qrcode';

const props = defineProps({
    value: {
        type: String,
        required: true,
    },
    size: {
        type: Number,
        default: 200,
    },
    darkColor: {
        type: String,
        default: '#000000',
    },
    lightColor: {
        type: String,
        default: '#ffffff',
    },
});

const canvas = ref(null);

async function renderQR() {
    if (!canvas.value || !props.value) return;

    try {
        await QRCode.toCanvas(canvas.value, props.value, {
            width: props.size,
            margin: 2,
            color: {
                dark: props.darkColor,
                light: props.lightColor,
            },
            errorCorrectionLevel: 'M',
        });
    } catch (err) {
        console.error('QR Code generation failed:', err);
    }
}

onMounted(renderQR);
watch(() => props.value, renderQR);
watch(() => props.size, renderQR);
</script>

<template>
    <canvas ref="canvas" class="rounded-xl" />
</template>
