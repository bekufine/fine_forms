<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from './stores/auth';

const authStore = useAuthStore();
const router = useRouter();

const user = computed(() => authStore.user);

async function handleLogout() {
    await authStore.logout();
    router.push({ name: 'login' });
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header v-if="user" class="bg-white border-b border-gray-200">
            <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
                <router-link :to="{ name: 'admin.forms' }" class="font-semibold text-gray-900">
                    Fine Forms
                </router-link>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-gray-500">{{ user.email }}</span>
                    <button
                        type="button"
                        class="text-gray-600 hover:text-gray-900"
                        @click="handleLogout"
                    >
                        Log out
                    </button>
                </div>
            </div>
        </header>

        <main>
            <router-view />
        </main>
    </div>
</template>
