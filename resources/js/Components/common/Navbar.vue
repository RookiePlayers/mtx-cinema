<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { theme } from '@/theme';
import type { Auth } from '@/types/auth';

const page = usePage<{ auth: Auth }>();
const user = computed(() => page.props.auth.user);
const logoutForm = useForm({});

const logout = () => {
    logoutForm.post('/logout');
};
</script>
<template>
    <header>
        <nav
            class="p-4 dark:bg-[#0a0a0a]"
            :style="{
                color: theme.primary,
            }"
        >
            <div class="container mx-auto flex items-center justify-between">
                <Link href="/" class="text-2xl font-bold">MTX Cinema</Link>
                <div class="flex items-center gap-4">
                    <Link href="/" class="hover:text-white">Home</Link>
                    <Link href="/search" class="hover:text-white">Search</Link>
                    <template v-if="user">
                        <Link href="/users" class="hover:text-white">{{ user.name }}</Link>
                        <Link href="/movies/create" class="hover:text-white">Manage Movie</Link>
                        <button type="button" class="hover:text-white" @click="logout">
                            Logout
                        </button>
                    </template>
                    <template v-else>
                        <Link href="/login" class="hover:text-white">Login</Link>
                        <Link href="/register" class="hover:text-white">Register</Link>
                    </template>
                </div>
            </div>
        </nav>
    </header>
    <main>
        <slot />
    </main>
</template>
