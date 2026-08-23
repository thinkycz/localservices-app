<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Mail } from '@lucide/vue';
import AppNavbar from '@/Components/AppNavbar.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ToastRegion from '@/Components/ToastRegion.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const providerHref = computed(() => user.value?.is_vendor
    ? route('vendor.dashboard')
    : route('vendor.onboarding.index'));
</script>

<template>
    <div class="flex min-h-screen min-w-0 flex-col bg-canvas">
        <AppNavbar />

        <main id="main-content" class="min-w-0 flex-1">
            <slot />
        </main>

        <footer class="mt-auto border-t border-line bg-white" aria-labelledby="footer-heading">
            <h2 id="footer-heading" class="sr-only">{{ $t('Footer') }}</h2>
            <div class="ui-container py-10 sm:py-12">
                <div class="grid gap-9 md:grid-cols-[1.4fr_1fr_1fr]">
                    <div class="max-w-sm">
                        <Link :href="route('home')" class="inline-flex min-h-11 items-center rounded-xl focus-visible:outline-none">
                            <ApplicationLogo />
                        </Link>
                        <p class="mt-4 text-sm leading-6 text-muted">
                            {{ $t('Find a local service, choose an available time and keep your booking details in one place.') }}
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-ink">{{ $t('Explore') }}</h3>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li>
                                <Link :href="route('shops.index')" class="inline-flex min-h-11 items-center text-muted transition hover:text-brand-700">
                                    {{ $t('Browse Shops') }}
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :href="user ? route('bookings.index') : route('login')"
                                    class="inline-flex min-h-11 items-center text-muted transition hover:text-brand-700"
                                >{{ $t('My Bookings') }}</Link>
                            </li>
                            <li>
                                <Link :href="providerHref" class="inline-flex min-h-11 items-center gap-1.5 text-muted transition hover:text-brand-700">
                                    {{ user?.is_vendor ? $t('Provider dashboard') : $t('Become a provider') }}
                                    <ArrowRight :size="14" aria-hidden="true" />
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-ink">{{ $t('Help') }}</h3>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li>
                                <Link :href="route('pages.faq')" class="inline-flex min-h-11 items-center text-muted transition hover:text-brand-700">
                                    {{ $t('Frequently Asked Questions') }}
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('pages.contact')" class="inline-flex min-h-11 items-center gap-2 text-muted transition hover:text-brand-700">
                                    <Mail :size="16" aria-hidden="true" />
                                    {{ $t('Contact Support') }}
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-10 flex flex-col gap-4 border-t border-line pt-6 text-xs text-muted sm:flex-row sm:items-center sm:justify-between">
                    <p>© {{ new Date().getFullYear() }} Domluveno.</p>
                    <div class="flex flex-wrap gap-x-5 gap-y-2">
                        <Link :href="route('pages.privacy')" class="inline-flex min-h-11 items-center transition hover:text-brand-700">{{ $t('Privacy Policy') }}</Link>
                        <Link :href="route('pages.terms')" class="inline-flex min-h-11 items-center transition hover:text-brand-700">{{ $t('Terms of Service') }}</Link>
                    </div>
                </div>
            </div>
        </footer>

        <ToastRegion />
    </div>
</template>
