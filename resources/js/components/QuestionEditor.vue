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
    { value: 'text', label: '記述式（短文）' },
    { value: 'textarea', label: '記述式（段落）' },
    { value: 'radio', label: 'ラジオボタン' },
    { value: 'checkbox', label: 'チェックボックス' },
    { value: 'select', label: 'プルダウン' },
    { value: 'scale', label: '均等目盛' },
    { value: 'date', label: '日付' },
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
    options.push(`オプション${options.length + 1}`);
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
        patch.options = ['オプション1'];
    }

    update(patch);
}
</script>

<template>
    <div class="border border-gray-200 rounded-lg p-5 bg-white">
        <div class="flex items-start gap-4 mb-3">
            <span class="cursor-grab text-gray-400 mt-2 select-none text-lg" title="ドラッグして並び替え">⠿</span>

            <div class="flex-1 space-y-4">
                <input
                    :value="question.title"
                    type="text"
                    placeholder="質問のタイトル"
                    class="w-full text-lg font-medium border-b border-gray-200 focus:border-gray-400 outline-none py-1.5"
                    @input="update({ title: $event.target.value })"
                >

                <div class="flex flex-wrap items-center gap-4">
                    <select
                        :value="question.type"
                        class="text-base border border-gray-300 rounded-md px-3 py-1.5"
                        @change="onTypeChange"
                    >
                        <option v-for="type in QUESTION_TYPES" :key="type.value" :value="type.value">
                            {{ type.label }}
                        </option>
                    </select>

                    <label class="flex items-center gap-2 text-base text-gray-600">
                        <input
                            :checked="question.is_required"
                            type="checkbox"
                            @change="update({ is_required: $event.target.checked })"
                            class="w-4 h-4"
                        >
                        必須
                    </label>
                </div>

                <div v-if="hasOptions" class="space-y-2.5">
                    <div
                        v-for="(option, index) in question.options ?? []"
                        :key="index"
                        class="flex items-center gap-2"
                    >
                        <span class="text-gray-400 text-base w-5">{{ index + 1 }}.</span>
                        <input
                            :value="option"
                            type="text"
                            class="flex-1 text-base border border-gray-300 rounded-md px-3 py-1.5"
                            @input="updateOption(index, $event.target.value)"
                        >
                        <button
                            type="button"
                            class="text-gray-400 hover:text-red-600 text-base"
                            @click="removeOption(index)"
                        >
                            ✕
                        </button>
                    </div>
                    <button
                        type="button"
                        class="text-base text-blue-600 hover:text-blue-800"
                        @click="addOption"
                    >
                        + 選択肢を追加
                    </button>
                </div>
            </div>

            <button
                type="button"
                class="text-gray-400 hover:text-red-600 text-lg shrink-0"
                title="質問を削除"
                @click="emit('remove')"
            >
                🗑
            </button>
        </div>
    </div>
</template>
