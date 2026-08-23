<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { KeyRound, LoaderCircle } from '@lucide/vue';

const props = defineProps({ email: { type: String, required: true }, token: { type: String, required: true } });
const page = usePage();
const copy = (cs, en) => page.props.locale === 'cs' ? cs : en;
const form = useForm({ token: props.token, email: props.email, password: '', password_confirmation: '' });
const submit = () => form.post(route('password.store'), { onFinish: () => form.reset('password', 'password_confirmation') });
</script>

<template>
    <GuestLayout>
        <Head :title="copy('Nové heslo', 'New password')" />
        <header class="mb-8">
            <span class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-700" aria-hidden="true"><KeyRound :size="23" /></span>
            <h1 class="text-2xl font-extrabold tracking-tight text-ink">{{ copy('Nastavte nové heslo', 'Set a new password') }}</h1>
            <p class="mt-2 text-sm leading-6 text-muted">{{ copy('Zvolte jedinečné heslo, které nepoužíváte u jiných služeb.', 'Choose a unique password that you do not use elsewhere.') }}</p>
        </header>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-ink">E-mail</label>
                <input id="email" v-model="form.email" type="email" required readonly autocomplete="username" class="ui-field bg-canvas" />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-ink">{{ copy('Nové heslo', 'New password') }}</label>
                <input id="password" v-model="form.password" type="password" required autofocus autocomplete="new-password" class="ui-field" :class="{ 'border-danger': form.errors.password }" :placeholder="copy('Alespoň 8 znaků', 'At least 8 characters')" />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>
            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-ink">{{ copy('Nové heslo znovu', 'Confirm new password') }}</label>
                <input id="password_confirmation" v-model="form.password_confirmation" type="password" required autocomplete="new-password" class="ui-field" :class="{ 'border-danger': form.errors.password_confirmation }" placeholder="••••••••" />
                <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
            </div>
            <button type="submit" :disabled="form.processing" class="ui-button ui-button-primary w-full">
                <LoaderCircle v-if="form.processing" :size="18" class="animate-spin" aria-hidden="true" />
                {{ form.processing ? copy('Ukládání…', 'Saving…') : copy('Uložit nové heslo', 'Save new password') }}
            </button>
        </form>
    </GuestLayout>
</template>
