<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    achievements: Array,
    userAchievements: Array,
});

const isUnlocked = (achievementId) => {
    return props.userAchievements.includes(achievementId);
};
</script>

<template>
    <Head title="Achievements" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Achievements</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <div v-for="achievement in achievements" :key="achievement.id" 
                        class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col items-center text-center transition-all duration-300"
                        :class="{ 'opacity-50 grayscale': !isUnlocked(achievement.id), 'transform hover:scale-105 border-2 border-yellow-400': isUnlocked(achievement.id) }"
                    >
                        <div class="text-4xl mb-4" :class="isUnlocked(achievement.id) ? 'text-yellow-400' : 'text-gray-400'">
                            <i :class="achievement.icon"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">{{ achievement.name }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ achievement.description }}</p>
                        
                        <div v-if="isUnlocked(achievement.id)" class="mt-4 text-xs font-semibold text-green-500 uppercase tracking-wider">
                            Unlocked
                        </div>
                        <div v-else class="mt-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Locked
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
