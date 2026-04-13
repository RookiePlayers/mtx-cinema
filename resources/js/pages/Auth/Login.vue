<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthTextField from '@/Components/auth/AuthTextField.vue';
import { theme } from '@/theme';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login');
};
</script>

<template>
    <Head title="Login" />
    <div class="flex min-h-screen items-center justify-center bg-[#FDFDFC] p-6 text-[#111827] dark:bg-[#0a0a0a] dark:text-[#F9FAFB]">
        <div class="w-full max-w-md rounded-3xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h1 class="text-3xl font-bold">Login</h1>
            <p class="mt-2 text-sm text-gray-500">Sign in to keep your movie activity tied to your account.</p>

            <form class="mt-8 space-y-4" @submit.prevent="submit">
                <AuthTextField
                    id="email"
                    v-model="form.email"
                    label="Email"
                    type="email"
                    autocomplete="email"
                    :error="form.errors.email"
                />

                <AuthTextField
                    id="password"
                    v-model="form.password"
                    label="Password"
                    type="password"
                    autocomplete="current-password"
                    :error="form.errors.password"
                />

                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.remember" type="checkbox" />
                    <span>Remember me</span>
                </label>

                <button
                    type="submit"
                    class="w-full rounded-2xl px-4 py-3 font-semibold text-white"
                    :style="{ backgroundColor: theme.primary }"
                    :disabled="form.processing"
                >
                    Login
                </button>
            </form>

            <p class="mt-6 text-sm text-gray-500">
                Need an account?
                <Link href="/register" class="font-semibold" :style="{ color: theme.primary }">Register</Link>
            </p>
        </div>
    </div>
</template>
