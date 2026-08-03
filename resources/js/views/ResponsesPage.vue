<script setup>
import { computed, onMounted, ref } from 'vue';
import http from '../api/http';
import ResponsesTable from '../components/ResponsesTable.vue';
import ResponsesChart from '../components/ResponsesChart.vue';

const props = defineProps({
    id: {
        type: [String, Number],
        required: true,
    },
});

const form = ref(null);
const responses = ref([]);
const stats = ref(null);
const view = ref('summary'); // summary | table
const loading = ref(true);

const CHOICE_TYPES = ['radio', 'checkbox', 'select', 'scale'];

const chartStats = computed(() => (stats.value?.questions ?? []).filter((s) => CHOICE_TYPES.includes(s.type)));
const textStats = computed(() => (stats.value?.questions ?? []).filter((s) => !CHOICE_TYPES.includes(s.type)));

onMounted(async () => {
    try {
        const [formRes, responsesRes, statsRes] = await Promise.all([
            http.get(`/forms/${props.id}`),
            http.get(`/forms/${props.id}/responses`),
            http.get(`/forms/${props.id}/stats`),
        ]);

        form.value = formRes.data;
        responses.value = responsesRes.data;
        stats.value = statsRes.data;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="max-w-5xl mx-auto px-4 py-8">
        <router-link :to="{ name: 'admin.forms' }" class="text-sm text-gray-500 hover:text-gray-700">
            ← Back to forms
        </router-link>

        <div v-if="loading" class="text-sm text-gray-500 mt-4">Loading…</div>

        <template v-else-if="form">
            <div class="flex items-center justify-between mt-4 mb-6">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">{{ form.title }}</h1>
                    <p class="text-sm text-gray-500">{{ stats.total_responses }} responses</p>
                </div>

                <div class="flex gap-2 text-sm">
                    <button
                        type="button"
                        class="px-3 py-1.5 rounded-md"
                        :class="view === 'summary' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100'"
                        @click="view = 'summary'"
                    >
                        Summary
                    </button>
                    <button
                        type="button"
                        class="px-3 py-1.5 rounded-md"
                        :class="view === 'table' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100'"
                        @click="view = 'table'"
                    >
                        Individual responses
                    </button>
                </div>
            </div>

            <div v-if="view === 'summary'" class="space-y-4">
                <div v-if="chartStats.length" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <ResponsesChart v-for="stat in chartStats" :key="stat.question_id" :stat="stat" />
                </div>

                <div
                    v-for="stat in textStats"
                    :key="stat.question_id"
                    class="bg-white border border-gray-200 rounded-lg p-4"
                >
                    <h3 class="text-sm font-medium text-gray-900 mb-1">{{ stat.title }}</h3>
                    <p class="text-xs text-gray-500 mb-3">{{ stat.total_answers }} answers</p>
                    <ul v-if="stat.values.length" class="space-y-1 max-h-64 overflow-y-auto">
                        <li
                            v-for="(value, index) in stat.values"
                            :key="index"
                            class="text-sm text-gray-700 border-b border-gray-100 py-1 last:border-0"
                        >
                            {{ value }}
                        </li>
                    </ul>
                    <p v-else class="text-sm text-gray-400">No answers yet.</p>
                </div>
            </div>

            <ResponsesTable v-else :questions="form.questions" :responses="responses" />
        </template>
    </div>
</template>
