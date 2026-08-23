<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { BriefcaseBusiness, LoaderCircle, UserRound } from '@lucide/vue';

const page = usePage();
const copy = (cs, en) => page.props.locale === 'cs' ? cs : en;
const form = useForm({ name: '', email: '', password: '', password_confirmation: '', is_vendor: false });

const submit = () => form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
});
</script>

<template>
    <GuestLayout>
        <Head :title="copy('Registrace', 'Create account')" />

        <header class="mb-8">
            <p class="text-sm font-bold text-brand-700">{{ copy('Začněte během chvilky', 'Get started in a moment') }}</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-ink">{{ copy('Vytvořte si účet', 'Create your account') }}</h1>
            <p class="mt-2 text-sm leading-6 text-muted">{{ copy('Rezervujte služby, nebo nabídněte vlastní. Bez zbytečných kroků.', 'Book local services or offer your own, without unnecessary steps.') }}</p>
        </header>

        <form class="space-y-5" @submit.prevent="submit">
            <fieldset>
                <legend class="mb-2 block text-sm font-semibold text-ink">{{ copy('Účet používám jako', 'I am joining as') }}</legend>
                <div class="grid grid-cols-2 gap-2 rounded-2xl bg-canvas p-1.5" role="radiogroup">
                    <button type="button" role="radio" :aria-checked="!form.is_vendor" class="min-h-12 rounded-xl px-3 text-sm font-bold transition" :class="!form.is_vendor ? 'bg-white text-brand-700 shadow-sm ring-1 ring-line' : 'text-muted hover:text-ink'" @click="form.is_vendor = false">
                        <span class="flex items-center justify-center gap-2"><UserRound :size="18" aria-hidden="true" />{{ copy('Zákazník', 'Customer') }}</span>
                    </button>
                    <button type="button" role="radio" :aria-checked="form.is_vendor" class="min-h-12 rounded-xl px-3 text-sm font-bold transition" :class="form.is_vendor ? 'bg-white text-brand-700 shadow-sm ring-1 ring-line' : 'text-muted hover:text-ink'" @click="form.is_vendor = true">
                        <span class="flex items-center justify-center gap-2"><BriefcaseBusiness :size="18" aria-hidden="true" />{{ copy('Poskytovatel', 'Provider') }}</span>
                    </button>
                </div>
                <p class="mt-2 text-xs leading-5 text-muted">{{ form.is_vendor ? copy('Po ověření e-mailu nastavíte provozovnu, otevírací dobu a služby.', 'After verifying your email, you will set up your shop, hours, and services.') : copy('Účet je praktický pro přehled rezervací a recenzí. Rezervovat lze i bez něj.', 'An account keeps bookings and reviews together. Guest booking is also available.') }}</p>
                <InputError class="mt-1.5" :message="form.errors.is_vendor" />
            </fieldset>

            <div>
                <label for="name" class="mb-1.5 block text-sm font-semibold text-ink">{{ copy('Jméno a příjmení', 'Full name') }}</label>
                <input id="name" v-model="form.name" type="text" required autofocus autocomplete="name" class="ui-field" :class="{ 'border-danger': form.errors.name }" placeholder="Jana Nováková" />
                <InputError class="mt-1.5" :message="form.errors.name" />
            </div>

            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-ink">E-mail</label>
                <input id="email" v-model="form.email" type="email" required autocomplete="username" inputmode="email" class="ui-field" :class="{ 'border-danger': form.errors.email }" placeholder="jana@example.cz" />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-ink">{{ copy('Heslo', 'Password') }}</label>
                <input id="password" v-model="form.password" type="password" required autocomplete="new-password" class="ui-field" :class="{ 'border-danger': form.errors.password }" :placeholder="copy('Alespoň 8 znaků', 'At least 8 characters')" />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-ink">{{ copy('Heslo znovu', 'Confirm password') }}</label>
                <input id="password_confirmation" v-model="form.password_confirmation" type="password" required autocomplete="new-password" class="ui-field" :class="{ 'border-danger': form.errors.password_confirmation }" placeholder="••••••••" />
                <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
            </div>

            <button type="submit" :disabled="form.processing" class="ui-button ui-button-primary w-full">
                <LoaderCircle v-if="form.processing" :size="18" class="animate-spin" aria-hidden="true" />
                {{ form.processing ? copy('Vytváření účtu…', 'Creating account…') : copy('Vytvořit účet', 'Create account') }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
            {{ copy('Už účet máte?', 'Already have an account?') }}
            <Link :href="route('login')" class="ml-1 rounded-lg font-bold text-brand-700 hover:text-brand-800">{{ copy('Přihlásit se', 'Sign in') }}</Link>
        </p>
    </GuestLayout>
</template>
