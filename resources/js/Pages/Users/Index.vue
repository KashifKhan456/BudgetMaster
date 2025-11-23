<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import InputText from 'primevue/inputtext';
import { formatDate } from '@/Utils/date';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    users: Array,
    filters: Object,
});

const filters = ref({
    search: props.filters?.search || '',
});

const loading = ref(false);

const updateParams = debounce((newFilters) => {
    router.get(route('users.index'), newFilters, {
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

const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        useForm({}).delete(route('users.destroy', id));
    }
};
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Users</h2>
                <Link :href="route('users.create')" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    Add User
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-[#1a1a1a] overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Filters -->
                        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <InputText 
                                    id="filter_search" 
                                    v-model="filters.search" 
                                    type="text" 
                                    class="mt-1 block w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                                    placeholder="Search users..." 
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
                                <thead class="bg-gray-50 dark:bg-[#1a1a1a]">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Joined</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-[#1a1a1a] divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="user in users" :key="user.id">
                                        <td class="px-6 py-2 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ user.name }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ user.email }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap">
                                            <span v-for="role in user.roles" :key="role" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 mr-1">
                                                {{ role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-2 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ formatDate(user.created_at) }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('users.edit', user.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 mr-4">Edit</Link>
                                            <button @click="deleteUser(user.id)" class="text-red-600 dark:text-red-400 hover:text-red-900">Delete</button>
                                        </td>
                                    </tr>
                                    <tr v-if="users.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
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
    </AuthenticatedLayout>
</template>
