<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from './stores/auth';

const authStore = useAuthStore();
const router = useRouter();

const user = computed(() => authStore.user);

async function handleLogout() {
    await authStore.logout();
    router.push({ name: 'public.form', params: { id: 1 } });
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header v-if="user" class="bg-white border-b border-gray-200">
            <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
                <router-link :to="{ name: 'admin.forms' }" class="text-lg font-semibold text-gray-900">
                    Fine Forms
                </router-link>
                <div class="flex items-center gap-5 text-base">
                    <router-link :to="{ name: 'admin.forms' }" class="text-gray-600 hover:text-gray-900">
                        フォーム一覧
                    </router-link>
                    <router-link :to="{ name: 'admin.users' }" class="text-gray-600 hover:text-gray-900">
                        管理者一覧
                    </router-link>
                    <router-link :to="{ name: 'admin.profile' }" class="text-gray-600 hover:text-gray-900">
                        プロフィール
                    </router-link>
                    <span class="text-gray-500">{{ user.email }}</span>
                    <button
                        type="button"
                        class="text-gray-600 hover:text-gray-900"
                        @click="handleLogout"
                    >
                        ログアウト
                    </button>
                </div>
            </div>
        </header>

        <main>
            <router-view />
        </main>
    </div>
</template>
