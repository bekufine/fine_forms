import { defineStore } from 'pinia';
import http from '../api/http';
import { debounce } from '../utils/debounce';

let debouncedSaveMeta = null;
const debouncedSaveQuestion = {};

export const useFormsStore = defineStore('forms', {
    state: () => ({
        forms: [],
        currentForm: null,
        questions: [],
        saveStatus: 'idle', // idle | saving | saved | error
        loading: false,
    }),

    actions: {
        async fetchForms() {
            this.loading = true;
            try {
                const { data } = await http.get('/forms');
                this.forms = data;
            } finally {
                this.loading = false;
            }
        },

        async createForm(payload) {
            const { data } = await http.post('/forms', payload);
            this.forms.unshift(data);
            return data;
        },

        async deleteForm(formId) {
            await http.delete(`/forms/${formId}`);
            this.forms = this.forms.filter((form) => form.id !== formId);
        },

        async fetchForm(formId) {
            this.loading = true;
            try {
                const { data } = await http.get(`/forms/${formId}`);
                this.currentForm = data;
                this.questions = data.questions ?? [];
            } finally {
                this.loading = false;
            }
        },

        updateFormMetaLocal(patch) {
            this.currentForm = { ...this.currentForm, ...patch };
            this.saveStatus = 'saving';

            debouncedSaveMeta ??= debounce(async () => {
                try {
                    await http.put(`/forms/${this.currentForm.id}`, {
                        title: this.currentForm.title,
                        description: this.currentForm.description,
                        is_published: this.currentForm.is_published,
                    });
                    this.saveStatus = 'saved';
                } catch (error) {
                    this.saveStatus = 'error';
                }
            }, 1500);

            debouncedSaveMeta();
        },

        async togglePublish() {
            const { data } = await http.put(`/forms/${this.currentForm.id}`, {
                is_published: !this.currentForm.is_published,
            });
            this.currentForm = { ...this.currentForm, is_published: data.is_published };
        },

        async addQuestion(payload) {
            const { data } = await http.post(`/forms/${this.currentForm.id}/questions`, {
                type: 'text',
                title: '無題の質問',
                is_required: false,
                order: this.questions.length,
                ...payload,
            });
            this.questions.push(data);
            return data;
        },

        async duplicateQuestion(questionId) {
            const source = this.questions.find((question) => question.id === questionId);
            if (!source) return;

            const sourceIndex = this.questions.findIndex((question) => question.id === questionId);

            const { data } = await http.post(`/forms/${this.currentForm.id}/questions`, {
                type: source.type,
                title: `${source.title}（コピー）`,
                is_required: source.is_required,
                options: source.options,
                order: sourceIndex + 1,
            });

            this.questions.splice(sourceIndex + 1, 0, data);

            const order = this.questions.map((question, index) => ({ id: question.id, order: index }));
            await http.patch(`/forms/${this.currentForm.id}/questions/reorder`, { order });
        },

        updateQuestionLocal(questionId, patch) {
            const index = this.questions.findIndex((question) => question.id === questionId);
            if (index === -1) return;

            this.questions[index] = { ...this.questions[index], ...patch };
            this.saveStatus = 'saving';

            debouncedSaveQuestion[questionId] ??= debounce(async () => {
                const question = this.questions.find((item) => item.id === questionId);
                if (!question) return;

                try {
                    await http.patch(`/forms/${this.currentForm.id}/questions/${questionId}`, {
                        type: question.type,
                        title: question.title,
                        is_required: question.is_required,
                        options: question.options,
                    });
                    this.saveStatus = 'saved';
                } catch (error) {
                    this.saveStatus = 'error';
                }
            }, 1500);

            debouncedSaveQuestion[questionId]();
        },

        async deleteQuestion(questionId) {
            await http.delete(`/forms/${this.currentForm.id}/questions/${questionId}`);
            this.questions = this.questions.filter((question) => question.id !== questionId);
            delete debouncedSaveQuestion[questionId];
        },

        async reorderQuestions(orderedQuestions) {
            this.questions = orderedQuestions;

            const order = orderedQuestions.map((question, index) => ({
                id: question.id,
                order: index,
            }));

            try {
                await http.patch(`/forms/${this.currentForm.id}/questions/reorder`, { order });
            } catch (error) {
                this.saveStatus = 'error';
            }
        },
    },
});
