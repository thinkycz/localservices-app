<script setup>
import { Inbox } from '@lucide/vue';

defineProps({
    title: { type: String, required: true },
    description: { type: String, default: null },
});
</script>

<template>
    <div class="rounded-2xl border border-dashed border-line bg-white px-5 py-12 text-center sm:px-8">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-700" aria-hidden="true">
            <slot name="icon"><Inbox :size="23" /></slot>
        </div>
        <h2 class="mt-4 text-base font-extrabold text-ink">{{ title }}</h2>
        <p v-if="description" class="mx-auto mt-2 max-w-md text-sm leading-6 text-muted">{{ description }}</p>
        <div v-if="$slots.actions" class="mt-5 flex flex-wrap justify-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
