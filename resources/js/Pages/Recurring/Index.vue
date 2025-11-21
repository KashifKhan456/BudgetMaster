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
    recurringTransactions: Array,
    categories: Array,
});

const form = useForm({
    category_id: '',
    amount: '',
    type: 'expense',
    interval: 'monthly',
    start_date: new Date().toISOString().split('T')[0],
    description: '',
});

const editingTransaction = ref(null);
const showModal = ref(false);

const openModal = (transaction = null) => {
    if (transaction) {
        editingTransaction.value = transaction;
        form.category_id = transaction.category_id;
        form.amount = transaction.amount;
        form.type = transaction.type;
        form.interval = transaction.interval;
        form.start_date = transaction.start_date;
        form.description = transaction.description;
    } else {
        editingTransaction.value = null;
        form.reset();
        form.start_date = new Date().toISOString().split('T')[0];
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingTransaction.value = null;
};

const submit = () => {
    if (editingTransaction.value) {
        form.put(route('recurring.update', editingTransaction.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('recurring.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteTransaction = (id) => {
    if (confirm('Are you sure you want to delete this recurring transaction?')) {
        useForm({}).delete(route('recurring.destroy', id));
    }
};
</script>

<template>
    <Head title="Recurring Transactions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Recurring Transactions</h2>
                <PrimaryButton @click="openModal()">Add Recurring</PrimaryButton>
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Interval</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Next Run</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-[#1a1a1a] divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="transaction in recurringTransactions" :key="transaction.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ transaction.description || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :style="{ backgroundColor: transaction.category?.color + '20', color: transaction.category?.color }">
                                                {{ transaction.category?.name || 'Uncategorized' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold" :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                            {{ transaction.type === 'income' ? '+' : '-' }}${{ transaction.amount }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap capitalize">{{ transaction.interval }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ transaction.next_run_date }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="openModal(transaction)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 mr-4">Edit</button>
                                            <button @click="deleteTransaction(transaction.id)" class="text-red-600 dark:text-red-400 hover:text-red-900">Delete</button>
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
                    {{ editingTransaction ? 'Edit Recurring Transaction' : 'Add Recurring Transaction' }}
                </h2>

                <div class="mt-6">
                    <InputLabel for="type" value="Type" />
                    <select id="type" v-model="form.type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="expense">Expense</option>
                        <option value="income">Income</option>
                    </select>
                    <div v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="amount" value="Amount" />
                    <TextInput id="amount" v-model="form.amount" type="number" step="0.01" class="mt-1 block w-full" placeholder="0.00" />
                    <div v-if="form.errors.amount" class="text-red-500 text-sm mt-1">{{ form.errors.amount }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="category_id" value="Category" />
                    <select id="category_id" v-model="form.category_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="">Select Category</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                    <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{ form.errors.category_id }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="interval" value="Interval" />
                    <select id="interval" v-model="form.interval" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                    <div v-if="form.errors.interval" class="text-red-500 text-sm mt-1">{{ form.errors.interval }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="start_date" value="Start Date" />
                    <TextInput id="start_date" v-model="form.start_date" type="date" class="mt-1 block w-full" />
                    <div v-if="form.errors.start_date" class="text-red-500 text-sm mt-1">{{ form.errors.start_date }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="description" value="Description" />
                    <TextInput id="description" v-model="form.description" type="text" class="mt-1 block w-full" placeholder="Description (optional)" />
                    <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton class="ml-3" @click="submit" :disabled="form.processing">
                        {{ editingTransaction ? 'Update' : 'Create' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
