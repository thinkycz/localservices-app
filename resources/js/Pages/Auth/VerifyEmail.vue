<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { CircleCheck, LoaderCircle, MailCheck } from '@lucide/vue';

const props = defineProps({ status: String });
const page = usePage();
const copy = (cs, en) => page.props.locale === 'cs' ? cs : en;
const form = useForm({});
const submit = () => form.post(route('verification.send'));
const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head :title="copy('Ověření e-mailu', 'Verify email')" />
        <header class="mb-8 text-center">
            <span class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-700" aria-hidden="true"><MailCheck :size="27" /></span>
            <h1 class="text-2xl font-extrabold tracking-tight text-ink">{{ copy('Ověřte svůj e-mail', 'Verify your email') }}</h1>
            <p class="mt-2 text-sm leading-6 text-muted">{{ copy('Do schránky jsme poslali ověřovací odkaz. Poskytovatelé musí e-mail ověřit před nastavením profilu.', 'We sent a verification link to your inbox. Providers must verify their email before setup.') }}</p>
        </header>

        <div v-if="verificationLinkSent" role="status" class="mb-6 flex items-start gap-3 rounded-xl border border-success/20 bg-success/10 p-4 text-sm font-medium text-success">
            <CircleCheck :size="19" class="mt-0.5 shrink-0" aria-hidden="true" />
            <span>{{ copy('Nový ověřovací odkaz byl odeslán.', 'A new verification link has been sent.') }}</span>
        </div>

        <p class="mb-6 rounded-xl border border-line bg-canvas p-4 text-sm leading-6 text-muted">{{ copy('E-mail nepřišel? Zkontrolujte hromadnou poštu, nebo si nechte poslat nový odkaz.', 'Did not receive it? Check your spam folder or request a new link.') }}</p>

        <form class="space-y-3" @submit.prevent="submit">
            <button type="submit" :disabled="form.processing" class="ui-button ui-button-primary w-full">
                <LoaderCircle v-if="form.processing" :size="18" class="animate-spin" aria-hidden="true" />
                {{ form.processing ? copy('Odesílání…', 'Sending…') : copy('Poslat nový ověřovací odkaz', 'Send a new verification link') }}
            </button>
            <Link :href="route('logout')" method="post" as="button" class="ui-button ui-button-secondary w-full">
                {{ copy('Odhlásit se', 'Sign out') }}
            </Link>
        </form>
    </GuestLayout>
</template>
