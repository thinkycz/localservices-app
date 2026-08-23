<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle2, Info, X } from '@lucide/vue';

const page = usePage();
const dismissed = ref(new Set());

const tones = {
    success: { icon: CheckCircle2, classes: 'border-green-200 bg-green-50 text-green-900' },
    error: { icon: AlertCircle, classes: 'border-red-200 bg-red-50 text-red-900' },
    warning: { icon: AlertCircle, classes: 'border-amber-200 bg-amber-50 text-amber-950' },
    status: { icon: Info, classes: 'border-brand-200 bg-brand-50 text-brand-950' },
    info: { icon: Info, classes: 'border-brand-200 bg-brand-50 text-brand-950' },
};

const flash = computed(() => page.props.flash ?? {});
const toasts = computed(() => Object.entries(flash.value)
    .filter(([type, message]) => tones[type] && message && !dismissed.value.has(`${type}:${message}`))
    .map(([type, message]) => ({
        id: `${type}:${message}`,
        type,
        message: typeof message === 'string' ? message : message.message,
        ...tones[type],
    }))
    .filter((toast) => toast.message));

watch(() => page.url, () => {
    dismissed.value = new Set();
});

function dismiss(id) {
    dismissed.value = new Set([...dismissed.value, id]);
}
</script>

<template>
    <div class="pointer-events-none fixed inset-x-4 top-4 z-[70] flex flex-col items-end gap-2 sm:left-auto sm:w-full sm:max-w-sm" aria-live="polite" aria-atomic="true">
        <TransitionGroup
            enter-active-class="transition duration-200"
            enter-from-class="translate-y-2 opacity-0 sm:translate-x-4 sm:translate-y-0"
            leave-active-class="transition duration-150"
            leave-to-class="translate-y-2 opacity-0 sm:translate-x-4 sm:translate-y-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto flex w-full items-start gap-3 rounded-2xl border p-4 shadow-lift"
                :class="toast.classes"
                :role="toast.type === 'error' ? 'alert' : 'status'"
            >
                <component :is="toast.icon" :size="20" class="mt-0.5 flex-none" aria-hidden="true" />
                <p class="min-w-0 flex-1 text-sm font-semibold leading-5">{{ toast.message }}</p>
                <button type="button" class="-m-2 flex h-10 w-10 flex-none items-center justify-center rounded-xl opacity-70 hover:bg-black/5 hover:opacity-100" :aria-label="$t('Dismiss message')" @click="dismiss(toast.id)">
                    <X :size="17" aria-hidden="true" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
