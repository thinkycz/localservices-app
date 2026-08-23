<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Check } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import UiCard from '@/Components/UiCard.vue';
import { useOnboardingCopy } from './onboardingCopy';

const props = defineProps({
    currentStep: { type: Number, required: true },
    title: { type: String, required: true },
    intro: { type: String, required: true },
});

const copy = useOnboardingCopy();
const steps = computed(() => [
    { number: 1, label: copy('step1'), routeName: 'vendor.onboarding.step1' },
    { number: 2, label: copy('step2'), routeName: 'vendor.onboarding.step2' },
    { number: 3, label: copy('step3'), routeName: 'vendor.onboarding.step3' },
]);
</script>

<template>
    <AppLayout>
        <div class="ui-container py-8 sm:py-12 lg:py-16">
            <div class="mx-auto max-w-4xl">
                <header class="mb-6 sm:mb-8">
                    <p class="text-sm font-bold text-brand-700">
                        {{ copy('providerSetup') }} · {{ copy('step') }} {{ currentStep }} {{ copy('of') }} 3
                    </p>
                    <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-ink sm:text-3xl">{{ title }}</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-muted sm:text-base">{{ intro }}</p>
                </header>

                <nav class="mb-6" :aria-label="copy('providerSetup')">
                    <ol class="grid grid-cols-3 gap-2 sm:gap-3">
                        <li v-for="item in steps" :key="item.number">
                            <Link
                                v-if="item.number < currentStep"
                                :href="route(item.routeName)"
                                class="flex min-h-11 items-center gap-2 rounded-xl border border-brand-200 bg-brand-50 px-3 text-xs font-bold text-brand-800 transition hover:border-brand-400 sm:text-sm"
                            >
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white">
                                    <Check :size="14" aria-hidden="true" />
                                </span>
                                <span class="truncate">{{ item.label }}</span>
                            </Link>
                            <div
                                v-else
                                class="flex min-h-11 items-center gap-2 rounded-xl border px-3 text-xs font-bold sm:text-sm"
                                :class="item.number === currentStep ? 'border-brand-600 bg-white text-ink shadow-sm' : 'border-line bg-white/60 text-muted'"
                                :aria-current="item.number === currentStep ? 'step' : undefined"
                            >
                                <span
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full"
                                    :class="item.number === currentStep ? 'bg-brand-600 text-white' : 'bg-gray-100 text-muted'"
                                >{{ item.number }}</span>
                                <span class="truncate">{{ item.label }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                <UiCard padding="lg">
                    <slot />
                </UiCard>
            </div>
        </div>
    </AppLayout>
</template>
