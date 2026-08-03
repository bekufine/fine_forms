<script setup>
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

function answerFor(response, questionId) {
    const answer = response.answers.find((a) => a.question_id === questionId);
    if (!answer || answer.value === null || answer.value === undefined) return '—';
    return Array.isArray(answer.value) ? answer.value.join(', ') : String(answer.value);
}
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-lg overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left text-gray-500">
                    <th class="px-4 py-2 whitespace-nowrap">Submitted</th>
                    <th class="px-4 py-2 whitespace-nowrap">Email</th>
                    <th
                        v-for="question in questions"
                        :key="question.id"
                        class="px-4 py-2 whitespace-nowrap"
                    >
                        {{ question.title }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="responses.length === 0">
                    <td :colspan="2 + questions.length" class="px-4 py-6 text-center text-gray-400">
                        No responses yet.
                    </td>
                </tr>
                <tr
                    v-for="response in responses"
                    :key="response.id"
                    class="border-b border-gray-100 last:border-0"
                >
                    <td class="px-4 py-2 whitespace-nowrap text-gray-500">
                        {{ new Date(response.submitted_at).toLocaleString() }}
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-gray-500">
                        {{ response.respondent_email ?? '—' }}
                    </td>
                    <td v-for="question in questions" :key="question.id" class="px-4 py-2">
                        {{ answerFor(response, question.id) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
