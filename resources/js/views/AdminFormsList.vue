<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useFormsStore } from '../stores/forms';

const formsStore = useFormsStore();
const router = useRouter();

onMounted(() => {
    formsStore.fetchForms();
});

async function createForm() {
    const form = await formsStore.createForm({ title: '無題のフォーム', description: '' });
    router.push({ name: 'admin.forms.edit', params: { id: form.id } });
}

async function removeForm(form) {
    if (!window.confirm(`「${form.title}」を削除しますか？この操作は取り消せません。`)) return;
    await formsStore.deleteForm(form.id);
}

async function duplicateForm(form) {
    const copy = await formsStore.duplicateForm(form.id);
    router.push({ name: 'admin.forms.edit', params: { id: copy.id } });
}
</script>

<template>
    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-semibold text-gray-900">フォーム一覧</h1>
            <button
                type="button"
                class="bg-gray-900 text-white text-base font-medium px-5 py-2.5 rounded-md hover:bg-black"
                @click="createForm"
            >
                + 新規フォーム
            </button>
        </div>

        <p v-if="formsStore.loading" class="text-base text-gray-500">読み込み中…</p>

        <p v-else-if="formsStore.forms.length === 0" class="text-base text-gray-500">
            まだフォームがありません。上のボタンから作成してください。
        </p>

        <ul v-else class="space-y-4">
            <li
                v-for="form in formsStore.forms"
                :key="form.id"
                class="bg-white border border-gray-200 rounded-lg p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <div>
                    <div class="flex items-center gap-3">
                        <router-link
                            :to="{ name: 'admin.forms.edit', params: { id: form.id } }"
                            class="text-lg font-medium text-gray-900 hover:underline"
                        >
                            {{ form.title }}
                        </router-link>
                        <span
                            class="text-sm px-2.5 py-1 rounded-full"
                            :class="form.is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                        >
                            {{ form.is_published ? '公開中' : '下書き' }}
                        </span>
                    </div>
                    <p class="text-base text-gray-500">
                        {{ form.responses_count ?? 0 }} 件の回答
                        <span v-if="form.owner"> · 作成者: {{ form.owner.name }}</span>
                    </p>
                </div>

                <div class="flex items-center gap-5 text-base">
                    <router-link
                        :to="{ name: 'admin.forms.edit', params: { id: form.id } }"
                        class="text-gray-600 hover:text-gray-900"
                    >
                        編集
                    </router-link>
                    <router-link
                        :to="{ name: 'admin.forms.responses', params: { id: form.id } }"
                        class="text-gray-600 hover:text-gray-900"
                    >
                        回答
                    </router-link>
                    <button type="button" class="text-gray-600 hover:text-gray-900" @click="duplicateForm(form)">
                        コピー
                    </button>
                    <button type="button" class="text-red-600 hover:text-red-800" @click="removeForm(form)">
                        削除
                    </button>
                </div>
            </li>
        </ul>
    </div>
</template>
