<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import draggable from 'vuedraggable';
import { useFormsStore } from '../stores/forms';
import QuestionEditor from '../components/QuestionEditor.vue';

const props = defineProps({
    id: {
        type: [String, Number],
        required: true,
    },
});

const formsStore = useFormsStore();
const draggableQuestions = ref([]);

onMounted(async () => {
    await formsStore.fetchForm(props.id);
});

watch(
    () => formsStore.questions,
    (questions) => {
        draggableQuestions.value = questions;
    },
    { immediate: true }
);

const saveStatusLabel = computed(() => ({
    idle: '',
    saving: 'Saving…',
    saved: 'All changes saved',
    error: 'Failed to save',
}[formsStore.saveStatus]));

function onDragEnd() {
    formsStore.reorderQuestions(draggableQuestions.value);
}

function addQuestion() {
    formsStore.addQuestion({});
}
</script>

<template>
    <div v-if="formsStore.currentForm" class="max-w-3xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <router-link :to="{ name: 'admin.forms' }" class="text-sm text-gray-500 hover:text-gray-700">
                ← Back to forms
            </router-link>
            <span class="text-sm text-gray-400">{{ saveStatusLabel }}</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6 space-y-3">
            <input
                :value="formsStore.currentForm.title"
                type="text"
                placeholder="Form title"
                class="w-full text-xl font-semibold border-b border-gray-200 focus:border-gray-400 outline-none py-1"
                @input="formsStore.updateFormMetaLocal({ title: $event.target.value })"
            >
            <textarea
                :value="formsStore.currentForm.description"
                placeholder="Form description"
                rows="2"
                class="w-full text-sm text-gray-600 border-b border-gray-200 focus:border-gray-400 outline-none py-1 resize-none"
                @input="formsStore.updateFormMetaLocal({ description: $event.target.value })"
            ></textarea>

            <div class="flex items-center justify-between pt-2">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input
                        type="checkbox"
                        :checked="formsStore.currentForm.is_published"
                        @change="formsStore.togglePublish"
                    >
                    Published (accepts responses)
                </label>

                <router-link
                    v-if="formsStore.currentForm.is_published"
                    :to="{ name: 'public.form', params: { id: formsStore.currentForm.id } }"
                    target="_blank"
                    class="text-sm text-blue-600 hover:text-blue-800"
                >
                    View public form ↗
                </router-link>
            </div>
        </div>

        <draggable
            v-model="draggableQuestions"
            item-key="id"
            handle=".cursor-grab"
            class="space-y-3"
            @end="onDragEnd"
        >
            <template #item="{ element }">
                <QuestionEditor
                    :question="element"
                    @update="(patch) => formsStore.updateQuestionLocal(element.id, patch)"
                    @remove="formsStore.deleteQuestion(element.id)"
                />
            </template>
        </draggable>

        <button
            type="button"
            class="mt-4 w-full border border-dashed border-gray-300 rounded-lg py-3 text-sm text-gray-500 hover:border-gray-400 hover:text-gray-700"
            @click="addQuestion"
        >
            + Add question
        </button>
    </div>
</template>
