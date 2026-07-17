<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <div class="rounded-md border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
        <p class="text-sm font-semibold text-slate-900">{{ title }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ message }}</p>
    </div>
</template>
