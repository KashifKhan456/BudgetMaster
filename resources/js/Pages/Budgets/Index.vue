<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { formatDate } from '@/Utils/date';
import { formatCurrency } from '@/Utils/currency';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import Pagination from '@/Components/Pagination.vue';

import debounce from 'lodash/debounce';

const props = defineProps({
    budgets: Object,
    categories: Array,
    filters: Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const currency = computed(() => user.value.currency || 'USD');

const filters = ref({
    search: props.filters?.search || '',
    category_id: props.filters?.category_id || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

const loading = ref(false);

const updateParams = debounce((newFilters) => {
    const params = { ...newFilters };
    if (params.start_date instanceof Date) {
        params.start_date = params.start_date.toISOString().split('T')[0];
    }
    if (params.end_date instanceof Date) {
        params.end_date = params.end_date.toISOString().split('T')[0];
    }
    router.get(route('budgets.index'), params, {
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

const form = useForm({
    category_id: '',
    amount: '',
    period: 'month',
    start_date: '',
    end_date: '',
});

const editingBudget = ref(null);
const showModal = ref(false);
const showShareModal = ref(false);
const sharingBudget = ref(null);

const shareForm = useForm({
    email: '',
});

const openModal = (budget = null) => {
    if (budget) {
        editingBudget.value = budget;
        form.category_id = budget.category_id;
        form.amount = budget.amount;
        form.period = budget.period;
        form.start_date = new Date(budget.start_date);
        form.end_date = new Date(budget.end_date);
    } else {
        editingBudget.value = null;
        form.reset();
        form.reset();
        form.start_date = '';
    }
    showModal.value = true;
};

const openShareModal = (budget) => {
    sharingBudget.value = budget;
    showShareModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    showShareModal.value = false;
    form.reset();
    shareForm.reset();
    editingBudget.value = null;
    sharingBudget.value = null;
};

const submit = () => {
    const data = form.data();
    if (data.start_date instanceof Date) {
        data.start_date = data.start_date.toISOString().split('T')[0];
    }
    if (data.end_date instanceof Date) {
        data.end_date = data.end_date.toISOString().split('T')[0];
    }

    if (editingBudget.value) {
        form.transform(() => data).put(route('budgets.update', editingBudget.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.transform(() => data).post(route('budgets.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const submitShare = () => {
    if (sharingBudget.value) {
        shareForm.post(route('budgets.share', sharingBudget.value.id), {
            onSuccess: () => {
                shareForm.reset();
                // Refresh budget to show new user
                router.reload({ only: ['budgets'] });
            },
        });
    }
};

const removeUser = (userId) => {
    if (confirm('Are you sure you want to remove this user from the budget?')) {
        router.delete(route('budgets.unshare', { budget: sharingBudget.value.id, user: userId }), {
            onSuccess: () => {
                // Refresh budget
                router.reload({ only: ['budgets'] });
            },
        });
    }
};

const deleteBudget = (id) => {
    if (confirm('Are you sure you want to delete this budget?')) {
        useForm({}).delete(route('budgets.destroy', id));
    }
};

</script>

<template>

    <Head title="Budgets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Budgets</h2>
                <PrimaryButton @click="openModal()">Add Budget</PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Filters -->
                        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <InputLabel for="filter_search" value="Search" />
                                <InputText id="filter_search" v-model="filters.search" type="text"
                                    class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    placeholder="Search category..." />
                            </div>
                            <div>
                                <InputLabel for="filter_category" value="Category" />
                                <Select id="filter_category" v-model="filters.category_id" :options="categories"
                                    optionLabel="name" optionValue="id" placeholder="All Categories"
                                    class="mt-1 w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    showClear />
                            </div>
                            <div>
                                <InputLabel for="filter_start_date" value="Start Date" />
                                <DatePicker id="filter_start_date" v-model="filters.start_date" dateFormat="yy-mm-dd"
                                    showIcon showClear :minDate="null" class="mt-1 w-full"
                                    inputClass="w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                            </div>
                            <div>
                                <InputLabel for="filter_end_date" value="End Date" />
                                <DatePicker id="filter_end_date" v-model="filters.end_date" dateFormat="yy-mm-dd"
                                    showIcon showClear :minDate="null" class="mt-1 w-full"
                                    inputClass="w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
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
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Category</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Amount</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Period</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Dates</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-[#1a1a1a] divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="budget in budgets.data" :key="budget.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :style="{ backgroundColor: budget.category?.color + '20', color: budget.category?.color }">
                                                {{ budget.category?.name }}
                                            </span>
                                            <span v-if="budget.user_id !== user.id"
                                                class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                Shared
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-bold">{{
                                            formatCurrency(budget.amount,
                                            currency) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap capitalize">{{ budget.period }}</td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button v-if="budget.user_id === user.id" @click="openShareModal(budget)"
                                                class="text-green-600 dark:text-green-400 hover:text-green-900 mr-4">Share</button>
                                            <button @click="openModal(budget)"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 mr-4">Edit</button>
                                            <button @click="deleteBudget(budget.id)"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                    <tr v-if="budgets.data.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No records found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6">
                            <Pagination :links="budgets.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ editingBudget ? 'Edit Budget' : 'Add Budget' }}
                </h2>

                <div class="mt-6">
                    <InputLabel for="category_id" value="Category" />
                    <select id="category_id" v-model="form.category_id"
                        class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="" disabled>Select Category</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                    <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{ form.errors.category_id }}
                    </div>
                </div>

                <div class="mt-4">
                    <InputLabel for="amount" value="Amount" />
                    <TextInput id="amount" v-model="form.amount" type="number" step="0.01" class="mt-1 block w-full"
                        placeholder="0.00" />
                    <div v-if="form.errors.amount" class="text-red-500 text-sm mt-1">{{ form.errors.amount }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="period" value="Period" />
                    <select id="period" v-model="form.period"
                        class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="month">Monthly</option>
                        <option value="year">Yearly</option>
                    </select>
                    <div v-if="form.errors.period" class="text-red-500 text-sm mt-1">{{ form.errors.period }}</div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <InputLabel for="start_date" value="Start Date" />
                        <DatePicker id="start_date" v-model="form.start_date" dateFormat="yy-mm-dd" showIcon showOnFocus
                            appendTo="body" class="mt-1 w-full"
                            inputClass="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600" />
                        <div v-if="form.errors.start_date" class="text-red-500 text-sm mt-1">{{ form.errors.start_date
                        }}</div>
                    </div>
                    <div>
                        <InputLabel for="end_date" value="End Date" />
                        <DatePicker id="end_date" v-model="form.end_date" dateFormat="yy-mm-dd" showIcon showOnFocus
                            appendTo="body" class="mt-1 w-full"
                            inputClass="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600" />
                        <div v-if="form.errors.end_date" class="text-red-500 text-sm mt-1">{{ form.errors.end_date }}
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton class="ml-3" @click="submit" :disabled="form.processing">
                        {{ editingBudget ? 'Update' : 'Create' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
        <Modal :show="showShareModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Share Budget: {{ sharingBudget?.category?.name }}
                </h2>

                <div class="mt-6">
                    <InputLabel for="share_email" value="User Email" />
                    <TextInput id="share_email" v-model="shareForm.email" type="email" class="mt-1 block w-full"
                        placeholder="user@example.com" />
                    <div v-if="shareForm.errors.email" class="text-red-500 text-sm mt-1">{{ shareForm.errors.email }}
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton class="ml-3" @click="submitShare" :disabled="shareForm.processing">
                        Share
                    </PrimaryButton>
                </div>

                <div class="mt-8" v-if="sharingBudget?.users?.length > 0">
                    <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">Shared With</h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li v-for="user in sharingBudget.users" :key="user.id"
                            class="py-3 flex justify-between items-center">
                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                {{ user.name }} ({{ user.email }})
                            </div>
                            <button @click="removeUser(user.id)"
                                class="text-red-600 dark:text-red-400 hover:text-red-900 text-sm">Remove</button>
                        </li>
                    </ul>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
