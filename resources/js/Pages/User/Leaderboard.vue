<script setup>
import Leaderboard from '@/Pages/Leaderboard.vue';

const props = defineProps({
    topPlayers: { type: Array, default: () => [] },
    myRank: { type: Object, default: null },
});
</script>

<template>
    <Leaderboard v-bind="props" panel />
</template>
