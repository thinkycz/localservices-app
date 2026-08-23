<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { Check, KeyRound, ShieldAlert, Trash2, UserRound } from '@lucide/vue';

defineProps({ mustVerifyEmail: Boolean, status: String });

const page = usePage();
const user = page.props.auth.user;
const copy = (cs, en) => page.props.locale === 'cs' ? cs : en;
const userInitials = user.name ? user.name.split(' ').map((part) => part[0]).join('').toUpperCase().slice(0, 2) : 'U';

const profileForm = useForm({ name: user.name, email: user.email });
const currentPasswordInput = ref(null);
const passwordInput = ref(null);
const passwordForm = useForm({ current_password: '', password: '', password_confirmation: '' });

const updatePassword = () => passwordForm.put(route('password.update'), {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
    onError: () => {
        if (passwordForm.errors.password) {
            passwordForm.reset('password', 'password_confirmation');
            passwordInput.value?.focus();
        }
        if (passwordForm.errors.current_password) {
            passwordForm.reset('current_password');
            currentPasswordInput.value?.focus();
        }
    },
});

const confirmingDeletion = ref(false);
const deletePasswordInput = ref(null);
const deleteForm = useForm({ password: '' });
const confirmDeletion = () => {
    confirmingDeletion.value = true;
    nextTick(() => deletePasswordInput.value?.focus());
};
const cancelDeletion = () => {
    confirmingDeletion.value = false;
    deleteForm.clearErrors();
    deleteForm.reset();
};
const deleteAccount = () => deleteForm.delete(route('profile.destroy'), {
    preserveScroll: true,
    onError: () => deletePasswordInput.value?.focus(),
    onFinish: () => deleteForm.reset(),
});
</script>

