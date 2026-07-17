<template>
    <AppLayout title="User Management">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <form class="flex gap-2" @submit.prevent="applyFilters">
                <input v-model="search" type="search" placeholder="Search users" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm sm:w-80">
                <PrimaryButton type="submit" variant="secondary">Search</PrimaryButton>
            </form>
            <Link v-if="can.create_user" href="/users/create" class="inline-flex items-center justify-center gap-2 rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                <Plus class="size-4" />
                Create User
            </Link>
        </div>

        <div v-if="users.data.length" class="overflow-x-auto rounded-md border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3 text-right">Total Points</th>
                        <th class="px-5 py-3 text-right">Transactions</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="user in users.data" :key="user.id">
                        <td class="px-5 py-3 font-medium text-slate-950">{{ user.name }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ user.email }}</td>
                        <td class="px-5 py-3 text-right font-semibold">{{ user.total_points }}</td>
                        <td class="px-5 py-3 text-right">{{ user.point_transactions_count }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-md px-2 py-1 text-xs font-semibold" :class="user.is_admin ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700'">
                                {{ user.is_admin ? 'Admin' : 'User' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex justify-end gap-2">
                                <Link :href="`/users/${user.id}`" class="rounded-md border border-slate-300 p-2 text-slate-600 hover:bg-slate-100" title="View">
                                    <Eye class="size-4" />
                                </Link>
                                <Link v-if="can.edit_user" :href="`/users/${user.id}/edit`" class="rounded-md border border-slate-300 p-2 text-slate-600 hover:bg-slate-100" title="Edit">
                                    <Pencil class="size-4" />
                                </Link>
                                <button v-if="can.delete_user && auth.user.id !== user.id" type="button" class="rounded-md border border-rose-200 p-2 text-rose-700 hover:bg-rose-50" title="Delete" @click="destroy(user)">
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <EmptyState v-else title="No users found" message="Create a user or adjust the search filter." />
        <Pagination :links="users.links" />
    </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Pencil, Trash2, Eye } from '@lucide/vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import EmptyState from '../../Components/EmptyState.vue';
import Pagination from '../../Components/Pagination.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';

const page = usePage();
const can = page.props.can;
const auth = page.props.auth;

const props = defineProps({
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

const applyFilters = () => router.get('/users', { search: search.value }, {
    preserveState: true,
    replace: true,
});

const destroy = (user) => {
    if (confirm(`Delete ${user.name}? This also removes their point history.`)) {
        router.delete(`/users/${user.id}`);
    }
};
</script>
