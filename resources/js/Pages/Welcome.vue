<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 selection:bg-indigo-500 selection:text-white overflow-hidden">
        <!-- Navigation -->
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="ml-2 text-xl font-bold text-gray-800 dark:text-white">BudgetMaster</span>
            </div>
            <div v-if="canLogin" class="flex space-x-4">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Dashboard</Link>
                <template v-else>
                    <Link :href="route('login')" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Log in</Link>
                    <Link v-if="canRegister" :href="route('register')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold transition duration-300">Get Started</Link>
                </template>
            </div>
        </nav>

        <!-- Hero Section -->
        <header class="container mx-auto px-6 py-16 text-center lg:text-left lg:flex lg:items-center lg:justify-between">
            <div class="lg:w-1/2">
                <h1 class="text-4xl lg:text-6xl font-bold leading-tight mb-6">
                    Master Your Money, <br>
                    <span class="text-indigo-600 dark:text-indigo-400">Secure Your Future.</span>
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">
                    Track expenses, set savings goals, and visualize your financial health with our intuitive budget management tool.
                </p>
                <div class="flex flex-col sm:flex-row justify-center lg:justify-start space-y-4 sm:space-y-0 sm:space-x-4">
                    <Link :href="route('register')" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-lg font-semibold rounded-lg shadow-lg transform hover:scale-105 transition duration-300">Start for Free</Link>
                    <a href="#features" class="px-8 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-lg font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-300">Learn More</a>
                </div>
            </div>
            <div class="lg:w-1/2 mt-12 lg:mt-0 relative">
                <!-- Abstract decorative elements -->
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-72 h-72 bg-purple-300 dark:bg-purple-900 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
                <div class="absolute top-0 -right-4 w-72 h-72 bg-yellow-300 dark:bg-yellow-900 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
                <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 dark:bg-pink-900 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
                
                <!-- Dashboard Mockup -->
                <div class="relative rounded-xl shadow-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden transform rotate-2 hover:rotate-0 transition duration-500">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center space-x-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    </div>
                    <div class="p-6 grid grid-cols-2 gap-4">
                        <div class="bg-indigo-50 dark:bg-indigo-900/30 p-4 rounded-lg">
                            <div class="text-sm text-indigo-600 dark:text-indigo-400 font-semibold">Total Balance</div>
                            <div class="text-2xl font-bold text-gray-800 dark:text-white">$12,450.00</div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/30 p-4 rounded-lg">
                            <div class="text-sm text-green-600 dark:text-green-400 font-semibold">Monthly Savings</div>
                            <div class="text-2xl font-bold text-gray-800 dark:text-white">+$2,100.00</div>
                        </div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg h-32 flex items-center justify-center text-gray-400">
                            <span>Spending Chart Visualization</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-white dark:bg-gray-800">
            <div class="container mx-auto px-6">
                <h2 class="text-3xl font-bold text-center mb-12">Everything you need to manage your finances</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-900 rounded-xl hover:shadow-xl transition duration-300">
                        <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Smart Tracking</h3>
                        <p class="text-gray-600 dark:text-gray-400">Effortlessly log income and expenses. Categorize transactions to see exactly where your money goes.</p>
                    </div>
                    <!-- Feature 2 -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-900 rounded-xl hover:shadow-xl transition duration-300">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Recurring Scheduler</h3>
                        <p class="text-gray-600 dark:text-gray-400">Never miss a bill. Set up recurring transactions for rent, subscriptions, and salary automatically.</p>
                    </div>
                    <!-- Feature 3 -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-900 rounded-xl hover:shadow-xl transition duration-300">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Visual Analytics</h3>
                        <p class="text-gray-600 dark:text-gray-400">Understand your spending habits with beautiful charts and graphs. Spot trends and adjust your budget.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-100 dark:bg-gray-900 py-8 border-t border-gray-200 dark:border-gray-800">
            <div class="container mx-auto px-6 text-center text-gray-600 dark:text-gray-400">
                <p>&copy; 2025 BudgetMaster. All rights reserved.</p>
                <div class="mt-4 flex justify-center space-x-4">
                    <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400">Privacy Policy</a>
                    <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400">Terms of Service</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
</style>
