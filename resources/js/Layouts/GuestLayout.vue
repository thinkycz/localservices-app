<script setup>
import { Link } from '@inertiajs/vue3';
import { CalendarCheck, Search, ShieldCheck } from '@lucide/vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ToastRegion from '@/Components/ToastRegion.vue';

const benefits = [
    { icon: Search, title: 'Find the right service', text: 'Compare available local providers and their services.' },
    { icon: CalendarCheck, title: 'Choose a suitable time', text: 'See availability before you send your booking.' },
    { icon: ShieldCheck, title: 'Keep control of your booking', text: 'Review the details and manage eligible changes clearly.' },
];
</script>

<template>
    <div class="grid min-h-screen bg-canvas lg:grid-cols-[minmax(22rem,0.9fr)_minmax(32rem,1.1fr)]">
        <aside class="relative hidden overflow-hidden bg-brand-800 p-10 text-white lg:flex lg:flex-col xl:p-14">
            <div class="absolute inset-0 opacity-30" aria-hidden="true">
                <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full border border-white/20"></div>
                <div class="absolute -bottom-28 -left-28 h-96 w-96 rounded-full border border-white/10"></div>
            </div>

            <Link :href="route('home')" class="relative z-10 inline-flex self-start rounded-xl focus-visible:outline-none">
                <ApplicationLogo inverse />
            </Link>

            <div class="relative z-10 my-auto max-w-lg py-16">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-brand-200">{{ $t('Simple booking') }}</p>
                <h1 class="mt-4 text-4xl font-extrabold leading-tight tracking-[-0.04em] xl:text-5xl">
                    {{ $t('Local services without unnecessary steps.') }}
                </h1>
                <p class="mt-5 max-w-md text-base leading-7 text-brand-100">
                    {{ $t('Choose a service and a suitable time. Domluveno keeps the important details clear from start to finish.') }}
                </p>

                <ul class="mt-10 space-y-6">
                    <li v-for="benefit in benefits" :key="benefit.title" class="flex gap-4">
                        <span class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-white/10 text-brand-100" aria-hidden="true">
                            <component :is="benefit.icon" :size="21" />
                        </span>
                        <span>
                            <span class="block text-sm font-bold">{{ $t(benefit.title) }}</span>
                            <span class="mt-1 block text-sm leading-5 text-brand-100">{{ $t(benefit.text) }}</span>
                        </span>
                    </li>
                </ul>
            </div>

            <p class="relative z-10 text-xs text-brand-200">© {{ new Date().getFullYear() }} Domluveno</p>
        </aside>

        <main id="main-content" class="flex min-w-0 flex-col px-4 py-6 sm:px-8 sm:py-10 lg:justify-center lg:px-14 xl:px-20">
            <div class="mx-auto w-full max-w-md">
                <Link :href="route('home')" class="mb-10 inline-flex rounded-xl focus-visible:outline-none lg:hidden">
                    <ApplicationLogo />
                </Link>

                <div class="rounded-2xl border border-line bg-white p-6 shadow-soft sm:p-8">
                    <slot />
                </div>

                <div class="mt-6 flex flex-wrap justify-center gap-x-5 gap-y-2 text-xs text-muted">
                    <Link :href="route('pages.privacy')" class="hover:text-brand-700">{{ $t('Privacy Policy') }}</Link>
                    <Link :href="route('pages.terms')" class="hover:text-brand-700">{{ $t('Terms of Service') }}</Link>
                    <Link :href="route('pages.contact')" class="hover:text-brand-700">{{ $t('Contact Support') }}</Link>
                </div>
            </div>
        </main>

        <ToastRegion />
    </div>
</template>
