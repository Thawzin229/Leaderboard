<script setup>
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import TextInput from '../../Components/TextInput.vue';

const form = useForm({
    email: 'admin@example.com',
    password: 'password',
    remember: true,
});

const submit = () => form.post('/login');
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-slate-100 px-4">
        <div class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-slate-950">Admin Login</h1>
                <p class="mt-2 text-sm text-slate-500">Sign in to manage users, point transactions, and leaderboard data.</p>
            </div>

            <form class="space-y-5" @submit.prevent="submit">
                <TextInput v-model="form.email" label="Email" type="email" :error="form.errors.email" />
                <TextInput v-model="form.password" label="Password" type="password" :error="form.errors.password" />

                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input v-model="form.remember" type="checkbox" class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                    Remember me
                </label>

                <PrimaryButton type="submit" class="w-full" :disabled="form.processing">Login</PrimaryButton>
            </form>
        </div>
    </div>
</template>
