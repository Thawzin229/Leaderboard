<script setup>
import AppLayout from '../Layouts/AppLayout.vue';
import EmptyState from '../Components/EmptyState.vue';

defineProps({
    stats: Object,
    topUsers: Array,
});
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-md border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">Total Users</p>
                <p class="mt-2 text-2xl font-semibold text-slate-950">{{ stats.totalUsers }}</p>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">Total Points</p>
                <p class="mt-2 text-2xl font-semibold text-slate-950">{{ stats.totalPoints }}</p>
            </div>
        </div>

        <section class="mt-6 rounded-md border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-semibold text-slate-950">Top 10 Users</h2>
            </div>
            <div v-if="topUsers.length" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Rank</th>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3 text-right">Points</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="user in topUsers" :key="user.id">
                            <td class="px-5 py-3 font-semibold text-slate-950">{{ user.rank }}</td>
                            <td class="px-5 py-3">{{ user.name }}</td>
                            <td class="px-5 py-3 text-right font-semibold">{{ user.points }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="p-5">
                <EmptyState title="No leaderboard data" message="Create users and point transactions to populate the dashboard." />
            </div>
        </section>
    </AppLayout>
</template>
