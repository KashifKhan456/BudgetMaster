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
import InputText from 'primevue/inputtext';
import DatePicker from 'primevue/datepicker';

import debounce from 'lodash/debounce';

const props = defineProps({
    goals: Array,
    filters: Object,
});

const filters = ref({
    search: props.filters?.search || '',
});

const loading = ref(false);

const updateParams = debounce((newFilters) => {
    router.get(route('goals.index'), newFilters, {
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
    name: '',
    target_amount: '',
    target_date: '',
});

const addFundsForm = useForm({
    add_amount: '',
});

const editingGoal = ref(null);
const addingFundsGoal = ref(null);
const showModal = ref(false);
const showAddFundsModal = ref(false);

const openModal = (goal = null) => {
    if (goal) {
        editingGoal.value = goal;
        form.name = goal.name;
        form.target_amount = goal.target_amount;
        form.target_date = new Date(goal.target_date);
    } else {
        editingGoal.value = null;
        form.reset();
    }
    showModal.value = true;
};

const openAddFundsModal = (goal) => {
    addingFundsGoal.value = goal;
    addFundsForm.reset();
    showAddFundsModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    showAddFundsModal.value = false;
    form.reset();
    addFundsForm.reset();
    editingGoal.value = null;
    addingFundsGoal.value = null;
};

const submit = () => {
    const data = form.data();
    if (data.target_date instanceof Date) {
        data.target_date = data.target_date.toISOString().split('T')[0];
    }

    if (editingGoal.value) {
        form.transform(() => data).put(route('goals.update', editingGoal.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.transform(() => data).post(route('goals.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const submitAddFunds = () => {
    if (addingFundsGoal.value) {
        addFundsForm.put(route('goals.update', addingFundsGoal.value.id), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteGoal = (id) => {
    if (confirm('Are you sure you want to delete this savings goal?')) {
        useForm({}).delete(route('goals.destroy', id));
    }
};

const calculateProgress = (current, target) => {
    if (target <= 0) return 0;
    const percentage = (current / target) * 100;
    return Math.min(percentage, 100).toFixed(1);
};

</script>

<template>
    <Head title="Savings Goals" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Savings Goals</h2>
                <PrimaryButton @click="openModal()">Add Goal</PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <InputLabel for="filter_search" value="Search" />
                        <InputText 
                            id="filter_search" 
                            v-model="filters.search" 
                            type="text" 
                            class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                            placeholder="Search goals..." 
                        />
                    </div>
                </div>

                <div class="relative">
                    <div v-if="loading" class="absolute inset-0 bg-white/70 dark:bg-black/70 flex items-center justify-center z-50 rounded-lg">
                        <svg class="animate-spin h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="goal in goals" :key="goal.id" class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ goal.name }}</h3>
                            <div class="flex space-x-2">
                                <button @click="openModal(goal)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 text-sm">Edit</button>
                                <button @click="deleteGoal(goal.id)" class="text-red-600 dark:text-red-400 hover:text-red-900 text-sm">Delete</button>
                            </div>
                        </div>

                        <div class="mb-2 flex justify-between text-sm text-gray-600 dark:text-gray-400">
                            <span>${{ goal.current_amount }} saved</span>
                            <span>Target: ${{ goal.target_amount }}</span>
                        </div>

                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-[#1a1a1a] mb-4">
                            <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: calculateProgress(goal.current_amount, goal.target_amount) + '%' }"></div>
                        </div>

                        <div class="text-right text-xs text-gray-500 mb-4">
                            Target Date: {{ formatDate(goal.target_date) }}
                        </div>

                        <PrimaryButton @click="openAddFundsModal(goal)" class="w-full justify-center">
                            Add Funds
                        </PrimaryButton>
                    </div>
                </div>
                
                <div v-if="goals.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-12">
                    No savings goals yet. Create one to start saving!
                </div>
            </div>
        </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ editingGoal ? 'Edit Goal' : 'Add Goal' }}
                </h2>

                <div class="mt-6">
                    <InputLabel for="name" value="Goal Name" />
                    <InputText 
                        id="name" 
                        v-model="form.name" 
                        type="text" 
                        class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                        placeholder="e.g. New Car" 
                    />
                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="target_amount" value="Target Amount" />
                    <InputText 
                        id="target_amount" 
                        v-model="form.target_amount" 
                        type="number" 
                        step="0.01" 
                        class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                        placeholder="0.00" 
                    />
                    <div v-if="form.errors.target_amount" class="text-red-500 text-sm mt-1">{{ form.errors.target_amount }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="target_date" value="Target Date" />
                    <DatePicker 
                        id="target_date" 
                        v-model="form.target_date" 
                        dateFormat="yy-mm-dd" 
                        showIcon 
                        showOnFocus 
                        appendTo="body" 
                        panelClass="!z-[9999]"
                        class="mt-1 w-full" 
                        inputClass="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600" 
                    />
                    <div v-if="form.errors.target_date" class="text-red-500 text-sm mt-1">{{ form.errors.target_date }}</div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton class="ml-3" @click="submit" :disabled="form.processing">
                        {{ editingGoal ? 'Update' : 'Create' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Add Funds Modal -->
        <Modal :show="showAddFundsModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Add Funds to {{ addingFundsGoal?.name }}
                </h2>

                <div class="mt-6">
                    <InputLabel for="add_amount" value="Amount to Add" />
                    <InputText 
                        id="add_amount" 
                        v-model="addFundsForm.add_amount" 
                        type="number" 
                        step="0.01" 
                        class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                        placeholder="0.00" 
                    />
                    <div v-if="addFundsForm.errors.add_amount" class="text-red-500 text-sm mt-1">{{ addFundsForm.errors.add_amount }}</div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton class="ml-3" @click="submitAddFunds" :disabled="addFundsForm.processing">
                        Add Funds
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
