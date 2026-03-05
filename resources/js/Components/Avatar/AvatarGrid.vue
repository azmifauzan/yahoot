<script setup>
import { useI18n } from 'vue-i18n';
import AvatarDisplay from './AvatarDisplay.vue';

const { t } = useI18n();

const props = defineProps({
    modelValue: {
        type: String,
        default: null,
    },
    size: {
        type: Number,
        default: 56,
    },
});

const emit = defineEmits(['update:modelValue']);

const categories = [
    {
        key: 'animals',
        avatars: ['cat', 'dog', 'panda', 'rabbit', 'fox', 'owl'],
    },
    {
        key: 'robots',
        avatars: ['robot_blue', 'robot_red', 'robot_green', 'robot_yellow', 'robot_purple', 'robot_pink'],
    },
    {
        key: 'monsters',
        avatars: ['monster_1', 'monster_2', 'monster_3', 'monster_4', 'monster_5', 'monster_6'],
    },
    {
        key: 'abstract',
        avatars: ['star', 'moon', 'sun', 'cloud', 'rainbow', 'lightning'],
    },
];

function select(avatar) {
    emit('update:modelValue', avatar);
}
</script>

<template>
    <div class="space-y-4">
        <div v-for="category in categories" :key="category.key">
            <h4 class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ t(`avatar_categories.${category.key}`) }}
            </h4>
            <div class="grid grid-cols-6 gap-2">
                <button
                    v-for="avatar in category.avatars"
                    :key="avatar"
                    type="button"
                    class="flex items-center justify-center rounded-xl p-1.5 transition-all hover:scale-110 hover:bg-gray-100 dark:hover:bg-gray-700"
                    :class="{
                        'ring-2 ring-primary-500 ring-offset-2 bg-primary-50 dark:bg-primary-900/20 dark:ring-offset-gray-800': modelValue === avatar,
                    }"
                    @click="select(avatar)"
                >
                    <AvatarDisplay :name="avatar" :size="size" />
                </button>
            </div>
        </div>
    </div>
</template>
