<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const success = computed(() => route.query.status === 'success');
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="bg-white border border-gray-200 rounded-lg p-8 max-w-md w-full text-center">
            <template v-if="success">
                <h1 class="text-2xl font-semibold text-gray-900 mb-2">メールアドレスを確認しました</h1>
                <p class="text-base text-gray-500">ご確認ありがとうございます。このまま続けてご利用いただけます。</p>
            </template>
            <template v-else>
                <h1 class="text-2xl font-semibold text-gray-900 mb-2">確認できませんでした</h1>
                <p class="text-base text-gray-500">
                    このリンクは無効か、有効期限が切れています。プロフィール画面からメールを再送信してください。
                </p>
            </template>

            <router-link
                :to="{ name: 'admin.profile' }"
                class="inline-block mt-6 bg-gray-900 text-white rounded-md px-6 py-3 text-lg font-medium hover:bg-black"
            >
                プロフィールへ戻る
            </router-link>
        </div>
    </div>
</template>
