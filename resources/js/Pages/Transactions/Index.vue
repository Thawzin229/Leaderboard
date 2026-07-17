<template>
    <AppLayout title="Point Management">
        <div class="mb-4 flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
            <form class="grid gap-2 sm:grid-cols-4" @submit.prevent="applyFilters">
                <input v-model="search" type="search" placeholder="Search" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                <select v-model="actionType" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All Types</option>
                    <option value="Earn">Earn</option>
                    <option value="Deduct">Deduct</option>
                </select>
                <select v-model="userId" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="">All Users</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>
                <PrimaryButton type="submit" variant="secondary">Filter</PrimaryButton>
            </form>
            <Link v-if="can.create_transaction" href="/transactions/create" class="inline-flex items-center justify-center gap-2 rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                <Plus class="size-4" />
                Add Points
            </Link>
        </div>

        <div v-if="transactions.data.length" class="overflow-x-auto rounded-md border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3 text-right">Points</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3">Created</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="transaction in transactions.data" :key="transaction.id">
                        <td class="px-5 py-3 font-medium text-slate-950">{{ transaction.user.name }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-md px-2 py-1 text-xs font-semibold" :class="transaction.action_type === 'Earn' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
                                {{ transaction.action_type }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right font-semibold">{{ transaction.points }}</td>
                        <td class="px-5 py-3">{{ transaction.description || '-' }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ new Date(transaction.created_at).toLocaleString() }}</td>
                        <td class="px-5 py-3">
                            <div class="flex justify-end gap-2">
                                <Link v-if="can.edit_transaction" :href="`/transactions/${transaction.id}/edit`" class="rounded-md border border-slate-300 p-2 text-slate-600 hover:bg-slate-100" title="Edit">
                                    <Pencil class="size-4" />
                                </Link>
                                <button v-if="can.delete_transaction" type="button" class="rounded-md border border-rose-200 p-2 text-rose-700 hover:bg-rose-50" title="Delete" @click="destroy(transaction)">
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <EmptyState v-else title="No transactions found" message="Record point changes or adjust the active filters." />
        <Pagination :links="transactions.links" />
    </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from '@lucide/vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import EmptyState from '../../Components/EmptyState.vue';
import Pagination from '../../Components/Pagination.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';

const page = usePage();
const can = page.props.can;

const props = defineProps({
    transactions: Object,
    filters: Object,
    users: Array,
});

const search = ref(props.filters.search || '');
const actionType = ref(props.filters.action_type || '');
const userId = ref(props.filters.user_id || '');

const applyFilters = () => router.get('/transactions', {
    search: search.value,
    action_type: actionType.value,
    user_id: userId.value,
}, { preserveState: true, replace: true });

const destroy = (transaction) => {
    if (confirm('Delete this point transaction? The user total will be recalculated.')) {
        router.delete(`/transactions/${transaction.id}`);
    }
};
</script>