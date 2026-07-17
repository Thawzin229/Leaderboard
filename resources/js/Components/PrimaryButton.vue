<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60"
        :class="variant === 'danger'
            ? 'bg-rose-700 text-white hover:bg-rose-800'
            : variant === 'secondary'
                ? 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-100'
                : 'bg-teal-700 text-white hover:bg-teal-800'"
    >
        <slot />
    </button>
</template>
