<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

import debounce from 'lodash/debounce';

const props = defineProps({
    requests: Array,
    budgets: Array, // We need to pass available budgets to select from
    categories: Array, // And categories
    filters: Object,
});

const user = usePage().props.auth.user;

const filters = ref({
    search: props.filters?.search || '',
});

const loading = ref(false);

const updateParams = debounce((newFilters) => {
    router.get(route('expense-requests.index'), newFilters, {
        preserveState: true,
        replace: true,
        showProgress: false,
        onFinish: () => {
            loading.value = false;
        },
    });
}, 300);

watch(filters, (newFilters) => {
    loading.value = true;
    updateParams(newFilters);
}, { deep: true });

const showCreateModal = ref(false);
const form = useForm({
    budget_id: '',
    category_id: '',
    amount: '',
    description: '',
});

const createRequest = () => {
    form.post(route('expense-requests.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

const approveForm = useForm({
    status: 'approved',
});

const rejectForm = useForm({
    status: 'rejected',
    rejection_reason: '',
});

const approve = (request) => {
    if (confirm('Are you sure you want to approve this request?')) {
        approveForm.put(route('expense-requests.update', request.id));
    }
};

const reject = (request) => {
    const reason = prompt('Reason for rejection (optional):');
    rejectForm.rejection_reason = reason;
    rejectForm.put(route('expense-requests.update', request.id));
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
};

</script>

<template>
    <Head title="Expense Requests" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Expense Requests</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-4 md:space-y-0">
                    <div class="w-full md:w-1/3">
                        <InputLabel for="filter_search" value="Search" class="sr-only" />
                        <TextInput 
                            id="filter_search" 
                            v-model="filters.search" 
                            type="text" 
                            class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                            placeholder="Search requests..." 
                        />
                    </div>
                    <PrimaryButton @click="showCreateModal = true">
                        New Request
                    </PrimaryButton>
                </div>

                <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <div class="overflow-x-auto relative">
                            <div v-if="loading" class="absolute inset-0 bg-white/70 dark:bg-black/70 flex items-center justify-center z-50 rounded-lg">
                                <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Requester</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Budget</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-[#1a1a1a] divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-if="requests.length === 0">
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No expense requests found.
                                        </td>
                                    </tr>
                                    <tr v-for="request in requests" :key="request.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ formatDate(request.created_at) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ request.user.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ request.budget.name || 'Budget #' + request.budget.id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ request.category.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ request.description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100">{{ formatCurrency(request.amount) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="{
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                                'bg-yellow-100 text-yellow-800': request.status === 'pending',
                                                'bg-green-100 text-green-800': request.status === 'approved',
                                                'bg-red-100 text-red-800': request.status === 'rejected',
                                            }">
                                                {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
                                            </span>
                                            <div v-if="request.rejection_reason" class="text-xs text-red-500 mt-1">
                                                {{ request.rejection_reason }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div v-if="request.status === 'pending' && request.budget.user_id === user.id" class="flex space-x-2">
                                                <button @click="approve(request)" class="text-green-600 hover:text-green-900 dark:hover:text-green-400">Approve</button>
                                                <button @click="reject(request)" class="text-red-600 hover:text-red-900 dark:hover:text-red-400">Reject</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    New Expense Request
                </h2>

                <div class="mt-6">
                    <div class="mb-4">
                        <InputLabel for="budget" value="Budget" />
                        <select id="budget" v-model="form.budget_id" class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="" disabled>Select a Budget</option>
                            <option v-for="budget in budgets" :key="budget.id" :value="budget.id">
                                {{ budget.name || 'Budget #' + budget.id }} ({{ formatCurrency(budget.amount) }})
                            </option>
                        </select>
                        <div v-if="form.errors.budget_id" class="text-red-500 text-xs mt-1">{{ form.errors.budget_id }}</div>
                    </div>

                    <div class="mb-4">
                        <InputLabel for="category" value="Category" />
                        <select id="category" v-model="form.category_id" class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="" disabled>Select a Category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                        <div v-if="form.errors.category_id" class="text-red-500 text-xs mt-1">{{ form.errors.category_id }}</div>
                    </div>

                    <div class="mb-4">
                        <InputLabel for="amount" value="Amount" />
                        <TextInput
                            id="amount"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full"
                            v-model="form.amount"
                            required
                        />
                        <div v-if="form.errors.amount" class="text-red-500 text-xs mt-1">{{ form.errors.amount }}</div>
                    </div>

                    <div class="mb-4">
                        <InputLabel for="description" value="Description" />
                        <TextInput
                            id="description"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.description"
                        />
                        <div v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="showCreateModal = false"> Cancel </SecondaryButton>
                        <PrimaryButton class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="createRequest">
                            Submit Request
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
