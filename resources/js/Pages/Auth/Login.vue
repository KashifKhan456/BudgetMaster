<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-400">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" class="text-gray-300" />

                <TextInput id="email" type="email"
                    class="mt-1 block w-full bg-white/5 border-white/10 text-white focus:border-indigo-500 focus:ring-indigo-500 placeholder-gray-500"
                    v-model="form.email" required autofocus autocomplete="username" placeholder="name@example.com" />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" class="text-gray-300" />

                <TextInput id="password" type="password"
                    class="mt-1 block w-full bg-white/5 border-white/10 text-white focus:border-indigo-500 focus:ring-indigo-500 placeholder-gray-500"
                    v-model="form.password" required autocomplete="current-password" placeholder="••••••••" />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember"
                        class="bg-gray-800 border-gray-600 text-indigo-500 focus:ring-indigo-500" />
                    <span class="ms-2 text-sm text-gray-400">Remember me</span>
                </label>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <Link v-if="canResetPassword" :href="route('password.request')"
                    class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
                Forgot password?
                </Link>

                <PrimaryButton
                    class="ms-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 border-0 focus:ring-indigo-500"
                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Log in
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-700"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-[#111827] text-gray-400">Or continue with</span>
                </div>
            </div>

            <div class="mt-6">
                <a :href="route('auth.google')"
                    class="w-full flex items-center justify-center px-4 py-2 border border-gray-700 rounded-md shadow-sm text-sm font-medium text-gray-300 bg-gray-800 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <svg class="h-5 w-5 mr-2" aria-hidden="true" viewBox="0 0 24 24">
                        <path
                            d="M12.0003 20.45c4.6667 0 8.5417-3.2917 9.9583-7.9167h-9.9583v-3.75h14.5c.2083 1.0417.3333 2.125.3333 3.25 0 6.625-4.5 11.6667-11.6667 11.6667-6.625 0-12-5.375-12-12s5.375-12 12-12c3.25 0 6.1667 1.1667 8.4583 3.2917l-3.5416 3.5416c-1.25-1.2083-2.9167-1.9166-4.9167-1.9166-4.1667 0-7.5833 3.4167-7.5833 7.5833s3.4166 7.5833 7.5833 7.5833z"
                            fill="currentColor" />
                    </svg>
                    Google
                </a>
            </div>
        </div>
    </GuestLayout>
</template>
