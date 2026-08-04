<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    BarElement,
    CategoryScale,
    LinearScale,
} from 'chart.js';

ChartJS.register(Title, Tooltip, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    stat: {
        type: Object,
        required: true,
    },
});

const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches ?? false;

const colors = prefersDark
    ? { bar: '#3987e5', grid: '#2c2c2a', ink: '#c3c2b7', axis: '#383835' }
    : { bar: '#2a78d6', grid: '#e1e0d9', ink: '#52514e', axis: '#c3c2b7' };

const chartData = computed(() => {
    const entries = Object.entries(props.stat.counts ?? {});

    return {
        labels: entries.map(([option]) => option),
        datasets: [
            {
                label: props.stat.title,
                data: entries.map(([, count]) => count),
                backgroundColor: colors.bar,
                borderRadius: 4,
                maxBarThickness: 48,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (context) => `${context.parsed.y} 件の回答`,
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { color: colors.ink },
            border: { color: colors.axis },
        },
        y: {
            beginAtZero: true,
            ticks: { precision: 0, color: colors.ink },
            grid: { color: colors.grid },
            border: { color: colors.axis },
        },
    },
};
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-lg p-5">
        <h3 class="text-base font-medium text-gray-900 mb-1">{{ stat.title }}</h3>
        <p class="text-sm text-gray-500 mb-3">{{ stat.total_answers }} 件の回答</p>
        <div class="h-56">
            <Bar :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
