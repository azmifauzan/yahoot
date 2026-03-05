<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    questions: Array,
    selectedIndex: Number,
    isQuestionComplete: Function,
});

const emit = defineEmits(['select', 'add', 'reorder', 'delete']);

// Drag and drop state
const draggedIndex = ref(null);
const dragOverIndex = ref(null);

function onDragStart(index) {
    draggedIndex.value = index;
}

function onDragOver(event, index) {
    event.preventDefault();
    dragOverIndex.value = index;
}

function onDragLeave() {
    dragOverIndex.value = null;
}

function onDrop(index) {
    if (draggedIndex.value === null || draggedIndex.value === index) {
        draggedIndex.value = null;
        dragOverIndex.value = null;
        return;
    }

    const newQuestions = [...props.questions];
    const [moved] = newQuestions.splice(draggedIndex.value, 1);
    newQuestions.splice(index, 0, moved);

    emit('reorder', newQuestions);

    // Adjust selected index
    if (props.selectedIndex === draggedIndex.value) {
        emit('select', index);
    }

    draggedIndex.value = null;
    dragOverIndex.value = null;
}

function onDragEnd() {
    draggedIndex.value = null;
    dragOverIndex.value = null;
}

function getTypeIcon(type) {
    return type === 'true_false' ? 'TF' : 'MC';
}
</script>

<template>
    <div class="w-64 flex-shrink-0 overflow-y-auto border-r border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
        <!-- Header -->
        <div class="sticky top-0 z-10 border-b border-gray-100 bg-white p-3 dark:border-gray-800 dark:bg-gray-950">
            <button
                @click="emit('add')"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                {{ t('quiz.add_question') }}
            </button>
        </div>

        <!-- Question List -->
        <div class="p-2 space-y-1">
            <div
                v-for="(question, index) in questions"
                :key="question.id"
                draggable="true"
                @dragstart="onDragStart(index)"
                @dragover="(e) => onDragOver(e, index)"
                @dragleave="onDragLeave"
                @drop="onDrop(index)"
                @dragend="onDragEnd"
                @click="emit('select', index)"
                :class="[
                    'group relative cursor-pointer rounded-lg border-2 p-2 transition',
                    selectedIndex === index
                        ? 'border-gray-900 bg-gray-100 dark:border-white dark:bg-gray-800'
                        : 'border-transparent hover:border-gray-200 hover:bg-gray-50 dark:hover:border-gray-600 dark:hover:bg-gray-700',
                    dragOverIndex === index ? 'border-gray-400 bg-gray-50 dark:bg-gray-800/50' : '',
                    draggedIndex === index ? 'opacity-50' : '',
                ]"
            >
                <!-- Question number + type badge -->
                <div class="mb-1 flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <!-- Drag handle -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 cursor-grab text-gray-300 group-hover:text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="9" cy="5" r="1.5"/><circle cx="15" cy="5" r="1.5"/>
                            <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                            <circle cx="9" cy="19" r="1.5"/><circle cx="15" cy="19" r="1.5"/>
                        </svg>
                        <span class="text-xs font-semibold text-gray-500">{{ index + 1 }}</span>
                        <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-500">
                            {{ getTypeIcon(question.type) }}
                        </span>
                    </div>

                    <!-- Validation indicator -->
                    <div class="flex items-center gap-1">
                        <span
                            v-if="!isQuestionComplete(question)"
                            class="flex h-4 w-4 items-center justify-center rounded-full bg-red-100 text-[10px] text-red-500"
                            title="Incomplete"
                        >!</span>

                        <!-- Delete button -->
                        <button
                            @click.stop="emit('delete', question)"
                            class="invisible flex h-4 w-4 items-center justify-center rounded text-gray-300 transition hover:text-red-500 group-hover:visible"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mini preview -->
                <div class="rounded bg-gray-50 p-1.5 dark:bg-gray-700/50">
                    <p class="truncate text-xs text-gray-600 dark:text-gray-400">
                        {{ question.question_text || t('quiz.question_placeholder') }}
                    </p>
                    <!-- Answer color dots -->
                    <div class="mt-1 flex gap-1">
                        <span
                            v-for="answer in question.answers"
                            :key="answer.id || answer.color"
                            :class="[
                                'h-2 w-2 rounded-full',
                                answer.color === 'red' ? 'bg-[#FF6B6B]' : '',
                                answer.color === 'blue' ? 'bg-[#5B8DEF]' : '',
                                answer.color === 'yellow' ? 'bg-[#FECA57]' : '',
                                answer.color === 'green' ? 'bg-[#48DBAB]' : '',
                            ]"
                        ></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="questions.length === 0" class="p-4 text-center">
            <p class="text-xs text-gray-400 dark:text-gray-500">{{ t('quiz.add_question') }}</p>
        </div>
    </div>
</template>
