<script setup>
import { MessageCircleCheck } from '@lucide/vue';

defineProps({
    inverse: {
        type: Boolean,
        default: false,
    },
    compact: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <span class="inline-flex min-w-0 items-center gap-2.5" aria-label="Domluveno">
        <span
            class="flex h-10 w-10 flex-none items-center justify-center rounded-xl"
            :class="inverse ? 'bg-white text-brand-700' : 'bg-brand-600 text-white'"
            aria-hidden="true"
        >
            <MessageCircleCheck :size="22" :stroke-width="2.25" />
        </span>
        <span
            v-if="!compact"
            class="truncate text-lg font-extrabold tracking-[-0.035em]"
            :class="inverse ? 'text-white' : 'text-ink'"
        >Domluveno</span>
    </span>
</template>