<template>
    <Head :title="copy('Můj profil', 'My profile')" />

    <AppLayout>
        <section class="border-b border-line bg-white">
            <div class="ui-container py-8 sm:py-10">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-100 text-lg font-extrabold text-brand-800" aria-hidden="true">{{ userInitials }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-brand-700">{{ copy('Nastavení účtu', 'Account settings') }}</p>
                        <h1 class="truncate text-2xl font-extrabold tracking-tight text-ink">{{ user.name }}</h1>
                        <p class="truncate text-sm text-muted">{{ user.email }}</p>
                    </div>
                </div>
            </div>
        </section>

        <main id="main-content" class="ui-container py-8 sm:py-10">
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="ui-card overflow-hidden" aria-labelledby="profile-heading">
                    <header class="flex items-center gap-3 border-b border-line px-5 py-4 sm:px-6">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700" aria-hidden="true"><UserRound :size="20" /></span>
                        <div>
                            <h2 id="profile-heading" class="font-bold text-ink">{{ copy('Osobní údaje', 'Personal details') }}</h2>
                            <p class="text-sm text-muted">{{ copy('Jméno a e-mail používaný u rezervací.', 'Your name and booking email.') }}</p>
                        </div>
                    </header>

                    <form class="space-y-5 p-5 sm:p-6" @submit.prevent="profileForm.patch(route('profile.update'), { preserveScroll: true })">
                        <div>
                            <label for="name" class="mb-1.5 block text-sm font-semibold text-ink">{{ copy('Jméno a příjmení', 'Full name') }}</label>
                            <input id="name" v-model="profileForm.name" type="text" required autocomplete="name" class="ui-field" :class="{ 'border-danger': profileForm.errors.name }" />
                            <InputError class="mt-1.5" :message="profileForm.errors.name" />
                        </div>
                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-semibold text-ink">E-mail</label>
                            <input id="email" v-model="profileForm.email" type="email" required autocomplete="username" inputmode="email" class="ui-field" :class="{ 'border-danger': profileForm.errors.email }" />
                            <InputError class="mt-1.5" :message="profileForm.errors.email" />
                        </div>

                        <div v-if="mustVerifyEmail && user.email_verified_at === null" class="rounded-xl border border-accent/30 bg-accent/10 p-4 text-sm text-ink">
                            <p>{{ copy('Tento e-mail ještě není ověřený. Pro zákaznické rezervace to nevadí, poskytovatelský profil ale vyžaduje ověření.', 'This email is not verified. Customer booking remains available, but provider setup requires verification.') }}</p>
                            <Link :href="route('verification.send')" method="post" as="button" class="mt-2 min-h-11 rounded-lg font-bold text-brand-700 hover:text-brand-800">{{ copy('Poslat ověřovací odkaz', 'Send verification link') }}</Link>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button type="submit" :disabled="profileForm.processing" class="ui-button ui-button-primary">{{ copy('Uložit údaje', 'Save details') }}</button>
                            <span v-if="profileForm.recentlySuccessful" role="status" class="flex items-center gap-1.5 text-sm font-semibold text-success"><Check :size="17" aria-hidden="true" />{{ copy('Uloženo', 'Saved') }}</span>
                        </div>
                    </form>
                </section>

                <section class="ui-card overflow-hidden" aria-labelledby="password-heading">
                    <header class="flex items-center gap-3 border-b border-line px-5 py-4 sm:px-6">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-700" aria-hidden="true"><KeyRound :size="20" /></span>
                        <div>
                            <h2 id="password-heading" class="font-bold text-ink">{{ copy('Změna hesla', 'Change password') }}</h2>
                            <p class="text-sm text-muted">{{ copy('Použijte jedinečné heslo alespoň o 8 znacích.', 'Use a unique password with at least 8 characters.') }}</p>
                        </div>
                    </header>

                    <form class="space-y-5 p-5 sm:p-6" @submit.prevent="updatePassword">
                        <div>
                            <label for="current_password" class="mb-1.5 block text-sm font-semibold text-ink">{{ copy('Současné heslo', 'Current password') }}</label>
                            <input id="current_password" ref="currentPasswordInput" v-model="passwordForm.current_password" type="password" required autocomplete="current-password" class="ui-field" :class="{ 'border-danger': passwordForm.errors.current_password }" />
                            <InputError class="mt-1.5" :message="passwordForm.errors.current_password" />
                        </div>
                        <div>
                            <label for="password" class="mb-1.5 block text-sm font-semibold text-ink">{{ copy('Nové heslo', 'New password') }}</label>
                            <input id="password" ref="passwordInput" v-model="passwordForm.password" type="password" required autocomplete="new-password" class="ui-field" :class="{ 'border-danger': passwordForm.errors.password }" />
                            <InputError class="mt-1.5" :message="passwordForm.errors.password" />
                        </div>
                        <div>
                            <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-ink">{{ copy('Nové heslo znovu', 'Confirm new password') }}</label>
                            <input id="password_confirmation" v-model="passwordForm.password_confirmation" type="password" required autocomplete="new-password" class="ui-field" :class="{ 'border-danger': passwordForm.errors.password_confirmation }" />
                            <InputError class="mt-1.5" :message="passwordForm.errors.password_confirmation" />
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <button type="submit" :disabled="passwordForm.processing" class="ui-button ui-button-primary">{{ copy('Změnit heslo', 'Update password') }}</button>
                            <span v-if="passwordForm.recentlySuccessful" role="status" class="flex items-center gap-1.5 text-sm font-semibold text-success"><Check :size="17" aria-hidden="true" />{{ copy('Heslo změněno', 'Password updated') }}</span>
                        </div>
                    </form>
                </section>
            </div>

            <section class="ui-card mt-6 overflow-hidden border-danger/20" aria-labelledby="delete-heading">
                <header class="flex items-center gap-3 border-b border-danger/10 px-5 py-4 sm:px-6">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-danger/10 text-danger" aria-hidden="true"><Trash2 :size="20" /></span>
                    <div>
                        <h2 id="delete-heading" class="font-bold text-ink">{{ copy('Smazání účtu', 'Delete account') }}</h2>
                        <p class="text-sm text-muted">{{ copy('Tento krok je nevratný.', 'This action cannot be undone.') }}</p>
                    </div>
                </header>

                <div class="p-5 sm:p-6">
                    <div class="flex items-start gap-3 rounded-xl bg-danger/5 p-4 text-sm leading-6 text-ink">
                        <ShieldAlert :size="20" class="mt-0.5 shrink-0 text-danger" aria-hidden="true" />
                        <p>{{ copy('Smazáním odstraníte přístup k účtu a jeho datům. Než budete pokračovat, uložte si informace, které potřebujete.', 'Deleting your account removes access and account data. Save any information you need before continuing.') }}</p>
                    </div>

                    <button v-if="!confirmingDeletion" type="button" class="ui-button ui-button-danger mt-5" @click="confirmDeletion">{{ copy('Smazat můj účet', 'Delete my account') }}</button>

                    <div v-else class="mt-5 max-w-lg rounded-2xl border border-danger/20 p-5" role="dialog" aria-modal="true" aria-labelledby="confirm-delete-heading">
                        <h3 id="confirm-delete-heading" class="font-bold text-ink">{{ copy('Opravdu účet smazat?', 'Delete this account?') }}</h3>
                        <p class="mt-1 text-sm leading-6 text-muted">{{ copy('Pro potvrzení zadejte své heslo.', 'Enter your password to confirm.') }}</p>
                        <label for="delete_password" class="mb-1.5 mt-4 block text-sm font-semibold text-ink">{{ copy('Heslo', 'Password') }}</label>
                        <input id="delete_password" ref="deletePasswordInput" v-model="deleteForm.password" type="password" autocomplete="current-password" class="ui-field" :class="{ 'border-danger': deleteForm.errors.password }" @keyup.enter="deleteAccount" />
                        <InputError class="mt-1.5" :message="deleteForm.errors.password" />
                        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row">
                            <button type="button" class="ui-button ui-button-secondary" @click="cancelDeletion">{{ copy('Zpět', 'Go back') }}</button>
                            <button type="button" :disabled="deleteForm.processing" class="ui-button ui-button-danger" @click="deleteAccount">{{ copy('Trvale smazat účet', 'Permanently delete account') }}</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </AppLayout>
</template>
