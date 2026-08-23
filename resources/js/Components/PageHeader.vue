<script setup>
defineProps({
    title: { type: String, required: true },
    description: { type: String, default: null },
    eyebrow: { type: String, default: null },
});
</script>

<template>
    <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <p v-if="eyebrow" class="text-xs font-bold uppercase tracking-[0.13em] text-brand-700">{{ eyebrow }}</p>
            <h1 class="text-2xl font-extrabold tracking-[-0.035em] text-ink sm:text-3xl" :class="eyebrow ? 'mt-2' : ''">{{ title }}</h1>
            <p v-if="description" class="mt-2 max-w-2xl text-sm leading-6 text-muted">{{ description }}</p>
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap gap-2 sm:flex-none">
            <slot name="actions" />
        </div>
    </header>
</template>
