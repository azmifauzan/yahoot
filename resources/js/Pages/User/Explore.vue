<script setup>
import Explore from '@/Pages/Explore.vue';

const props = defineProps({
    quizzes: Object,
    categories: Array,
    tags: Array,
    filters: Object,
});
</script>

<template>
    <Explore v-bind="props" panel />
</template>
