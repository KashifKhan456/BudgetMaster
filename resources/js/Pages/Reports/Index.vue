<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Bar, Line, Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, PointElement, LineElement, ArcElement } from 'chart.js';
import { formatCurrency } from '@/Utils/currency';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, PointElement, LineElement, ArcElement);

const props = defineProps({
    filters: Object,
    monthlySpending: Array,
    incomeVsExpense: Array,
    categoryBreakdown: Array,
    forecast: Number,
    budgetVsActual: Array,
    savingsRate: Number,
    topSpenders: Array,
    yoyGrowth: Number,
    netCashFlow: Number,
    recurringVsDiscretionary: Object,
});

const user = usePage().props.auth.user;
const currency = user.currency || 'USD';

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

const applyFilters = () => {
    router.get(route('reports.index'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const exportCsv = () => {
    window.location.href = route('reports.export', {
        start_date: startDate.value,
        end_date: endDate.value,
    });
};

const monthlySpendingData = computed(() => {
    const labels = props.monthlySpending.map(item => item.month);
    const data = props.monthlySpending.map(item => item.total);
    const datasets = [{
        label: 'Spending',
        backgroundColor: '#EF4444',
        data: data,
    }];

    if (props.forecast) {
        labels.push('Forecast');
        const forecastData = new Array(data.length).fill(null);
        forecastData.push(props.forecast);

        datasets.push({
            label: 'Forecast',
            backgroundColor: '#3B82F6',
            data: forecastData,
            skipNull: true,
        });
    }

    return { labels, datasets };
});

const incomeVsExpenseData = computed(() => ({
    labels: props.incomeVsExpense.map(item => item.month),
    datasets: [
        {
            label: 'Income',
            backgroundColor: '#10B981',
            borderColor: '#10B981',
            data: props.incomeVsExpense.map(item => item.income),
            fill: false,
        },
        {
            label: 'Expense',
            backgroundColor: '#EF4444',
            borderColor: '#EF4444',
            data: props.incomeVsExpense.map(item => item.expense),
            fill: false,
        }
    ]
}));

const categoryBreakdownData = computed(() => ({
    labels: props.categoryBreakdown.map(item => item.name),
    datasets: [{
        backgroundColor: props.categoryBreakdown.map(item => item.color || '#ccc'),
        data: props.categoryBreakdown.map(item => item.total),
    }]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
};
</script>

<template>

    <Head title="Reports" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Reports</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Filters & Export -->
                <div
                    class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-wrap gap-4 items-end justify-between">
                    <div class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                            <input type="date" v-model="startDate"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                            <input type="date" v-model="endDate"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                        </div>
                        <PrimaryButton @click="applyFilters">Apply Filters</PrimaryButton>
                    </div>
                    <SecondaryButton @click="exportCsv">Export CSV</SecondaryButton>
                </div>

                <!-- High Level Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Net Cash Flow -->
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Net Cash Flow</h3>
                        <div class="text-3xl font-bold" :class="netCashFlow >= 0 ? 'text-green-500' : 'text-red-500'">
                            {{ formatCurrency(netCashFlow, currency) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">Income - Expenses</div>
                    </div>

                    <!-- YoY Growth -->
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">YoY Spending Growth</h3>
                        <div class="flex items-center justify-between">
                            <div class="text-3xl font-bold" :class="yoyGrowth <= 0 ? 'text-green-500' : 'text-red-500'">
                                {{ yoyGrowth > 0 ? '+' : '' }}{{ yoyGrowth }}%
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">vs Last Year</div>
                        </div>
                         <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            {{ yoyGrowth <= 0 ? 'Great job! Spending is down.' : 'Spending is up compared to last year.' }}
                        </div>
                    </div>

                    <!-- Savings Rate -->
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Savings Rate</h3>
                        <div class="flex items-center justify-between">
                            <div class="text-3xl font-bold" :class="savingsRate >= 20 ? 'text-green-500' : 'text-yellow-500'">
                                {{ savingsRate }}%
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Target: 20%</div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mt-4">
                            <div class="bg-green-500 h-2.5 rounded-full" :style="{ width: Math.min(savingsRate, 100) + '%' }"></div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Recurring vs Discretionary -->
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Est. Fixed Costs</h3>
                         <div class="text-3xl font-bold text-indigo-500">
                            {{ formatCurrency(recurringVsDiscretionary.recurring, currency) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            Discretionary: {{ formatCurrency(recurringVsDiscretionary.discretionary, currency) }}
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mt-4 flex overflow-hidden">
                            <div class="bg-indigo-500 h-2.5" :style="{ width: Math.min((recurringVsDiscretionary.recurring / recurringVsDiscretionary.total) * 100, 100) + '%' }"></div>
                            <div class="bg-gray-400 h-2.5" :style="{ width: Math.min((recurringVsDiscretionary.discretionary / recurringVsDiscretionary.total) * 100, 100) + '%' }"></div>
                        </div>
                         <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>Fixed</span>
                            <span>Variable</span>
                        </div>
                    </div>
                    
                    <!-- Forecast Next Month -->
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Forecast Next Month</h3>
                         <div class="text-3xl font-bold text-blue-500">
                            {{ formatCurrency(forecast || 0, currency) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            Based on last 6 months trend
                        </div>
                    </div>

                    <!-- Top Spender -->
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                         <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Top Spending</h3>
                         <div v-if="topSpenders.length > 0">
                            <div class="text-xl font-bold text-gray-800 dark:text-gray-200 truncate">{{ topSpenders[0].description }}</div>
                            <div class="text-lg text-red-500">{{ formatCurrency(topSpenders[0].total, currency) }}</div>
                         </div>
                         <div v-else class="text-gray-500">No data</div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Monthly Spending -->
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Monthly Spending</h3>
                        <div class="h-64">
                            <Bar :data="monthlySpendingData" :options="chartOptions" />
                        </div>
                    </div>

                    <!-- Income vs Expense -->
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Income vs Expense</h3>
                        <div class="h-64">
                            <Line :data="incomeVsExpenseData" :options="chartOptions" />
                        </div>
                    </div>

                    <!-- Category Breakdown -->
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Expense Breakdown</h3>
                        <div class="h-64 flex justify-center">
                            <Doughnut :data="categoryBreakdownData" :options="chartOptions" />
                        </div>
                    </div>

                    <!-- Budget vs Actual -->
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6 overflow-y-auto max-h-[340px]">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Budget vs Actual</h3>
                        <div class="space-y-4">
                            <div v-for="item in budgetVsActual" :key="item.category">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ item.category }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">
                                        {{ formatCurrency(item.spent, currency) }} / {{ formatCurrency(item.budget, currency) }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="h-2.5 rounded-full" 
                                        :class="item.percentage > 100 ? 'bg-red-600' : (item.percentage > 85 ? 'bg-yellow-500' : 'bg-green-500')"
                                        :style="{ width: Math.min(item.percentage, 100) + '%' }"></div>
                                </div>
                            </div>
                            <div v-if="budgetVsActual.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-4">
                                No active budgets found.
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top Spenders Table -->
                    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6 md:col-span-2">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Top Spenders</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="(spender, index) in topSpenders" :key="index">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ spender.description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">{{ formatCurrency(spender.total, currency) }}</td>
                                    </tr>
                                    <tr v-if="topSpenders.length === 0">
                                        <td colspan="2" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No transactions found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
