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
    saving: '保存中…',
    saved: 'すべての変更を保存しました',
    error: '保存に失敗しました',
}[formsStore.saveStatus]));

function onDragEnd() {
    formsStore.reorderQuestions(draggableQuestions.value);
}

function addQuestion() {
    formsStore.addQuestion({});
}

function addFreeTextQuestion() {
    formsStore.addQuestion({ type: 'textarea', title: '自由記述' });
}
</script>

<template>
    <div v-if="formsStore.currentForm" class="max-w-3xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <router-link :to="{ name: 'admin.forms' }" class="text-base text-gray-500 hover:text-gray-700">
                ← フォーム一覧に戻る
            </router-link>
            <span class="text-base text-gray-400">{{ saveStatusLabel }}</span>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-8 space-y-4">
            <input
                :value="formsStore.currentForm.title"
                type="text"
                placeholder="フォームタイトル"
                class="w-full text-2xl font-semibold border-b border-gray-200 focus:border-gray-400 outline-none py-1.5"
                @input="formsStore.updateFormMetaLocal({ title: $event.target.value })"
            >
            <textarea
                :value="formsStore.currentForm.description"
                placeholder="フォームの説明"
                rows="2"
                class="w-full text-base text-gray-600 border-b border-gray-200 focus:border-gray-400 outline-none py-1.5 resize-none"
                @input="formsStore.updateFormMetaLocal({ description: $event.target.value })"
            ></textarea>

            <div class="flex items-center justify-between pt-2">
                <label class="flex items-center gap-2 text-base text-gray-600">
                    <input
                        type="checkbox"
                        :checked="formsStore.currentForm.is_published"
                        @change="formsStore.togglePublish"
                        class="w-4 h-4"
                    >
                    公開する（回答を受け付ける）
                </label>

                <router-link
                    v-if="formsStore.currentForm.is_published"
                    :to="{ name: 'public.form', params: { id: formsStore.currentForm.id } }"
                    target="_blank"
                    class="text-base text-blue-600 hover:text-blue-800"
                >
                    公開フォームを見る ↗
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
                    @duplicate="formsStore.duplicateQuestion(element.id)"
                />
            </template>
        </draggable>

        <div class="mt-4 flex gap-3">
            <button
                type="button"
                class="flex-1 border border-dashed border-gray-300 rounded-lg py-3.5 text-base text-gray-500 hover:border-gray-400 hover:text-gray-700"
                @click="addQuestion"
            >
                + 質問を追加
            </button>
            <button
                type="button"
                class="flex-1 border border-dashed border-gray-300 rounded-lg py-3.5 text-base text-gray-500 hover:border-gray-400 hover:text-gray-700"
                @click="addFreeTextQuestion"
            >
                + 自由記述を追加
            </button>
        </div>
    </div>
</template>
