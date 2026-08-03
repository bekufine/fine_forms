<script setup>
import { computed } from 'vue';

const props = defineProps({
    question: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update', 'remove']);

const QUESTION_TYPES = [
    { value: 'text', label: 'Short answer' },
    { value: 'textarea', label: 'Paragraph' },
    { value: 'radio', label: 'Multiple choice' },
    { value: 'checkbox', label: 'Checkboxes' },
    { value: 'select', label: 'Dropdown' },
    { value: 'scale', label: 'Linear scale' },
    { value: 'date', label: 'Date' },
];

const hasOptions = computed(() => ['radio', 'checkbox', 'select'].includes(props.question.type));

function update(patch) {
    emit('update', patch);
}

function updateOption(index, value) {
    const options = [...(props.question.options ?? [])];
    options[index] = value;
    update({ options });
}

function addOption() {
    const options = [...(props.question.options ?? [])];
    options.push(`Option ${options.length + 1}`);
    update({ options });
}

function removeOption(index) {
    const options = (props.question.options ?? []).filter((_, i) => i !== index);
    update({ options });
}

function onTypeChange(event) {
    const type = event.target.value;
    const patch = { type };

    if (['radio', 'checkbox', 'select'].includes(type) && !(props.question.options?.length)) {
        patch.options = ['Option 1'];
    }

    update(patch);
}
</script>

<template>
    <div class="border border-gray-200 rounded-lg p-4 bg-white">
        <div class="flex items-start gap-3 mb-3">
            <span class="cursor-grab text-gray-400 mt-2 select-none" title="Drag to reorder">⠿</span>

            <div class="flex-1 space-y-3">
                <input
                    :value="question.title"
                    type="text"
                    placeholder="Question title"
                    class="w-full text-sm font-medium border-b border-gray-200 focus:border-gray-400 outline-none py-1"
                    @input="update({ title: $event.target.value })"
                >

                <div class="flex flex-wrap items-center gap-3">
                    <select
                        :value="question.type"
                        class="text-sm border border-gray-300 rounded-md px-2 py-1"
                        @change="onTypeChange"
                    >
                        <option v-for="type in QUESTION_TYPES" :key="type.value" :value="type.value">
                            {{ type.label }}
                        </option>
                    </select>

                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input
                            :checked="question.is_required"
                            type="checkbox"
                            @change="update({ is_required: $event.target.checked })"
                        >
                        Required
                    </label>
                </div>

                <div v-if="hasOptions" class="space-y-2">
                    <div
                        v-for="(option, index) in question.options ?? []"
                        :key="index"
                        class="flex items-center gap-2"
                    >
                        <span class="text-gray-400 text-sm w-4">{{ index + 1 }}.</span>
                        <input
                            :value="option"
                            type="text"
                            class="flex-1 text-sm border border-gray-300 rounded-md px-2 py-1"
                            @input="updateOption(index, $event.target.value)"
                        >
                        <button
                            type="button"
                            class="text-gray-400 hover:text-red-600 text-sm"
                            @click="removeOption(index)"
                        >
                            ✕
                        </button>
                    </div>
                    <button
                        type="button"
                        class="text-sm text-blue-600 hover:text-blue-800"
                        @click="addOption"
                    >
                        + Add option
                    </button>
                </div>
            </div>

            <button
                type="button"
                class="text-gray-400 hover:text-red-600 text-sm shrink-0"
                title="Delete question"
                @click="emit('remove')"
            >
                🗑
            </button>
        </div>
    </div>
</template>
