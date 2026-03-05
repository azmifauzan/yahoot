<script setup>
import { computed, defineAsyncComponent } from 'vue';

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    size: {
        type: Number,
        default: 64,
    },
});

const avatarComponents = {
    cat: defineAsyncComponent(() => import('./avatars/Cat.vue')),
    dog: defineAsyncComponent(() => import('./avatars/Dog.vue')),
    panda: defineAsyncComponent(() => import('./avatars/Panda.vue')),
    rabbit: defineAsyncComponent(() => import('./avatars/Rabbit.vue')),
    fox: defineAsyncComponent(() => import('./avatars/Fox.vue')),
    owl: defineAsyncComponent(() => import('./avatars/Owl.vue')),
    robot_blue: defineAsyncComponent(() => import('./avatars/RobotBlue.vue')),
    robot_red: defineAsyncComponent(() => import('./avatars/RobotRed.vue')),
    robot_green: defineAsyncComponent(() => import('./avatars/RobotGreen.vue')),
    robot_yellow: defineAsyncComponent(() => import('./avatars/RobotYellow.vue')),
    robot_purple: defineAsyncComponent(() => import('./avatars/RobotPurple.vue')),
    robot_pink: defineAsyncComponent(() => import('./avatars/RobotPink.vue')),
    monster_1: defineAsyncComponent(() => import('./avatars/Monster1.vue')),
    monster_2: defineAsyncComponent(() => import('./avatars/Monster2.vue')),
    monster_3: defineAsyncComponent(() => import('./avatars/Monster3.vue')),
    monster_4: defineAsyncComponent(() => import('./avatars/Monster4.vue')),
    monster_5: defineAsyncComponent(() => import('./avatars/Monster5.vue')),
    monster_6: defineAsyncComponent(() => import('./avatars/Monster6.vue')),
    star: defineAsyncComponent(() => import('./avatars/Star.vue')),
    moon: defineAsyncComponent(() => import('./avatars/Moon.vue')),
    sun: defineAsyncComponent(() => import('./avatars/Sun.vue')),
    cloud: defineAsyncComponent(() => import('./avatars/Cloud.vue')),
    rainbow: defineAsyncComponent(() => import('./avatars/Rainbow.vue')),
    lightning: defineAsyncComponent(() => import('./avatars/Lightning.vue')),
};

const avatarComponent = computed(() => avatarComponents[props.name] || null);
</script>

<template>
    <div class="inline-flex items-center justify-center">
        <component :is="avatarComponent" v-if="avatarComponent" :size="size" />
        <div
            v-else
            class="flex items-center justify-center rounded-full bg-gray-200 text-gray-400"
            :style="{ width: `${size}px`, height: `${size}px` }"
        >
            <svg
                class="h-1/2 w-1/2"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                />
            </svg>
        </div>
    </div>
</template>
