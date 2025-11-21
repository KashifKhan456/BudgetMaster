<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { formatDate } from '@/Utils/date';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

const props = defineProps({
    transactions: Array,
    categories: Array,
    filters: Object,
});

const filters = ref({
    search: props.filters?.search || '',
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    category_id: props.filters?.category_id || '',
    type: props.filters?.type || '',
});

watch(filters, (newFilters) => {
    router.get(route('transactions.index'), newFilters, {
        preserveState: true,
        replace: true,
    });
}, { deep: true });

const form = useForm({
    category_id: '',
    amount: '',
    type: 'expense',
    date: new Date().toISOString().split('T')[0],
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
        form.date = transaction.date;
        form.description = transaction.description;
    } else {
        editingTransaction.value = null;
        form.reset();
        form.date = new Date().toISOString().split('T')[0];
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
        form.put(route('transactions.update', editingTransaction.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('transactions.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

    const importForm = useForm({
        file: null,
    });

    const showImportModal = ref(false);

    const openImportModal = () => {
        showImportModal.value = true;
    };

    const closeImportModal = () => {
        showImportModal.value = false;
        importForm.reset();
    };

    const submitImport = () => {
        importForm.post(route('import.transactions'), {
            onSuccess: () => closeImportModal(),
        });
    };

    const deleteTransaction = (id) => {
        if (confirm('Are you sure you want to delete this transaction?')) {
            useForm({}).delete(route('transactions.destroy', id));
        }
    };
</script>

<template>
    <Head title="Transactions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Transactions</h2>
                <div class="flex space-x-2">
                    <button @click="openImportModal" class="inline-flex items-center px-4 py-2 bg-white dark:bg-[#1a1a1a] border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                        Import CSV
                    </button>
                    <a :href="route('export.transactions')" class="inline-flex items-center px-4 py-2 bg-white dark:bg-[#1a1a1a] border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                        Export CSV
                    </a>
                    <PrimaryButton @click="openModal()">Add Transaction</PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Filters -->
                        <div class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <InputLabel for="filter_search" value="Search" />
                                <InputText id="filter_search" v-model="filters.search" type="text" class="mt-1 block w-full" placeholder="Search description..." />
                            </div>
                            <div>
                                <InputLabel for="filter_start_date" value="Start Date" />
                                <DatePicker id="filter_start_date" v-model="filters.start_date" dateFormat="yy-mm-dd" showIcon class="mt-1 w-full" />
                            </div>
                            <div>
                                <InputLabel for="filter_end_date" value="End Date" />
                                <DatePicker id="filter_end_date" v-model="filters.end_date" dateFormat="yy-mm-dd" showIcon class="mt-1 w-full" />
                            </div>
                            <div>
                                <InputLabel for="filter_category" value="Category" />
                                <Select id="filter_category" v-model="filters.category_id" :options="categories" optionLabel="name" optionValue="id" placeholder="All Categories" class="mt-1 w-full" showClear />
                            </div>
                            <div>
                                <InputLabel for="filter_type" value="Type" />
                                <Select id="filter_type" v-model="filters.type" :options="[{label: 'Expense', value: 'expense'}, {label: 'Income', value: 'income'}]" optionLabel="label" optionValue="value" placeholder="All Types" class="mt-1 w-full" showClear />
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-[#1a1a1a] divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="transaction in transactions" :key="transaction.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ formatDate(transaction.date) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ transaction.description || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :style="{ backgroundColor: transaction.category?.color + '20', color: transaction.category?.color }">
                                                {{ transaction.category?.name || 'Uncategorized' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold" :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                            {{ transaction.type === 'income' ? '+' : '-' }}${{ transaction.amount }}
                                        </td>
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
                    {{ editingTransaction ? 'Edit Transaction' : 'Add Transaction' }}
                </h2>

                <div class="mt-6">
                    <InputLabel for="type" value="Type" />
                    <Select id="type" v-model="form.type" :options="[{label: 'Expense', value: 'expense'}, {label: 'Income', value: 'income'}]" optionLabel="label" optionValue="value" class="mt-1 w-full" />
                    <div v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="amount" value="Amount" />
                    <InputText id="amount" v-model="form.amount" type="number" step="0.01" class="mt-1 block w-full" placeholder="0.00" />
                    <div v-if="form.errors.amount" class="text-red-500 text-sm mt-1">{{ form.errors.amount }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="category_id" value="Category" />
                    <Select id="category_id" v-model="form.category_id" :options="categories" optionLabel="name" optionValue="id" placeholder="Select Category" class="mt-1 w-full" />
                    <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{ form.errors.category_id }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="date" value="Date" />
                    <DatePicker id="date" v-model="form.date" dateFormat="yy-mm-dd" showIcon class="mt-1 w-full" />
                    <div v-if="form.errors.date" class="text-red-500 text-sm mt-1">{{ form.errors.date }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="description" value="Description" />
                    <InputText id="description" v-model="form.description" type="text" class="mt-1 block w-full" placeholder="Description (optional)" />
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

        <!-- Import Modal -->
        <Modal :show="showImportModal" @close="closeImportModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Import Transactions
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Upload a CSV file with columns: Date, Type, Category, Amount, Description.
                </p>

                <div class="mt-6">
                    <InputLabel for="file" value="CSV File" />
                    <input type="file" id="file" @input="importForm.file = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" accept=".csv,.txt" />
                    <div v-if="importForm.errors.file" class="text-red-500 text-sm mt-1">{{ importForm.errors.file }}</div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeImportModal">Cancel</SecondaryButton>
                    <PrimaryButton class="ml-3" @click="submitImport" :disabled="importForm.processing">
                        Import
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
