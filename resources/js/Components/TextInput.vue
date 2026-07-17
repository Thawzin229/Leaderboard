<script setup>
defineProps({
    label: String,
    error: String,
    modelValue: [String, Number],
    type: {
        type: String,
        default: 'text',
    },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <label class="block">
        <span class="mb-1 block text-sm font-medium text-slate-700">{{ label }}</span>
        <input
            :type="type"
            :value="modelValue"
            class="block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-950 shadow-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
            @input="$emit('update:modelValue', $event.target.value)"
        >
        <span v-if="error" class="mt-1 block text-sm text-rose-700">{{ error }}</span>
    </label>
</template>
