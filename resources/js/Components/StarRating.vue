<script setup>
import { computed } from 'vue';
import { Star } from '@lucide/vue';

const props = defineProps({
    rating: { type: Number, default: 0 },
    max: { type: Number, default: 5 },
    size: { type: String, default: 'sm' },
});

const sizeClass = computed(() => ({
    sm: 'h-4 w-4',
    md: 'h-5 w-5',
    lg: 'h-6 w-6',
}[props.size] ?? 'h-4 w-4'));
const roundedRating = computed(() => Math.round(props.rating));
</script>

<template>
    <div class="flex items-center gap-0.5" role="img" :aria-label="`${rating} z ${max} hvězdiček`">
        <Star
            v-for="i in max"
            :key="i"
            :class="[sizeClass, i <= roundedRating ? 'fill-accent text-accent' : 'text-line']"
            aria-hidden="true"
        />
    </div>
</template>
