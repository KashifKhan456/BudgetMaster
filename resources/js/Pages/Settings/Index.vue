<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import { currencies } from '@/Utils/currency';
import Select from 'primevue/select';
import { computed } from 'vue';

const user = usePage().props.auth.user;

const form = useForm({
    currency: user.currency || 'USD',
});

const currencyOptions = computed(() => {
    return currencies.map(c => ({
        ...c,
        label: `${c.name} (${c.symbol})`
    }));
});

const submit = () => {
    form.patch(route('settings.update'), {
        preserveScroll: true,
        showProgress: false,
    });
};
</script>

<template>

    <Head title="Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Settings</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-[#1a1a1a] shadow sm:rounded-lg">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Application Settings
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Update your application preferences.
                            </p>
                        </header>

                        <form @submit.prevent="submit" class="mt-6 space-y-6">
                            <div>
                                <InputLabel for="currency" value="Currency" />

                                <Select id="currency" v-model="form.currency" :options="currencyOptions"
                                    optionLabel="label" optionValue="code" filter placeholder="Select a Currency"
                                    class="mt-1 w-full border-gray-300 dark:border-[#404040] dark:bg-[#262626] dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    :pt="{
                                        root: { class: 'dark:bg-[#262626] dark:border-[#404040]' },
                                        input: { class: 'dark:text-gray-300' },
                                        panel: { class: 'dark:bg-[#262626] dark:border-[#404040]' },
                                        item: { class: 'dark:text-gray-300 hover:dark:bg-[#404040] dark:focus:bg-[#404040]' },
                                        filterInput: { class: 'dark:bg-[#262626] dark:border-[#404040] dark:text-gray-300' },
                                        filterIcon: { class: 'dark:text-gray-400' }
                                    }" />

                                <InputError class="mt-2" :message="form.errors.currency" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
