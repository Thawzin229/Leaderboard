<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { Download } from '@lucide/vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import EmptyState from '../../Components/EmptyState.vue';
import Pagination from '../../Components/Pagination.vue';

const page = usePage();
const can = page.props.can;

const props = defineProps({
    leaderboard: Object,
    filters: Object,
});

const setFilter = (filter) => {
    router.get('/leaderboard', { filter }, { preserveState: true, replace: true });
};

const filterItems = [
    { label: 'Today', value: 'today' },
    { label: 'This Week', value: 'week' },
    { label: 'This Month', value: 'month' },
    { label: 'All Time', value: 'all' },
];

const handleexport= () => {
        const params = new URLSearchParams();
        const link = document.createElement('a');
        link.href = `/leaderboard/export?filter=${props.filters.filter}`;
        link.download = `leaderboard-${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

<template>
    <AppLayout title="Leaderboard">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="inline-flex rounded-md border border-slate-300 bg-white p-1">
                <button
                    v-for="item in filterItems"
                    :key="item.value"
                    type="button"
                    class="rounded px-3 py-2 text-sm font-medium"
                    :class="filters.filter === item.value ? 'bg-teal-700 text-white' : 'text-slate-600 hover:bg-slate-100'"
                    @click="setFilter(item.value)"
                >
                    {{ item.label }}
                </button>
            </div>
            <div  v-if="can.export_data" @click="handleexport" class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                <Download class="size-4" />
                Export CSV
            </div>
        </div>

        <div v-if="leaderboard.data.length" class="overflow-x-auto rounded-md border border-slate-200 bg-white">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Rank</th>
                        <th class="px-5 py-3">User Name</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3 text-right">Total Points</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="row in leaderboard.data" :key="row.id">
                        <td class="px-5 py-3 text-lg font-semibold text-slate-950">{{ row.rank }}</td>
                        <td class="px-5 py-3 font-medium text-slate-950">{{ row.name }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ row.email }}</td>
                        <td class="px-5 py-3 text-right font-semibold">{{ row.points }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <EmptyState v-else title="No leaderboard rows" message="Users will appear here after they are created." />
        <Pagination :links="leaderboard.links" />
    </AppLayout>
</template>
