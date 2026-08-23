<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { CircleCheck, LoaderCircle } from '@lucide/vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const page = usePage();
const copy = (cs, en) => page.props.locale === 'cs' ? cs : en;
const form = useForm({ email: '', password: '', remember: false });

const submit = () => form.post(route('login'), {
    onFinish: () => form.reset('password'),
});
</script>

<template>
    <GuestLayout>
        <Head :title="copy('Přihlášení', 'Sign in')" />

        <header class="mb-8">
            <p class="text-sm font-bold text-brand-700">{{ copy('Vítejte zpět', 'Welcome back') }}</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-ink">{{ copy('Přihlaste se do Domluveno', 'Sign in to Domluveno') }}</h1>
            <p class="mt-2 text-sm leading-6 text-muted">{{ copy('Spravujte své rezervace a recenze na jednom místě.', 'Manage your bookings and reviews in one place.') }}</p>
        </header>

        <div v-if="status" role="status" class="mb-6 flex items-start gap-3 rounded-xl border border-success/20 bg-success/10 p-4 text-sm font-medium text-success">
            <CircleCheck :size="19" class="mt-0.5 shrink-0" aria-hidden="true" />
            <span>{{ status }}</span>
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-ink">E-mail</label>
                <input id="email" v-model="form.email" type="email" required autofocus autocomplete="username" inputmode="email" class="ui-field" :class="{ 'border-danger': form.errors.email }" placeholder="jana@example.cz" />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between gap-4">
                    <label for="password" class="block text-sm font-semibold text-ink">{{ copy('Heslo', 'Password') }}</label>
                    <Link v-if="canResetPassword" :href="route('password.request')" class="rounded-lg text-sm font-semibold text-brand-700 hover:text-brand-800">
                        {{ copy('Zapomenuté heslo?', 'Forgot password?') }}
                    </Link>
                </div>
                <input id="password" v-model="form.password" type="password" required autocomplete="current-password" class="ui-field" :class="{ 'border-danger': form.errors.password }" placeholder="••••••••" />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <label for="remember" class="flex min-h-11 cursor-pointer items-center gap-3 rounded-xl text-sm text-muted">
                <input id="remember" v-model="form.remember" type="checkbox" class="h-5 w-5 rounded border-line text-brand-600 focus:ring-brand-600" />
                <span>{{ copy('Zůstat přihlášený', 'Keep me signed in') }}</span>
            </label>

            <button type="submit" :disabled="form.processing" class="ui-button ui-button-primary w-full">
                <LoaderCircle v-if="form.processing" :size="18" class="animate-spin" aria-hidden="true" />
                {{ form.processing ? copy('Přihlašování…', 'Signing in…') : copy('Přihlásit se', 'Sign in') }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-muted">
            {{ copy('Ještě nemáte účet?', 'New to Domluveno?') }}
            <Link :href="route('register')" class="ml-1 rounded-lg font-bold text-brand-700 hover:text-brand-800">{{ copy('Vytvořit účet', 'Create an account') }}</Link>
        </p>
    </GuestLayout>
</template>
