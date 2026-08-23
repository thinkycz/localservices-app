<script setup>
import { computed } from 'vue';

const props = defineProps({
    tone: { type: String, default: 'neutral' },
});

const toneClass = computed(() => ({
    neutral: 'bg-gray-100 text-gray-700 ring-gray-500/15',
    brand: 'bg-brand-50 text-brand-800 ring-brand-600/20',
    success: 'bg-green-50 text-green-800 ring-green-600/20',
    warning: 'bg-amber-50 text-amber-800 ring-amber-600/20',
    danger: 'bg-red-50 text-danger ring-red-600/20',
}[props.tone] ?? 'bg-gray-100 text-gray-700 ring-gray-500/15'));
</script>

<template>
    <span class="inline-flex min-h-6 items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset" :class="toneClass">
        <slot />
    </span>
</template>
