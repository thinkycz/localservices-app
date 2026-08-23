<script setup>
import { Head } from '@inertiajs/vue3';
import { ArrowRight, BriefcaseBusiness, CalendarClock, CircleCheck, Store } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import UiButton from '@/Components/UiButton.vue';
import UiCard from '@/Components/UiCard.vue';
import { useOnboardingCopy } from './onboardingCopy';

const copy = useOnboardingCopy();

const items = [
    { key: 'businessContact', help: 'businessContactHelp', icon: BriefcaseBusiness },
    { key: 'shopProfile', help: 'shopProfileHelp', icon: Store },
    { key: 'initialServices', help: 'initialServicesHelp', icon: CalendarClock },
];
</script>

<template>
    <Head :title="copy('providerSetup')" />

    <AppLayout>
        <section class="border-b border-line bg-white">
            <div class="ui-container py-12 sm:py-16 lg:py-20">
                <div class="mx-auto max-w-3xl text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-100 text-brand-700">
                        <CircleCheck :size="30" aria-hidden="true" />
                    </div>
                    <p class="mt-5 text-sm font-bold text-brand-700">{{ copy('providerSetup') }}</p>
                    <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl lg:text-5xl">
                        {{ copy('setupTitle') }}
                    </h1>
                    <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-muted sm:text-lg">
                        {{ copy('setupIntro') }}
                    </p>
                    <div class="mt-8 flex justify-center">
                        <UiButton :href="route('vendor.onboarding.step1')" size="lg">
                            {{ copy('startSetup') }}
                            <ArrowRight :size="19" aria-hidden="true" />
                        </UiButton>
                    </div>
                </div>
            </div>
        </section>

        <section class="ui-container py-10 sm:py-14" :aria-labelledby="'setup-checklist'">
            <div class="mx-auto max-w-5xl">
                <h2 id="setup-checklist" class="text-center text-xl font-extrabold text-ink sm:text-2xl">
                    {{ copy('alreadyPrepared') }}
                </h2>
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <UiCard v-for="(item, index) in items" :key="item.key" class="h-full">
                        <div class="flex items-start gap-4 md:block">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 md:mb-4">
                                <component :is="item.icon" :size="22" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-brand-700">{{ copy('step') }} {{ index + 1 }}</p>
                                <h3 class="mt-1 text-base font-extrabold text-ink">{{ copy(item.key) }}</h3>
                                <p class="mt-1.5 text-sm leading-6 text-muted">{{ copy(item.help) }}</p>
                            </div>
                        </div>
                    </UiCard>
                </div>

                <p class="mx-auto mt-6 max-w-2xl rounded-xl border border-line bg-white px-4 py-3 text-center text-sm text-muted">
                    {{ copy('honestNote') }}
                </p>
            </div>
        </section>
    </AppLayout>
</template>
