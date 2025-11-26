<script setup>
import { computed } from 'vue';

const props = defineProps({
    insights: {
        type: Array,
        default: () => [],
    },
});

const hasInsights = computed(() => props.insights && props.insights.length > 0);
</script>

<template>
    <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8">
        <div class="flex items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                <i class="pi pi-sparkles text-indigo-500 mr-2"></i>
                AI Spending Insights
            </h3>
        </div>

        <div v-if="hasInsights" class="space-y-4">
            <div v-for="(insight, index) in insights" :key="index" class="flex items-start p-4 rounded-lg bg-gray-50 dark:bg-[#262626] border border-gray-100 dark:border-[#404040]">
                <div class="flex-shrink-0 mr-4">
                    <i :class="[insight.icon, insight.color, 'text-xl']"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ insight.message }}</p>
                </div>
            </div>
        </div>
        <div v-else class="text-center py-6 text-gray-500 dark:text-gray-400 text-sm">
            <p>Not enough data to generate insights yet. Keep tracking your expenses!</p>
        </div>
    </div>
</template>
