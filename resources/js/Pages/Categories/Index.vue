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
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';

import debounce from 'lodash/debounce';

const props = defineProps({
    categories: Array,
    filters: Object,
});

const filters = ref({
    search: props.filters?.search || '',
    type: props.filters?.type || '',
});

const loading = ref(false);

const updateParams = debounce((newFilters) => {
    router.get(route('categories.index'), newFilters, {
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
    type: 'expense',
    color: '#000000',
});

const editingCategory = ref(null);
const showModal = ref(false);

const openModal = (category = null) => {
    if (category) {
        editingCategory.value = category;
        form.name = category.name;
        form.type = category.type;
        form.color = category.color;
    } else {
        editingCategory.value = null;
        form.reset();
        form.type = 'expense'; // Default
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingCategory.value = null;
};

const submit = () => {
    if (editingCategory.value) {
        form.put(route('categories.update', editingCategory.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('categories.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCategory = (id) => {
    if (confirm('Are you sure you want to delete this category?')) {
        useForm({}).delete(route('categories.destroy', id));
    }
};

</script>

<template>
    <Head title="Categories" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Categories</h2>
                <PrimaryButton @click="openModal()">Add Category</PrimaryButton>
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
                                <InputText 
                                    id="filter_search" 
                                    v-model="filters.search" 
                                    type="text" 
                                    class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                                    placeholder="Search name..." 
                                />
                            </div>
                            <div>
                                <InputLabel for="filter_type" value="Type" />
                                <Select 
                                    id="filter_type" 
                                    v-model="filters.type" 
                                    :options="[{label: 'Expense', value: 'expense'}, {label: 'Income', value: 'income'}]" 
                                    optionLabel="label" 
                                    optionValue="value" 
                                    placeholder="All Types" 
                                    class="mt-1 w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                                    showClear 
                                />
                            </div>
                        </div>

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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Color</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-[#1a1a1a] divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="category in categories" :key="category.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ category.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap capitalize">{{ category.type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="w-6 h-6 rounded-full border border-gray-200 dark:border-gray-600" :style="{ backgroundColor: category.color }"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="openModal(category)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 mr-4">Edit</button>
                                            <button @click="deleteCategory(category.id)" class="text-red-600 dark:text-red-400 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                    <tr v-if="categories.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No records found.
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
                    {{ editingCategory ? 'Edit Category' : 'Add Category' }}
                </h2>

                <div class="mt-6">
                    <InputLabel for="name" value="Name" />
                    <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" placeholder="Category Name" />
                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="type" value="Type" />
                    <select id="type" v-model="form.type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="expense">Expense</option>
                        <option value="income">Income</option>
                    </select>
                    <div v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</div>
                </div>

                <div class="mt-4">
                    <InputLabel for="color" value="Color" />
                    <TextInput id="color" v-model="form.color" type="color" class="mt-1 block w-full h-10 p-1" />
                    <div v-if="form.errors.color" class="text-red-500 text-sm mt-1">{{ form.errors.color }}</div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton class="ml-3" @click="submit" :disabled="form.processing">
                        {{ editingCategory ? 'Update' : 'Create' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
