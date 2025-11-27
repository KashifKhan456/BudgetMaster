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

    <div class="min-h-screen bg-[#0f172a] text-white selection:bg-indigo-500 selection:text-white overflow-hidden font-sans">
        <!-- Background Gradients -->
        <div class="fixed inset-0 z-0 pointer-events-none">
            <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-purple-900/20 blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-900/20 blur-[120px] animate-pulse-slow delay-1000"></div>
        </div>

        <!-- Navigation -->
        <nav class="relative z-10 container mx-auto px-6 py-6 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="text-2xl font-bold tracking-tight">BudgetMaster</span>
            </div>
            <div v-if="canLogin" class="flex items-center space-x-6">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Dashboard</Link>
                <template v-else>
                    <Link :href="route('login')" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Log in</Link>
                    <Link v-if="canRegister" :href="route('register')" class="px-5 py-2.5 bg-white text-gray-900 rounded-full text-sm font-bold hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg shadow-white/10">Get Started</Link>
                </template>
            </div>
        </nav>

        <!-- Hero Section -->
        <header class="relative z-10 container mx-auto px-6 pt-20 pb-32 text-center lg:text-left lg:flex lg:items-center lg:justify-between">
            <div class="lg:w-1/2 lg:pr-12">
                <div class="inline-block px-4 py-1.5 mb-6 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-sm font-medium backdrop-blur-sm">
                    ✨ The Future of Personal Finance
                </div>
                <h1 class="text-5xl lg:text-7xl font-extrabold leading-tight mb-8 tracking-tight">
                    Master Your Money, <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 animate-gradient">Design Your Life.</span>
                </h1>
                <p class="text-xl text-gray-400 mb-10 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Stop guessing where your money goes. Track expenses, visualize trends, and build wealth with a budgeting tool that's as ambitious as you are.
                </p>
                <div class="flex flex-col sm:flex-row justify-center lg:justify-start space-y-4 sm:space-y-0 sm:space-x-4">
                    <Link :href="route('register')" class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-lg font-bold rounded-2xl shadow-xl shadow-indigo-500/20 transform hover:-translate-y-1 transition-all duration-300">
                        Start for Free
                    </Link>
                    <a href="#features" class="px-8 py-4 bg-white/5 border border-white/10 hover:bg-white/10 text-white text-lg font-semibold rounded-2xl backdrop-blur-md transition-all duration-300">
                        Explore Features
                    </a>
                </div>
            </div>
            
            <!-- Hero Visual -->
            <div class="lg:w-1/2 mt-16 lg:mt-0 relative perspective-1000">
                <div class="relative transform rotate-y-12 rotate-x-6 hover:rotate-y-0 hover:rotate-x-0 transition-transform duration-700 ease-out">
                    <!-- Glass Card Main -->
                    <div class="relative z-20 bg-gray-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-2xl shadow-indigo-500/20">
                        <!-- Mockup Header -->
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex space-x-2">
                                <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                            </div>
                            <div class="h-2 w-20 bg-gray-700 rounded-full"></div>
                        </div>
                        
                        <!-- Mockup Content -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-indigo-500/10 border border-indigo-500/20 p-4 rounded-2xl">
                                <div class="text-indigo-300 text-xs font-medium mb-1">Total Balance</div>
                                <div class="text-2xl font-bold text-white">$24,500.00</div>
                            </div>
                            <div class="bg-green-500/10 border border-green-500/20 p-4 rounded-2xl">
                                <div class="text-green-300 text-xs font-medium mb-1">Monthly Savings</div>
                                <div class="text-2xl font-bold text-white">+$3,200.00</div>
                            </div>
                        </div>
                        
                        <!-- Mockup Chart Area -->
                        <div class="bg-gray-800/50 border border-white/5 rounded-2xl h-48 flex items-end justify-between p-4 space-x-2">
                            <div class="w-full bg-indigo-500/20 rounded-t-lg h-[40%] hover:h-[50%] transition-all duration-300"></div>
                            <div class="w-full bg-indigo-500/40 rounded-t-lg h-[60%] hover:h-[70%] transition-all duration-300"></div>
                            <div class="w-full bg-indigo-500/60 rounded-t-lg h-[45%] hover:h-[55%] transition-all duration-300"></div>
                            <div class="w-full bg-indigo-500/80 rounded-t-lg h-[80%] hover:h-[90%] transition-all duration-300"></div>
                            <div class="w-full bg-indigo-500 rounded-t-lg h-[65%] hover:h-[75%] transition-all duration-300 shadow-[0_0_20px_rgba(99,102,241,0.5)]"></div>
                        </div>
                    </div>

                    <!-- Floating Elements -->
                    <div class="absolute -top-10 -right-10 z-30 bg-gray-800/80 backdrop-blur-md border border-white/10 p-4 rounded-2xl shadow-xl animate-float">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center text-green-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            </div>
                            <div>
                                <div class="text-xs text-gray-400">Stock Portfolio</div>
                                <div class="text-sm font-bold text-white">+12.5%</div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -bottom-8 -left-8 z-30 bg-gray-800/80 backdrop-blur-md border border-white/10 p-4 rounded-2xl shadow-xl animate-float delay-2000">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-red-500/20 rounded-full flex items-center justify-center text-red-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            </div>
                            <div>
                                <div class="text-xs text-gray-400">Shopping</div>
                                <div class="text-sm font-bold text-white">-$120.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Features Section -->
        <section id="features" class="relative z-10 py-24 bg-gray-900/50 backdrop-blur-sm">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">Everything you need to <span class="text-indigo-400">thrive</span></h2>
                    <p class="text-gray-400 max-w-2xl mx-auto">Powerful features wrapped in a beautiful interface. Managing money has never felt this good.</p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="group p-8 bg-white/5 border border-white/10 rounded-3xl hover:bg-white/10 transition-all duration-300 hover:-translate-y-2">
                        <div class="w-14 h-14 bg-indigo-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Smart Analytics</h3>
                        <p class="text-gray-400 leading-relaxed">Visualize your spending patterns with interactive charts. Spot trends instantly and adjust your habits.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="group p-8 bg-white/5 border border-white/10 rounded-3xl hover:bg-white/10 transition-all duration-300 hover:-translate-y-2">
                        <div class="w-14 h-14 bg-purple-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Recurring & Forecasting</h3>
                        <p class="text-gray-400 leading-relaxed">Never miss a bill. Track recurring expenses and see your projected balance months in advance.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="group p-8 bg-white/5 border border-white/10 rounded-3xl hover:bg-white/10 transition-all duration-300 hover:-translate-y-2">
                        <div class="w-14 h-14 bg-pink-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Family Sharing</h3>
                        <p class="text-gray-400 leading-relaxed">Manage finances together. Share budgets with your partner or family members seamlessly.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative z-10 py-12 border-t border-white/5 bg-[#0f172a]">
            <div class="container mx-auto px-6 flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-lg font-bold">BudgetMaster</span>
                </div>
                <div class="text-gray-500 text-sm">
                    &copy; 2025 BudgetMaster. Crafted with ❤️.
                </div>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-500 hover:text-white transition-colors">Privacy</a>
                    <a href="#" class="text-gray-500 hover:text-white transition-colors">Terms</a>
                    <a href="#" class="text-gray-500 hover:text-white transition-colors">Twitter</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
.animate-pulse-slow {
    animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 0.8; }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
    100% { transform: translateY(0px); }
}

.perspective-1000 {
    perspective: 1000px;
}

.animate-gradient {
    background-size: 200% 200%;
    animation: gradient 8s ease infinite;
}
@keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
</style>
