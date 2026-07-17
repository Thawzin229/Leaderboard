<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { BarChart3, LayoutDashboard, LogOut, ReceiptText, Trophy, Users } from '@lucide/vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);

const navItems = [
    { label: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
    { label: 'Users', href: '/users', icon: Users },
    { label: 'Points', href: '/transactions', icon: ReceiptText },
    { label: 'Leaderboard', href: '/leaderboard', icon: Trophy },
];

const isActive = (href) => page.url.startsWith(href);
const logout = () => router.post('/logout');
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <aside class="fixed inset-y-0 left-0 hidden w-64 border-r border-slate-200 bg-white lg:block">
            <div class="flex h-16 items-center gap-3 border-b border-slate-200 px-6">
                <div class="flex size-10 items-center justify-center rounded-lg bg-teal-700 text-white">
                    <BarChart3 class="size-5" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-950">Leaderboard</p>
                    <p class="text-xs text-slate-500">Admin Panel</p>
                </div>
            </div>

            <nav class="space-y-1 px-3 py-4">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition"
                    :class="isActive(item.href) ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950'"
                >
                    <component :is="item.icon" class="size-4" />
                    {{ item.label }}
                </Link>
            </nav>
        </aside>

        <div class="lg:pl-64">
            <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div class="flex min-h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div>
                        <h1 class="text-lg font-semibold text-slate-950">{{ title }}</h1>
                        <p class="text-sm text-slate-500">{{ user?.name }}</p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
                        @click="logout"
                    >
                        <LogOut class="size-4" />
                        Logout
                    </button>
                </div>
                <nav class="flex gap-1 overflow-x-auto border-t border-slate-100 px-4 py-2 lg:hidden">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex shrink-0 items-center gap-2 rounded-md px-3 py-2 text-sm font-medium"
                        :class="isActive(item.href) ? 'bg-teal-50 text-teal-800' : 'text-slate-600'"
                    >
                        <component :is="item.icon" class="size-4" />
                        {{ item.label }}
                    </Link>
                </nav>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                <div v-if="flash.success" class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ flash.success }}
                </div>
                <div v-if="flash.error" class="mb-4 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ flash.error }}
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
