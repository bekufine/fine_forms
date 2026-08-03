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
    const form = await formsStore.createForm({ title: 'Untitled form', description: '' });
    router.push({ name: 'admin.forms.edit', params: { id: form.id } });
}

async function removeForm(form) {
    if (!window.confirm(`Delete "${form.title}"? This cannot be undone.`)) return;
    await formsStore.deleteForm(form.id);
}
</script>

<template>
    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold text-gray-900">Your forms</h1>
            <button
                type="button"
                class="bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-md hover:bg-black"
                @click="createForm"
            >
                + New form
            </button>
        </div>

        <p v-if="formsStore.loading" class="text-sm text-gray-500">Loading…</p>

        <p v-else-if="formsStore.forms.length === 0" class="text-sm text-gray-500">
            You don't have any forms yet. Create your first one above.
        </p>

        <ul v-else class="space-y-3">
            <li
                v-for="form in formsStore.forms"
                :key="form.id"
                class="bg-white border border-gray-200 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
            >
                <div>
                    <div class="flex items-center gap-2">
                        <router-link
                            :to="{ name: 'admin.forms.edit', params: { id: form.id } }"
                            class="font-medium text-gray-900 hover:underline"
                        >
                            {{ form.title }}
                        </router-link>
                        <span
                            class="text-xs px-2 py-0.5 rounded-full"
                            :class="form.is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                        >
                            {{ form.is_published ? 'Published' : 'Draft' }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">{{ form.responses_count ?? 0 }} responses</p>
                </div>

                <div class="flex items-center gap-4 text-sm">
                    <router-link
                        :to="{ name: 'admin.forms.edit', params: { id: form.id } }"
                        class="text-gray-600 hover:text-gray-900"
                    >
                        Edit
                    </router-link>
                    <router-link
                        :to="{ name: 'admin.forms.responses', params: { id: form.id } }"
                        class="text-gray-600 hover:text-gray-900"
                    >
                        Responses
                    </router-link>
                    <button type="button" class="text-red-600 hover:text-red-800" @click="removeForm(form)">
                        Delete
                    </button>
                </div>
            </li>
        </ul>
    </div>
</template>
