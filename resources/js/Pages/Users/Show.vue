<script setup>
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import EmptyState from '../../Components/EmptyState.vue';

const page = usePage();
const can = page.props.can;

defineProps({
    user: Object,
});
</script>

<template>
    <AppLayout title="User Detail">
        <div class="mb-5 rounded-md border border-slate-200 bg-white p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950">{{ user.name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ user.email }}</p>
                </div>
                <div class="flex gap-2" v-if="can.manage_transactions">
                    <Link :href="`/transactions/create?user_id=${user.id}`" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Add Transaction</Link>
                    <Link :href="`/users/${user.id}/edit`" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Edit</Link>
                </div>
            </div>
            <dl class="mt-5 grid gap-4 sm:grid-cols-3">
                <div>
                    <dt class="text-sm text-slate-500">Total Points</dt>
                    <dd class="mt-1 text-2xl font-semibold text-slate-950">{{ user.total_points }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500">Role</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-950">{{ user.is_admin ? 'Admin' : 'User' }}</dd>
                </div>
            </dl>
        </div>

        <section class="rounded-md border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-semibold text-slate-950">Recent Point History</h2>
            </div>
            <div v-if="user.point_transactions.length" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Type</th>
                            <th class="px-5 py-3 text-right">Points</th>
                            <th class="px-5 py-3">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="transaction in user.point_transactions" :key="transaction.id">
                            <td class="px-5 py-3 text-slate-500">{{ new Date(transaction.created_at).toLocaleString() }}</td>
                            <td class="px-5 py-3">{{ transaction.action_type }}</td>
                            <td class="px-5 py-3 text-right font-semibold">{{ transaction.points }}</td>
                            <td class="px-5 py-3">{{ transaction.description || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="p-5">
                <EmptyState title="No point history" message="This user has no point transactions yet." />
            </div>
        </section>
    </AppLayout>
</template>
