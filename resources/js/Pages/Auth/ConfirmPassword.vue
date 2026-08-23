<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { LoaderCircle, ShieldCheck } from '@lucide/vue';

const page = usePage();
const copy = (cs, en) => page.props.locale === 'cs' ? cs : en;
const form = useForm({ password: '' });
const submit = () => form.post(route('password.confirm'), { onFinish: () => form.reset() });
</script>

<template>
    <GuestLayout>
        <Head :title="copy('Potvrzení hesla', 'Confirm password')" />
        <header class="mb-8">
            <span class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-700" aria-hidden="true"><ShieldCheck :size="23" /></span>
            <h1 class="text-2xl font-extrabold tracking-tight text-ink">{{ copy('Potvrďte své heslo', 'Confirm your password') }}</h1>
            <p class="mt-2 text-sm leading-6 text-muted">{{ copy('Pro pokračování v této chráněné části znovu zadejte heslo.', 'Please enter your password again to continue to this protected area.') }}</p>
        </header>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-ink">{{ copy('Heslo', 'Password') }}</label>
                <input id="password" v-model="form.password" type="password" required autofocus autocomplete="current-password" class="ui-field" :class="{ 'border-danger': form.errors.password }" placeholder="••••••••" />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>
            <button type="submit" :disabled="form.processing" class="ui-button ui-button-primary w-full">
                <LoaderCircle v-if="form.processing" :size="18" class="animate-spin" aria-hidden="true" />
                {{ form.processing ? copy('Ověřování…', 'Confirming…') : copy('Potvrdit a pokračovat', 'Confirm and continue') }}
            </button>
        </form>
    </GuestLayout>
</template>
