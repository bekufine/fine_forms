<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import http from '../api/http';
import { useAuthStore } from '../stores/auth';
import { DEFAULT_LOCALE, JA_CLARITY_SCALE, JA_SATISFACTION_SCALE, JA_SCALE, LANGUAGES, TRANSLATIONS } from '../i18n/surveyTranslations';
import { LOCATION_REVIEW_LINKS } from '../config/locationReviewLinks';

const props = defineProps({
    id: {
        type: [String, Number],
        required: true,
    },
});

const authStore = useAuthStore();

const form = ref(null);
const loadErrorCode = ref('');
const answers = reactive({});
const errors = reactive({});
const submitting = ref(false);
const submitted = ref(false);
const submitError = ref('');

const locale = ref(localStorage.getItem('surveyLocale') || DEFAULT_LOCALE);
const t = computed(() => TRANSLATIONS[locale.value] ?? TRANSLATIONS[DEFAULT_LOCALE]);

const reviewLink = computed(() => {
    const locationQuestion = form.value?.questions?.[0];
    if (!locationQuestion) return null;
    return LOCATION_REVIEW_LINKS[answers[locationQuestion.id]] ?? null;
});

const displayTitle = computed(() => {
    if (!form.value) return '';
    if (form.value.title === TRANSLATIONS.ja.title) return t.value.title;
    if (form.value.title === TRANSLATIONS.ja.officeTitle) return t.value.officeTitle;
    return form.value.title;
});

const displayDescription = computed(() => {
    if (!form.value?.description) return '';
    if (form.value.description === TRANSLATIONS.ja.description) return t.value.description;
    if (form.value.description === TRANSLATIONS.ja.officeDescription) return t.value.officeDescription;
    return form.value.description;
});

function translateQuestionTitle(jaTitle) {
    if (jaTitle === TRANSLATIONS.ja.locationQuestionTitle) return t.value.locationQuestionTitle;

    let index = TRANSLATIONS.ja.questions.indexOf(jaTitle);
    if (index !== -1) return t.value.questions[index] ?? jaTitle;

    index = TRANSLATIONS.ja.officeQuestions.indexOf(jaTitle);
    return index !== -1 ? (t.value.officeQuestions[index] ?? jaTitle) : jaTitle;
}

function translateOption(jaOption) {
    let index = JA_SCALE.indexOf(jaOption);
    if (index !== -1) return t.value.scale[index] ?? jaOption;

    index = JA_CLARITY_SCALE.indexOf(jaOption);
    if (index !== -1) return t.value.clarityScale[index] ?? jaOption;

    index = JA_SATISFACTION_SCALE.indexOf(jaOption);
    if (index !== -1) return t.value.satisfactionScale[index] ?? jaOption;

    return t.value.locations?.[jaOption] ?? jaOption;
}

async function fetchForm() {
    loadErrorCode.value = '';

    try {
        const { data } = await http.get(`/public/forms/${props.id}`);
        form.value = data;
        for (const question of data.questions) {
            if (!(question.id in answers)) {
                answers[question.id] = question.type === 'checkbox' ? [] : '';
            }
        }
    } catch (e) {
        loadErrorCode.value = e.response?.status === 404 ? 'notFound' : 'failed';
    }
}

function setLocale(code) {
    locale.value = code;
    localStorage.setItem('surveyLocale', code);
}

const loadError = computed(() => {
    if (loadErrorCode.value === 'notFound') return t.value.notAvailable;
    if (loadErrorCode.value === 'failed') return t.value.loadFailed;
    return '';
});

onMounted(fetchForm);

function isEmpty(value) {
    return value === null || value === undefined || value === '' || (Array.isArray(value) && value.length === 0);
}

