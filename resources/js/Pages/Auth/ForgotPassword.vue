<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, CircleCheck, LoaderCircle, Mail } from '@lucide/vue';

defineProps({ status: String });
const page = usePage();
const copy = (cs, en) => page.props.locale === 'cs' ? cs : en;
const form = useForm({ email: '' });
const submit = () => form.post(route('password.email'));
</script>

<template>
    <GuestLayout>
        <Head :title="copy('Obnova hesla', 'Reset password')" />

        <header class="mb-8">
            <span class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-700" aria-hidden="true"><Mail :size="23" /></span>
            <h1 class="text-2xl font-extrabold tracking-tight text-ink">{{ copy('Zapomněli jste heslo?', 'Forgot your password?') }}</h1>
            <p class="mt-2 text-sm leading-6 text-muted">{{ copy('Zadejte e-mail k účtu. Pošleme vám odkaz pro bezpečné nastavení nového hesla.', 'Enter the email for your account and we will send you a secure reset link.') }}</p>
        </header>

        <div v-if="status" role="status" class="mb-6 flex items-start gap-3 rounded-xl border border-success/20 bg-success/10 p-4 text-sm font-medium text-success">
            <CircleCheck :size="19" class="mt-0.5 shrink-0" aria-hidden="true" /><span>{{ status }}</span>
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-ink">E-mail</label>
                <input id="email" v-model="form.email" type="email" required autofocus autocomplete="username" inputmode="email" class="ui-field" :class="{ 'border-danger': form.errors.email }" placeholder="jana@example.cz" />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>
            <button type="submit" :disabled="form.processing" class="ui-button ui-button-primary w-full">
                <LoaderCircle v-if="form.processing" :size="18" class="animate-spin" aria-hidden="true" />
                {{ form.processing ? copy('Odesílání…', 'Sending…') : copy('Poslat odkaz pro obnovu', 'Send reset link') }}
            </button>
        </form>

        <Link :href="route('login')" class="mt-6 flex min-h-11 items-center justify-center gap-2 rounded-xl text-sm font-semibold text-muted hover:bg-brand-50 hover:text-brand-700">
            <ArrowLeft :size="17" aria-hidden="true" />{{ copy('Zpět na přihlášení', 'Back to sign in') }}
        </Link>
    </GuestLayout>
</template>
