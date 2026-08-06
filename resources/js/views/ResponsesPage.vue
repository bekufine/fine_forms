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

const exportUrl = computed(() => `/api/forms/${props.id}/responses/export`);

const chartStats = computed(() => (stats.value?.questions ?? []).filter((s) => CHOICE_TYPES.includes(s.type)));
const textStats = computed(() => (stats.value?.questions ?? []).filter((s) => !CHOICE_TYPES.includes(s.type)));

const copiedStatId = ref(null);
const copiedTable = ref(false);

async function copyStatValues(stat) {
    await navigator.clipboard.writeText(stat.values.join('\n'));
    copiedStatId.value = stat.question_id;
    setTimeout(() => {
        if (copiedStatId.value === stat.question_id) copiedStatId.value = null;
    }, 1500);
}

async function deleteResponse(responseId) {
    await http.delete(`/forms/${props.id}/responses/${responseId}`);

    responses.value = responses.value.filter((response) => response.id !== responseId);

    const statsRes = await http.get(`/forms/${props.id}/stats`);
    stats.value = statsRes.data;
}

async function copyTableAsTsv() {
    const header = ['送信日時', 'メールアドレス', ...form.value.questions.map((q) => q.title)];
    const rows = responses.value.map((response) => [
        new Date(response.submitted_at).toLocaleString(),
        response.respondent_email ?? '',
        ...form.value.questions.map((question) => {
            const answer = response.answers.find((a) => a.question_id === question.id);
            if (!answer || answer.value === null || answer.value === undefined) return '';
            return Array.isArray(answer.value) ? answer.value.join(', ') : String(answer.value);
        }),
    ]);

    const tsv = [header, ...rows].map((row) => row.join('\t')).join('\n');
    await navigator.clipboard.writeText(tsv);
    copiedTable.value = true;
    setTimeout(() => (copiedTable.value = false), 1500);
}

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
        <router-link :to="{ name: 'admin.forms' }" class="text-base text-gray-500 hover:text-gray-700">
            ← フォーム一覧に戻る
        </router-link>

        <div v-if="loading" class="text-base text-gray-500 mt-4">読み込み中…</div>

        <template v-else-if="form">
            <div class="flex items-center justify-between mt-4 mb-8">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ form.title }}</h1>
                    <p class="text-base text-gray-500">{{ stats.total_responses }} 件の回答</p>
                </div>

                <div class="flex items-center gap-2 text-base">
                    <button
                        type="button"
                        class="px-4 py-2 rounded-md"
                        :class="view === 'summary' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100'"
                        @click="view = 'summary'"
                    >
                        集計
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 rounded-md"
                        :class="view === 'table' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100'"
                        @click="view = 'table'"
                    >
                        個別の回答
                    </button>
                    <a
                        :href="exportUrl"
                        class="px-4 py-2 rounded-md text-gray-600 hover:bg-gray-100 border border-gray-300"
                    >
                        Excelでダウンロード
                    </a>
                </div>
            </div>

            <div v-if="view === 'summary'" class="space-y-4">
                <div v-if="chartStats.length" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <ResponsesChart v-for="stat in chartStats" :key="stat.question_id" :stat="stat" />
                </div>

                <div
                    v-for="stat in textStats"
                    :key="stat.question_id"
                    class="bg-white border border-gray-200 rounded-lg p-5"
                >
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-base font-medium text-gray-900">{{ stat.title }}</h3>
                        <button
                            v-if="stat.values.length"
                            type="button"
                            class="text-sm text-blue-600 hover:text-blue-800"
                            @click="copyStatValues(stat)"
                        >
                            {{ copiedStatId === stat.question_id ? 'コピーしました' : '回答をコピー' }}
                        </button>
                    </div>
                    <p class="text-sm text-gray-500 mb-3">{{ stat.total_answers }} 件の回答</p>
                    <ul v-if="stat.values.length" class="space-y-1 max-h-64 overflow-y-auto">
                        <li
                            v-for="(value, index) in stat.values"
                            :key="index"
                            class="text-base text-gray-700 border-b border-gray-100 py-1.5 last:border-0"
                        >
                            {{ value }}
                        </li>
                    </ul>
                    <p v-else class="text-base text-gray-400">まだ回答がありません。</p>
                </div>
            </div>

            <template v-else>
                <div class="flex justify-end mb-3">
                    <button
                        type="button"
                        class="text-sm text-blue-600 hover:text-blue-800"
                        @click="copyTableAsTsv"
                    >
                        {{ copiedTable ? 'コピーしました' : '表としてコピー' }}
                    </button>
                </div>
                <ResponsesTable :questions="form.questions" :responses="responses" @delete="deleteResponse" />
            </template>
        </template>
    </div>
</template>