function validate() {
    let valid = true;

    for (const question of form.value.questions) {
        errors[question.id] = '';

        if (question.is_required && isEmpty(answers[question.id])) {
            errors[question.id] = t.value.required;
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
            respondent_email: null,
            answers: form.value.questions.map((question) => ({
                question_id: question.id,
                value: answers[question.id],
            })),
        });
        submitted.value = true;
    } catch (e) {
        submitError.value = e.response?.data?.message || t.value.submitFailed;
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="flex justify-end items-center gap-3 mb-3">
                <label class="sr-only" :for="'locale-select'">{{ t.languageLabel }}</label>
                <select
                    id="locale-select"
                    :value="locale"
                    class="text-base text-gray-600 border border-gray-300 rounded-md px-3 py-2 bg-white"
                    @change="setLocale($event.target.value)"
                >
                    <option v-for="lang in LANGUAGES" :key="lang.code" :value="lang.code">
                        {{ lang.label }}
                    </option>
                </select>

                <router-link
                    :to="authStore.isAuthenticated ? '/admin' : '/login'"
                    class="text-base text-gray-500 border border-gray-300 rounded-md px-4 py-2 hover:bg-white hover:text-gray-700"
                >
                    {{ authStore.isAuthenticated ? t.adminPanel : t.adminLogin }}
                </router-link>
            </div>

            <p v-if="loadError" class="bg-white border border-gray-200 rounded-lg p-6 text-center text-lg text-gray-600">
                {{ loadError }}
            </p>

            <div v-else-if="submitted" class="bg-white border border-gray-200 rounded-lg p-8 text-center">
                <h1 class="text-2xl font-semibold text-gray-900 mb-2">{{ t.thanksTitle }}</h1>
                <p class="text-lg text-gray-500">{{ t.thanksBody }}</p>

                <div v-if="reviewLink" class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-lg font-medium text-gray-900 mb-3">{{ t.reviewInviteTitle }}</p>
                    <p
                        v-for="(paragraph, index) in t.reviewInviteBody"
                        :key="index"
                        class="text-base text-gray-500 mb-2 last:mb-4"
                    >
                        {{ paragraph }}
                    </p>
                    <a
                        :href="reviewLink"
                        target="_blank"
                        rel="noopener"
                        class="inline-block bg-gray-900 text-white rounded-md px-6 py-3 text-lg font-medium hover:bg-black"
                    >
                        {{ t.reviewButtonLabel }}
                    </a>
                </div>
            </div>

            <form v-else-if="form" class="space-y-4" @submit.prevent="submit">
                <div class="bg-white border border-gray-200 rounded-lg p-8">
                    <h1 class="text-3xl font-semibold text-gray-900">{{ displayTitle }}</h1>
                    <p v-if="displayDescription" class="text-lg text-gray-600 mt-2">{{ displayDescription }}</p>
                </div>

                <div
                    v-for="question in form.questions"
                    :key="question.id"
                    class="bg-white border border-gray-200 rounded-lg p-8"
                >
                    <label class="block text-lg font-medium text-gray-900 mb-4">
                        {{ translateQuestionTitle(question.title) }}
                        <span v-if="question.is_required" class="text-red-500">*</span>
                    </label>

                    <input
                        v-if="question.type === 'text'"
                        v-model="answers[question.id]"
                        type="text"
                        class="w-full rounded-md border border-gray-300 px-4 py-3 text-lg"
                    >

                    <textarea
                        v-else-if="question.type === 'textarea'"
                        v-model="answers[question.id]"
                        rows="4"
                        class="w-full rounded-md border border-gray-300 px-4 py-3 text-lg resize-none"
                    ></textarea>

                    <div v-else-if="question.type === 'radio'" class="space-y-3">
                        <label
                            v-for="option in question.options"
                            :key="option"
                            class="flex items-center gap-3 text-lg text-gray-700"
                        >
                            <input
                                v-model="answers[question.id]"
                                type="radio"
                                :name="`question-${question.id}`"
                                :value="option"
                                class="w-5 h-5"
                            >
                            {{ translateOption(option) }}
                        </label>
                    </div>

                    <div v-else-if="question.type === 'checkbox'" class="space-y-3">
                        <label
                            v-for="option in question.options"
                            :key="option"
                            class="flex items-center gap-3 text-lg text-gray-700"
                        >
                            <input
                                type="checkbox"
                                :checked="(answers[question.id] ?? []).includes(option)"
                                @change="toggleCheckbox(question.id, option, $event.target.checked)"
                                class="w-5 h-5"
                            >
                            {{ translateOption(option) }}
                        </label>
                    </div>

                    <select
                        v-else-if="question.type === 'select'"
                        v-model="answers[question.id]"
                        class="w-full rounded-md border border-gray-300 px-4 py-3 text-lg"
                    >
                        <option value="" disabled>{{ t.selectPlaceholder }}</option>
                        <option v-for="option in question.options" :key="option" :value="option">
                            {{ translateOption(option) }}
                        </option>
                    </select>

                    <div v-else-if="question.type === 'scale'" class="flex flex-wrap gap-3">
                        <button
                            v-for="n in 5"
                            :key="n"
                            type="button"
                            class="w-12 h-12 rounded-full border text-lg"
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
                        class="w-full rounded-md border border-gray-300 px-4 py-3 text-lg"
                    >

                    <p v-if="errors[question.id]" class="text-base text-red-600 mt-3">{{ errors[question.id] }}</p>
                </div>

                <p v-if="submitError" class="text-base text-red-600">{{ submitError }}</p>

                <button
                    type="submit"
                    :disabled="submitting"
                    class="w-full bg-gray-900 text-white rounded-md py-3.5 text-lg font-medium hover:bg-black disabled:opacity-50"
                >
                    {{ t.submit }}
                </button>
            </form>
        </div>
    </div>
</template>
