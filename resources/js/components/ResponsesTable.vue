<script setup>
import { formatJstDateTime } from '../utils/formatJstDateTime';

defineProps({
    questions: {
        type: Array,
        required: true,
    },
    responses: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['delete']);

function answerFor(response, questionId) {
    const answer = response.answers.find((a) => a.question_id === questionId);
    if (!answer || answer.value === null || answer.value === undefined) return '—';
    return Array.isArray(answer.value) ? answer.value.join(', ') : String(answer.value);
}

function confirmDelete(response) {
    if (window.confirm('この回答を削除しますか？この操作は取り消せません。')) {
        emit('delete', response.id);
    }
}
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-lg overflow-x-auto">
        <table class="w-full text-base">
            <thead>
                <tr class="border-b border-gray-200 text-left text-gray-500">
                    <th class="px-4 py-3 whitespace-nowrap">送信日時</th>
                    <th class="px-4 py-3 whitespace-nowrap">メールアドレス</th>
                    <th
                        v-for="question in questions"
                        :key="question.id"
                        class="px-4 py-3 whitespace-nowrap"
                    >
                        {{ question.title }}
                    </th>
                    <th class="px-4 py-3 whitespace-nowrap"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="responses.length === 0">
                    <td :colspan="3 + questions.length" class="px-4 py-8 text-center text-gray-400">
                        まだ回答がありません。
                    </td>
                </tr>
                <tr
                    v-for="response in responses"
                    :key="response.id"
                    class="border-b border-gray-100 last:border-0"
                >
                    <td class="px-4 py-3 whitespace-nowrap text-gray-500">
                        {{ formatJstDateTime(response.submitted_at) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-gray-500">
                        {{ response.respondent_email ?? '—' }}
                    </td>
                    <td v-for="question in questions" :key="question.id" class="px-4 py-3">
                        {{ answerFor(response, question.id) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                        <button
                            type="button"
                            class="text-sm text-red-600 hover:text-red-800"
                            @click="confirmDelete(response)"
                        >
                            削除
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
