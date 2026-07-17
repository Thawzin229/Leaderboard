<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import TextInput from '../../Components/TextInput.vue';

const page = usePage();
const can = page.props.can;

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    password: '',
    password_confirmation: '',
    is_admin: Boolean(props.user?.is_admin),
});

const submit = () => {
    if (props.user) {
        form.put(`/users/${props.user.id}`);
    } else {
        form.post('/users');
    }
};
</script>

<template>
    <AppLayout :title="user ? 'Edit User' : 'Create User'">
        <form class="max-w-2xl space-y-5 rounded-md border border-slate-200 bg-white p-5" @submit.prevent="submit">
            <TextInput v-model="form.name" label="Name" :error="form.errors.name" />
            <TextInput v-model="form.email" label="Email" type="email" :error="form.errors.email" />
            <TextInput v-model="form.password" label="Password" type="password" :error="form.errors.password" />
            <TextInput v-model="form.password_confirmation" label="Confirm Password" type="password" :error="form.errors.password_confirmation" />

            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input v-model="form.is_admin" type="checkbox" class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                Administrator access
            </label>

            <div class="flex gap-3">
                <PrimaryButton type="submit" :disabled="form.processing || !can.edit_user">{{ user ? 'Update User' : 'Create User' }}</PrimaryButton>
                <Link href="/users" class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Cancel</Link>
            </div>
        </form>
    </AppLayout>
</template>
