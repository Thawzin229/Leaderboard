<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div v-if="links.length > 3" class="mt-5 flex flex-wrap gap-2">
        <component
            :is="link.url ? Link : 'span'"
            v-for="link in links"
            :key="link.label"
            :href="link.url || undefined"
            class="rounded-md border px-3 py-2 text-sm"
            :class="link.active
                ? 'border-teal-700 bg-teal-700 text-white'
                : link.url
                    ? 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100'
                    : 'border-slate-200 bg-slate-100 text-slate-400'"
            v-html="link.label"
        />
    </div>
</template>
