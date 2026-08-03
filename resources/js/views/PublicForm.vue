<script setup>
import { onMounted, reactive, ref } from 'vue';
import http from '../api/http';

const props = defineProps({
    id: {
        type: [String, Number],
        required: true,
    },
});

const form = ref(null);
const loadError = ref('');
const answers = reactive({});
const errors = reactive({});
const respondentEmail = ref('');
const submitting = ref(false);
const submitted = ref(false);
const submitError = ref('');

onMounted(async () => {
    try {
        const { data } = await http.get(`/public/forms/${props.id}`);
        form.value = data;
        for (const question of data.questions) {
            answers[question.id] = question.type === 'checkbox' ? [] : '';
        }
    } catch (e) {
        loadError.value = e.response?.status === 404
            ? 'This form is not available.'
            : 'Failed to load the form.';
    }
});

function isEmpty(value) {
    return value === null || value === undefined || value === '' || (Array.isArray(value) && value.length === 0);
}

function validate() {
    let valid = true;

    for (const question of form.value.questions) {
        errors[question.id] = '';

        if (question.is_required && isEmpty(answers[question.id])) {
            errors[question.id] = 'This question is required.';
            valid = false;
        }
    }

    return valid;
}

function toggleCheckbox(questionId, option, checked) {
    const current = answers[questionId] ?? [];
    answers[questionId] = checked
        ? [...current, option]
        : current.filter((value) => value !== option);
}

async function submit() {
    submitError.value = '';

    if (!validate()) return;

    submitting.value = true;

    try {
        await http.post(`/forms/${props.id}/responses`, {
            respondent_email: respondentEmail.value || null,
            answers: form.value.questions.map((question) => ({
                question_id: question.id,
                value: answers[question.id],
            })),
        });
        submitted.value = true;
    } catch (e) {
        submitError.value = e.response?.data?.message || 'Failed to submit your response. Please try again.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 px-4 py-8">
        <div class="max-w-xl mx-auto">
            <p v-if="loadError" class="bg-white border border-gray-200 rounded-lg p-6 text-center text-gray-600">
                {{ loadError }}
            </p>

            <div v-else-if="submitted" class="bg-white border border-gray-200 rounded-lg p-6 text-center">
                <h1 class="text-lg font-semibold text-gray-900 mb-1">Thanks!</h1>
                <p class="text-sm text-gray-500">Your response has been recorded.</p>
            </div>

            <form v-else-if="form" class="space-y-4" @submit.prevent="submit">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h1 class="text-xl font-semibold text-gray-900">{{ form.title }}</h1>
                    <p v-if="form.description" class="text-sm text-gray-600 mt-1">{{ form.description }}</p>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Your email (optional)</label>
                    <input
                        v-model="respondentEmail"
                        type="email"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                    >
                </div>

                <div
                    v-for="question in form.questions"
                    :key="question.id"
                    class="bg-white border border-gray-200 rounded-lg p-6"
                >
                    <label class="block text-sm font-medium text-gray-900 mb-3">
                        {{ question.title }}
                        <span v-if="question.is_required" class="text-red-500">*</span>
                    </label>

                    <input
                        v-if="question.type === 'text'"
                        v-model="answers[question.id]"
                        type="text"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                    >

                    <textarea
                        v-else-if="question.type === 'textarea'"
                        v-model="answers[question.id]"
                        rows="4"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm resize-none"
                    ></textarea>

                    <div v-else-if="question.type === 'radio'" class="space-y-2">
                        <label
                            v-for="option in question.options"
                            :key="option"
                            class="flex items-center gap-2 text-sm text-gray-700"
                        >
                            <input
                                v-model="answers[question.id]"
                                type="radio"
                                :name="`question-${question.id}`"
                                :value="option"
                            >
                            {{ option }}
                        </label>
                    </div>

                    <div v-else-if="question.type === 'checkbox'" class="space-y-2">
                        <label
                            v-for="option in question.options"
                            :key="option"
                            class="flex items-center gap-2 text-sm text-gray-700"
                        >
                            <input
                                type="checkbox"
                                :checked="(answers[question.id] ?? []).includes(option)"
                                @change="toggleCheckbox(question.id, option, $event.target.checked)"
                            >
                            {{ option }}
                        </label>
                    </div>

                    <select
                        v-else-if="question.type === 'select'"
                        v-model="answers[question.id]"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                    >
                        <option value="" disabled>Select an option</option>
                        <option v-for="option in question.options" :key="option" :value="option">
                            {{ option }}
                        </option>
                    </select>

                    <div v-else-if="question.type === 'scale'" class="flex flex-wrap gap-2">
                        <button
                            v-for="n in 5"
                            :key="n"
                            type="button"
                            class="w-10 h-10 rounded-full border text-sm"
                            :class="answers[question.id] === String(n)
                                ? 'bg-gray-900 text-white border-gray-900'
                                : 'border-gray-300 text-gray-700 hover:border-gray-400'"
                            @click="answers[question.id] = String(n)"
                        >
                            {{ n }}
                        </button>
                    </div>

                    <input
                        v-else-if="question.type === 'date'"
                        v-model="answers[question.id]"
                        type="date"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"
                    >

                    <p v-if="errors[question.id]" class="text-sm text-red-600 mt-2">{{ errors[question.id] }}</p>
                </div>

                <p v-if="submitError" class="text-sm text-red-600">{{ submitError }}</p>

                <button
                    type="submit"
                    :disabled="submitting"
                    class="w-full bg-gray-900 text-white rounded-md py-2.5 text-sm font-medium hover:bg-black disabled:opacity-50"
                >
                    Submit
                </button>
            </form>
        </div>
    </div>
</template>
