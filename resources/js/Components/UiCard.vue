<script setup>
import { computed } from 'vue';

const props = defineProps({
    as: { type: String, default: 'div' },
    padding: { type: String, default: 'md' },
});

const paddingClass = computed(() => ({
    none: '',
    sm: 'p-4',
    md: 'p-5 sm:p-6',
    lg: 'p-6 sm:p-8',
}[props.padding] ?? 'p-5 sm:p-6'));
</script>

<template>
    <component :is="as" class="ui-card" :class="paddingClass">
        <slot />
    </component>
</template>
