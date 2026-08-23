<script setup>
import { Link } from '@inertiajs/vue3';
import { LoaderCircle } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps({
    href: { type: String, default: null },
    type: { type: String, default: 'button' },
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'md' },
    loading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});

const classes = computed(() => [
    'ui-button',
    {
        primary: 'ui-button-primary',
        secondary: 'ui-button-secondary',
        danger: 'ui-button-danger',
        ghost: 'text-muted hover:bg-brand-50 hover:text-brand-700',
    }[props.variant] ?? 'ui-button-primary',
    {
        sm: 'min-h-9 px-3 py-1.5 text-xs',
        md: '',
        lg: 'min-h-12 px-5 py-3 text-base',
    }[props.size] ?? '',
]);
</script>

<template>
    <Link v-if="href" :href="href" :class="classes" :aria-disabled="disabled || loading">
        <LoaderCircle v-if="loading" class="animate-spin" :size="17" aria-hidden="true" />
        <slot />
    </Link>
    <button v-else :type="type" :disabled="disabled || loading" :class="classes">
        <LoaderCircle v-if="loading" class="animate-spin" :size="17" aria-hidden="true" />
        <slot />
    </button>
</template>
