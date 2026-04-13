<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthTextField from '@/Components/auth/AuthTextField.vue';
import { theme } from '@/theme';

const form = useForm({
    name: '',
    email: '',
    password: '',
});

const submit = () => {
    form.post('/register');
};
</script>

<template>
    <Head title="Register" />
    <div class="flex min-h-screen items-center justify-center bg-[#FDFDFC] p-6 text-[#111827] dark:bg-[#0a0a0a] dark:text-[#F9FAFB]">
        <div class="w-full max-w-md rounded-3xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h1 class="text-3xl font-bold">Register</h1>
            <p class="mt-2 text-sm text-gray-500">Create an account to save your searches and personalize the app.</p>

            <form class="mt-8 space-y-4" @submit.prevent="submit">
                <AuthTextField
                    id="name"
                    v-model="form.name"
                    label="Name"
                    autocomplete="name"
                    :error="form.errors.name"
                />

                <AuthTextField
                    id="register-email"
                    v-model="form.email"
                    label="Email"
                    type="email"
                    autocomplete="email"
                    :error="form.errors.email"
                />

                <AuthTextField
                    id="register-password"
                    v-model="form.password"
                    label="Password"
                    type="password"
                    autocomplete="new-password"
                    :error="form.errors.password"
                />

                <button
                    type="submit"
                    class="w-full rounded-2xl px-4 py-3 font-semibold text-white"
                    :style="{ backgroundColor: theme.primary }"
                    :disabled="form.processing"
                >
                    Create account
                </button>
            </form>

            <p class="mt-6 text-sm text-gray-500">
                Already registered?
                <Link href="/login" class="font-semibold" :style="{ color: theme.primary }">Login</Link>
            </p>
        </div>
    </div>
</template>
