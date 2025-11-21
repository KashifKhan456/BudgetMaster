<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    budgets: Array,
    categories: Array,
});

const form = useForm({
    category_id: '',
    amount: '',
    period: 'month',
    start_date: '',
    end_date: '',
});

const editingBudget = ref(null);
const showModal = ref(false);

const openModal = (budget = null) => {
    if (budget) {
        editingBudget.value = budget;
        form.category_id = budget.category_id;
        form.amount = budget.amount;
        form.period = budget.period;
        form.start_date = budget.start_date;
        form.end_date = budget.end_date;
    } else {
        editingBudget.value = null;
        form.reset();
        form.period = 'month'; // Default
        // Set default dates for current month
        const date = new Date();
        form.start_date = new Date(date.getFullYear(), date.getMonth(), 1).toISOString().split('T')[0];
        form.end_date = new Date(date.getFullYear(), date.getMonth() + 1, 0).toISOString().split('T')[0];
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingBudget.value = null;
};

const submit = () => {
    if (editingBudget.value) {
        form.put(route('budgets.update', editingBudget.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('budgets.store'), {
            onSuccess: () => closeModal(),
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
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Period</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dates</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-[#1a1a1a] divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="budget in budgets" :key="budget.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :style="{ backgroundColor: budget.category?.color + '20', color: budget.category?.color }">
                                                {{ budget.category?.name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-bold">${{ budget.amount }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap capitalize">{{ budget.period }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ budget.start_date }} - {{ budget.end_date }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="openModal(budget)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 mr-4">Edit</button>
                                            <button @click="deleteBudget(budget.id)" class="text-red-600 dark:text-red-400 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
                    <select id="category_id" v-model="form.category_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="" disabled>Select Category</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                    <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{ form.errors.category_id }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="amount" value="Amount" />
                    <TextInput id="amount" v-model="form.amount" type="number" step="0.01" class="mt-1 block w-full" placeholder="0.00" />
                    <div v-if="form.errors.amount" class="text-red-500 text-sm mt-1">{{ form.errors.amount }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="period" value="Period" />
                    <select id="period" v-model="form.period" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="month">Monthly</option>
                        <option value="year">Yearly</option>
                    </select>
                    <div v-if="form.errors.period" class="text-red-500 text-sm mt-1">{{ form.errors.period }}</div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <InputLabel for="start_date" value="Start Date" />
                        <TextInput id="start_date" v-model="form.start_date" type="date" class="mt-1 block w-full" />
                        <div v-if="form.errors.start_date" class="text-red-500 text-sm mt-1">{{ form.errors.start_date }}</div>
                    </div>
                    <div>
                        <InputLabel for="end_date" value="End Date" />
                        <TextInput id="end_date" v-model="form.end_date" type="date" class="mt-1 block w-full" />
                        <div v-if="form.errors.end_date" class="text-red-500 text-sm mt-1">{{ form.errors.end_date }}</div>
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
    </AuthenticatedLayout>
</template>
