<script setup>
import { onMounted, reactive, ref } from 'vue';
import http from '../api/http';

const admins = ref([]);
const loading = ref(true);
const submitting = ref(false);
const error = ref('');
const successMessage = ref('');
let successTimeout = null;

const form = reactive({ name: '', login_id: '', password: '' });

async function fetchAdmins() {
    loading.value = true;
    const { data } = await http.get('/admins');
    admins.value = data;
    loading.value = false;
}

async function createAdmin() {
    error.value = '';
    submitting.value = true;

    try {
        await http.post('/admins', { ...form });
        form.name = '';
        form.login_id = '';
        form.password = '';
        await fetchAdmins();

        successMessage.value = 'ユーザーを作成しました。';
        clearTimeout(successTimeout);
        successTimeout = setTimeout(() => { successMessage.value = ''; }, 3000);
    } catch (e) {
        error.value = e.response?.data?.message || '管理者の作成に失敗しました。もう一度お試しください。';
    } finally {
        submitting.value = false;
    }
}

onMounted(fetchAdmins);
</script>

<template>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <router-link :to="{ name: 'admin.forms' }" class="text-base text-gray-500 hover:text-gray-700">
            ← フォーム一覧に戻る
        </router-link>

        <h1 class="text-2xl font-semibold text-gray-900 mt-4 mb-8">管理者一覧</h1>

        <p
            v-if="successMessage"
            class="bg-green-50 border border-green-200 text-green-700 text-base rounded-md px-4 py-3 mb-6"
        >
            {{ successMessage }}
        </p>

        <form class="bg-white border border-gray-200 rounded-lg p-8 space-y-5 mb-8" @submit.prevent="createAdmin">
            <h2 class="text-base font-medium text-gray-900">管理者を追加</h2>

            <div>
                <label class="block text-base font-medium text-gray-700 mb-1.5">名前</label>
                <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full rounded-md border border-gray-300 px-4 py-3 text-lg focus:outline-none focus:ring focus:border-blue-300"
                >
            </div>

            <div>
                <label class="block text-base font-medium text-gray-700 mb-1.5">ユーザー名</label>
                <input
                    v-model="form.login_id"
                    type="text"
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
                    minlength="8"
                    class="w-full rounded-md border border-gray-300 px-4 py-3 text-lg focus:outline-none focus:ring focus:border-blue-300"
                >
            </div>

            <p v-if="error" class="text-base text-red-600">{{ error }}</p>

            <button
                type="submit"
                :disabled="submitting"
                class="bg-gray-900 text-white text-lg font-medium px-5 py-2.5 rounded-md hover:bg-black disabled:opacity-50"
            >
                追加
            </button>
        </form>

        <p v-if="loading" class="text-base text-gray-500">読み込み中…</p>

        <ul v-else class="space-y-3">
            <li
                v-for="admin in admins"
                :key="admin.id"
                class="bg-white border border-gray-200 rounded-lg p-5 flex items-center justify-between"
            >
                <div>
                    <p class="text-lg font-medium text-gray-900">{{ admin.name }}</p>
                    <p class="text-base text-gray-500">{{ admin.login_id }}</p>
                </div>
            </li>
        </ul>
    </div>
</template>
