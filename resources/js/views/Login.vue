<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { TRANSLATIONS } from '../i18n/surveyTranslations';

const surveyTitle = TRANSLATIONS.ja.title;

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();

const mode = ref('login'); // login | register
const form = ref({ name: '', email: '', password: '' });
const error = ref('');
const submitting = ref(false);

async function submit() {
    error.value = '';
    submitting.value = true;

    try {
        if (mode.value === 'login') {
            await authStore.login({ email: form.value.email, password: form.value.password });
        } else {
            await authStore.register({ ...form.value });
        }

        router.push(route.query.redirect || { name: 'admin.forms' });
    } catch (e) {
        error.value = e.response?.data?.message || 'エラーが発生しました。もう一度お試しください。';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <router-link
                to="/forms/1"
                class="inline-flex items-center gap-1 text-base text-gray-500 hover:text-gray-700 mb-4"
            >
                ← {{ surveyTitle }}
            </router-link>

            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-8">
            <h1 class="text-2xl font-semibold text-gray-900 mb-2">
                {{ mode === 'login' ? 'サインイン' : 'アカウント作成' }}
            </h1>
            <p class="text-base text-gray-500 mb-8">フォームの管理と回答の確認ができます。</p>

            <form class="space-y-5" @submit.prevent="submit">
                <div v-if="mode === 'register'">
                    <label class="block text-base font-medium text-gray-700 mb-1.5">名前</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full rounded-md border border-gray-300 px-4 py-3 text-lg focus:outline-none focus:ring focus:border-blue-300"
                    >
                </div>

                <div>
                    <label class="block text-base font-medium text-gray-700 mb-1.5">メールアドレス</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full rounded-md border border-gray-300 px-4 py-3 text-lg focus:outline-none focus:ring focus:border-blue-300"
                    >
                </div>

                <div>
                    <label class="block text-base font-medium text-gray-700 mb-1.5">パスワード</label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        class="w-full rounded-md border border-gray-300 px-4 py-3 text-lg focus:outline-none focus:ring focus:border-blue-300"
                    >
                </div>

                <p v-if="error" class="text-base text-red-600">{{ error }}</p>

                <button
                    type="submit"
                    :disabled="submitting"
                    class="w-full bg-gray-900 text-white rounded-md py-3 text-lg font-medium hover:bg-black disabled:opacity-50"
                >
                    {{ mode === 'login' ? 'サインイン' : 'サインアップ' }}
                </button>
            </form>

            <button
                type="button"
                class="mt-5 text-base text-gray-500 hover:text-gray-700 underline underline-offset-4"
                @click="mode = mode === 'login' ? 'register' : 'login'"
            >
                {{ mode === 'login' ? 'アカウントをお持ちでないですか？サインアップ' : 'すでにアカウントをお持ちですか？サインイン' }}
            </button>
            </div>
        </div>
    </div>
</template>
