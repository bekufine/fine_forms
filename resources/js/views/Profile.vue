<script setup>
import { reactive, ref } from 'vue';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();

const form = reactive({
    name: authStore.user?.name ?? '',
    email: authStore.user?.email ?? '',
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitting = ref(false);
const errors = ref({});
const success = ref(false);

async function submit() {
    submitting.value = true;
    success.value = false;
    errors.value = {};

    try {
        await authStore.updateProfile({ ...form });
        form.current_password = '';
        form.password = '';
        form.password_confirmation = '';
        success.value = true;
    } catch (e) {
        errors.value = e.response?.data?.errors ?? {};
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <router-link :to="{ name: 'admin.forms' }" class="text-base text-gray-500 hover:text-gray-700">
            ← フォーム一覧に戻る
        </router-link>

        <h1 class="text-2xl font-semibold text-gray-900 mt-4 mb-8">プロフィール設定</h1>

        <form class="bg-white border border-gray-200 rounded-lg p-8 space-y-5" @submit.prevent="submit">
            <div>
                <label class="block text-base font-medium text-gray-700 mb-1.5">名前</label>
                <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full rounded-md border border-gray-300 px-4 py-3 text-lg focus:outline-none focus:ring focus:border-blue-300"
                >
                <p v-if="errors.name" class="text-base text-red-600 mt-1">{{ errors.name[0] }}</p>
            </div>

            <div>
                <label class="block text-base font-medium text-gray-700 mb-1.5">メールアドレス</label>
                <input
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full rounded-md border border-gray-300 px-4 py-3 text-lg focus:outline-none focus:ring focus:border-blue-300"
                >
                <p v-if="errors.email" class="text-base text-red-600 mt-1">{{ errors.email[0] }}</p>
            </div>

            <div class="pt-3 border-t border-gray-200">
                <p class="text-base text-gray-500 mb-4">パスワードを変更する場合のみ入力してください。</p>

                <label class="block text-base font-medium text-gray-700 mb-1.5">現在のパスワード</label>
                <input
                    v-model="form.current_password"
                    type="password"
                    class="w-full rounded-md border border-gray-300 px-4 py-3 text-lg focus:outline-none focus:ring focus:border-blue-300"
                >
                <p v-if="errors.current_password" class="text-base text-red-600 mt-1">{{ errors.current_password[0] }}</p>
            </div>

            <div>
                <label class="block text-base font-medium text-gray-700 mb-1.5">新しいパスワード</label>
                <input
                    v-model="form.password"
                    type="password"
                    minlength="8"
                    class="w-full rounded-md border border-gray-300 px-4 py-3 text-lg focus:outline-none focus:ring focus:border-blue-300"
                >
                <p v-if="errors.password" class="text-base text-red-600 mt-1">{{ errors.password[0] }}</p>
            </div>

            <div>
                <label class="block text-base font-medium text-gray-700 mb-1.5">新しいパスワード（確認）</label>
                <input
                    v-model="form.password_confirmation"
                    type="password"
                    minlength="8"
                    class="w-full rounded-md border border-gray-300 px-4 py-3 text-lg focus:outline-none focus:ring focus:border-blue-300"
                >
            </div>

            <p v-if="success" class="text-base text-green-600">保存しました。</p>

            <button
                type="submit"
                :disabled="submitting"
                class="bg-gray-900 text-white text-lg font-medium px-5 py-2.5 rounded-md hover:bg-black disabled:opacity-50"
            >
                保存
            </button>
        </form>
    </div>
</template>
