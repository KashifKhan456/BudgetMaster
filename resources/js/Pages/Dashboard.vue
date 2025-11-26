<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { formatDate } from '@/Utils/date';
import { formatCurrency } from '@/Utils/currency';
import { Doughnut, Line } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, Title } from 'chart.js';
import { computed, ref, watch } from 'vue';
import debounce from 'lodash/debounce';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, Title);

const props = defineProps({
    stats: Object,
    expensesByCategory: Array,
    monthlyTrend: Array,
    recentTransactions: Array,
    categories: Array, // Assuming categories are passed to dashboard
    filters: Object,
    insights: Array,
});

import InsightsWidget from '@/Components/Dashboard/InsightsWidget.vue';

const user = usePage().props.auth.user;
const currency = user.currency || 'USD';

const search = ref(props.filters?.search || '');
const category = ref(props.filters?.category || '');
const loading = ref(false);

const updateFilters = debounce(() => {
    router.get(
        route('dashboard'),
        { search: search.value, category: category.value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            showProgress: false,
            only: ['recentTransactions'],
            onFinish: () => {
                loading.value = false;
            },
        }
    );
}, 300);

watch([search, category], () => {
    loading.value = true;
    updateFilters();
});

const chartData = computed(() => {
    return {
        labels: props.expensesByCategory.map(item => item.category),
        datasets: [
            {
                backgroundColor: props.expensesByCategory.map(item => item.color || '#ccc'),
                data: props.expensesByCategory.map(item => item.total),
            },
        ],
    };
});

const lineChartData = computed(() => {
    return {
        labels: props.monthlyTrend.map(item => item.month),
        datasets: [
            {
                label: 'Income',
                backgroundColor: '#10B981',
                borderColor: '#10B981',
                data: props.monthlyTrend.map(item => item.income),
                fill: false,
            },
            {
                label: 'Expenses',
                backgroundColor: '#EF4444',
                borderColor: '#EF4444',
                data: props.monthlyTrend.map(item => item.expense),
                fill: false,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
};
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Total Income</div>
                        <div class="text-2xl font-bold text-green-500">{{ formatCurrency(stats.income, currency) }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Total Expenses</div>
                        <div class="text-2xl font-bold text-red-500">{{ formatCurrency(stats.expenses, currency) }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Total Budget</div>
                        <div class="text-2xl font-bold text-blue-500">{{ formatCurrency(stats.totalBudget, currency) }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Remaining</div>
                        <div class="text-2xl font-bold"
                            :class="stats.remainingBudget >= 0 ? 'text-green-500' : 'text-red-500'">
                            {{ formatCurrency(stats.remainingBudget, currency) }}
                        </div>
                    </div>
                </div>

                <!-- AI Spending Insights -->
                <InsightsWidget :insights="insights" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Chart: Expenses by Category -->
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Expenses by Category</h3>
                        <div class="h-64">
                            <Doughnut :data="chartData" :options="chartOptions" v-if="expensesByCategory.length > 0" />
                            <div v-else class="text-center text-gray-500 dark:text-gray-400 py-10">No expenses yet.
                            </div>
                        </div>
                    </div>

                    <!-- Chart: Monthly Trend -->
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Monthly Trend (Last 6
                            Months)</h3>
                        <div class="h-64">
                            <Line :data="lineChartData" :options="chartOptions" />
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-4 space-y-4 md:space-y-0">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Recent Transactions</h3>
                        <div class="flex space-x-4 w-full md:w-auto">
                            <input v-model="search" type="text" placeholder="Search transactions..."
                                class="w-full md:w-64 px-4 py-2 rounded-md border border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600" />
                            <select v-model="category"
                                class="w-full md:w-48 px-4 py-2 rounded-md border border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600">
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto relative">
                        <div v-if="loading"
                            class="absolute inset-0 bg-white/70 dark:bg-black/70 flex items-center justify-center z-50 rounded-lg">
                            <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Date</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Category</th>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="transaction in recentTransactions" :key="transaction.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{
                                        formatDate(transaction.date) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="transaction.splits && transaction.splits.length > 0"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            Split ({{ transaction.splits.length }})
                                        </span>
                                        <span v-else
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :style="{ backgroundColor: transaction.category?.color + '20', color: transaction.category?.color }">
                                            {{ transaction.category?.name || 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm font-medium"
                                        :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                        {{ transaction.type === 'income' ? '+' : '-' }}{{
                                        formatCurrency(transaction.amount,
                                        currency) }}
                                    </td>
                                </tr>
                                <tr v-if="recentTransactions.length === 0">
                                    <td colspan="3"
                                        class="px-4 py-2 text-sm text-center text-gray-500 dark:text-gray-400">No
                                        recent transactions found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
