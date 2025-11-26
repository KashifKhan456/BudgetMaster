<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
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
import Pagination from '@/Components/Pagination.vue';

import debounce from 'lodash/debounce';

const props = defineProps({
    transactions: Object,
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

const loading = ref(false);
const showFilters = ref(false);

const updateParams = debounce((newFilters) => {
    const params = { ...newFilters };
    if (params.start_date instanceof Date) {
        params.start_date = params.start_date.toISOString().split('T')[0];
    }
    if (params.end_date instanceof Date) {
        params.end_date = params.end_date.toISOString().split('T')[0];
    }
    router.get(route('transactions.index'), params, {
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
    type: 'expense',
    date: new Date().toISOString().split('T')[0],
    description: '',
    splits: [],
});

const isSplit = ref(false);

const addSplit = () => {
    form.splits.push({ category_id: '', amount: 0 });
};

const removeSplit = (index) => {
    form.splits.splice(index, 1);
};

const splitTotal = computed(() => {
    return form.splits.reduce((sum, split) => sum + Number(split.amount), 0);
});

const editingTransaction = ref(null);
const showModal = ref(false);

const openModal = (transaction = null) => {
    if (transaction) {
        editingTransaction.value = transaction;
        form.category_id = transaction.category_id;
        form.amount = transaction.amount;
        form.type = transaction.type;
        form.date = new Date(transaction.date);
        form.description = transaction.description;

        if (transaction.splits && transaction.splits.length > 0) {
            isSplit.value = true;
            form.splits = transaction.splits.map(split => ({
                category_id: split.category_id,
                amount: split.amount
            }));
        } else {
            isSplit.value = false;
            form.splits = [];
        }
    } else {
        editingTransaction.value = null;
        form.reset();
        form.date = new Date().toISOString().split('T')[0];
        isSplit.value = false;
        form.splits = [];
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingTransaction.value = null;
    isSplit.value = false;
};

const submit = () => {
    const data = form.data();
    if (data.date instanceof Date) {
        data.date = data.date.toISOString().split('T')[0];
    }

    if (!isSplit.value) {
        data.splits = [];
    }

    if (editingTransaction.value) {
        form.transform(() => data).put(route('transactions.update', editingTransaction.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.transform(() => data).post(route('transactions.store'), {
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
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 sm:gap-0">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Transactions</h2>
                <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                    <Button icon="pi pi-filter" :label="showFilters ? 'Hide Filters' : 'Filters'"
                        @click="showFilters = !showFilters" severity="secondary" outlined
                        class="flex-1 sm:flex-none justify-center" />
                    <button @click="openImportModal"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-[#1a1a1a] border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150 flex-1 sm:flex-none justify-center">
                        Import CSV
                    </button>
                    <a :href="route('export.transactions')"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-[#1a1a1a] border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150 flex-1 sm:flex-none justify-center">
                        Export CSV
                    </a>
                    <PrimaryButton @click="openModal()" class="flex-1 sm:flex-none justify-center">Add Transaction
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Filters -->
                        <div v-if="showFilters" class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <InputLabel for="filter_search" value="Search" />
                                <InputText id="filter_search" v-model="filters.search" type="text"
                                    class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    placeholder="Search description..." />
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
                            <div>
                                <InputLabel for="filter_category" value="Category" />
                                <Select id="filter_category" v-model="filters.category_id" :options="categories"
                                    optionLabel="name" optionValue="id" placeholder="All Categories"
                                    class="mt-1 w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    showClear />
                            </div>
                            <div>
                                <InputLabel for="filter_type" value="Type" />
                                <Select id="filter_type" v-model="filters.type"
                                    :options="[{ label: 'Expense', value: 'expense' }, { label: 'Income', value: 'income' }]"
                                    optionLabel="label" optionValue="value" placeholder="All Types"
                                    class="mt-1 w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    showClear />
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
                                            Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Description</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Category</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Amount</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-[#1a1a1a] divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="transaction in transactions.data" :key="transaction.id">
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{
                                                formatDate(transaction.date) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ transaction.description ||
                                            '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-if="transaction.splits && transaction.splits.length > 0"
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                Split ({{ transaction.splits.length }})
                                            </span>
                                            <span v-else
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :style="{ backgroundColor: transaction.category?.color + '20', color: transaction.category?.color }">
                                                {{ transaction.category?.name || 'Uncategorized' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold"
                                            :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                            {{ transaction.type === 'income' ? '+' : '-' }}${{ transaction.amount }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="openModal(transaction)"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 mr-4">Edit</button>
                                            <button @click="deleteTransaction(transaction.id)"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                    <tr v-if="transactions.data.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No records found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6">
                            <Pagination :links="transactions.links" />
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
                    <Select id="type" v-model="form.type"
                        :options="[{ label: 'Expense', value: 'expense' }, { label: 'Income', value: 'income' }]"
                        optionLabel="label" optionValue="value" appendTo="body" overlayClass="!z-[9999]"
                        class="mt-1 w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                    <div v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="amount" value="Amount" />
                    <InputText id="amount" v-model="form.amount" type="number" step="0.01"
                        class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        placeholder="0.00" />
                    <div v-if="form.errors.amount" class="text-red-500 text-sm mt-1">{{ form.errors.amount }}</div>
                </div>

                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <InputLabel for="category_id" value="Category" />
                        <div class="flex items-center">
                            <input id="is_split" type="checkbox" v-model="isSplit"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-900 dark:border-gray-600">
                            <label for="is_split" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Split
                                Transaction</label>
                        </div>
                    </div>

                    <div v-if="!isSplit">
                        <Select id="category_id" v-model="form.category_id" :options="categories" optionLabel="name"
                            optionValue="id" placeholder="Select Category" appendTo="body" overlayClass="!z-[9999]"
                            class="mt-1 w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                        <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{ form.errors.category_id
                            }}
                        </div>
                    </div>

                    <div v-else class="space-y-3">
                        <div v-for="(split, index) in form.splits" :key="index" class="flex items-center space-x-2">
                            <div class="flex-grow">
                                <Select v-model="split.category_id" :options="categories" optionLabel="name"
                                    optionValue="id" placeholder="Category" appendTo="body" overlayClass="!z-[9999]"
                                    class="w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                                <div v-if="form.errors[`splits.${index}.category_id`]"
                                    class="text-red-500 text-xs mt-1">{{
                                        form.errors[`splits.${index}.category_id`] }}</div>
                            </div>
                            <div class="w-1/3">
                                <InputText v-model="split.amount" type="number" step="0.01" placeholder="Amount"
                                    class="w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" />
                                <div v-if="form.errors[`splits.${index}.amount`]" class="text-red-500 text-xs mt-1">{{
                                    form.errors[`splits.${index}.amount`] }}</div>
                            </div>
                            <button @click="removeSplit(index)"
                                class="text-red-500 hover:text-red-700 text-sm font-medium px-2">
                                Remove
                            </button>
                        </div>

                        <div class="flex justify-between items-center mt-2">
                            <button @click="addSplit" type="button"
                                class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                + Add Split
                            </button>
                            <div class="text-sm"
                                :class="{ 'text-red-500': splitTotal > form.amount, 'text-green-500': splitTotal <= form.amount }">
                                Total: ${{ splitTotal.toFixed(2) }} / ${{ Number(form.amount).toFixed(2) }}
                            </div>
                        </div>
                        <div v-if="splitTotal > form.amount" class="text-red-500 text-xs text-right">
                            Splits cannot exceed total amount.
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <InputLabel for="date" value="Date" />
                    <DatePicker id="date" v-model="form.date" dateFormat="yy-mm-dd" showIcon showOnFocus appendTo="body"
                        panelClass="!z-[9999]" class="mt-1 w-full"
                        inputClass="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600" />
                    <div v-if="form.errors.date" class="text-red-500 text-sm mt-1">{{ form.errors.date }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="description" value="Description" />
                    <InputText id="description" v-model="form.description" type="text"
                        class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        placeholder="Description (optional)" />
                    <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}
                    </div>
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
                    <input type="file" id="file" @input="importForm.file = $event.target.files[0]"
                        class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                        accept=".csv,.txt" />
                    <div v-if="importForm.errors.file" class="text-red-500 text-sm mt-1">{{ importForm.errors.file }}
                    </div>
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
