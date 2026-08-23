<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowRight, HelpCircle, LoaderCircle, MessageSquareText } from '@lucide/vue';

defineProps({ title: String });
const page = usePage();
const copy = (cs, en) => page.props.locale === 'cs' ? cs : en;
const form = useForm({ name: '', email: '', type: 'general', subject: '', message: '' });

const submit = () => form.post(route('pages.contact.submit'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
});
</script>

<template>
    <AppLayout>
        <Head :title="copy('Kontakt', 'Contact')" />

        <section class="border-b border-line bg-white">
            <div class="ui-container py-10 sm:py-14">
                <p class="text-sm font-bold text-brand-700">{{ copy('Podpora Domluveno', 'Domluveno support') }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">{{ copy('Napište nám', 'Contact us') }}</h1>
                <p class="mt-3 max-w-2xl text-base leading-7 text-muted">{{ copy('Popište, s čím potřebujete pomoci. Čím konkrétnější budete, tím lépe se v požadavku zorientujeme.', 'Tell us what you need help with. Specific details help us understand your request.') }}</p>
            </div>
        </section>

        <main id="main-content" class="ui-container py-8 sm:py-10">
            <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
                <section class="ui-card p-5 sm:p-7" aria-labelledby="contact-form-heading">
                    <div class="mb-6 flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700" aria-hidden="true"><MessageSquareText :size="22" /></span>
                        <div>
                            <h2 id="contact-form-heading" class="text-lg font-bold text-ink">{{ copy('Kontaktní formulář', 'Contact form') }}</h2>
                            <p class="text-sm text-muted">{{ copy('Všechna pole jsou povinná.', 'All fields are required.') }}</p>
                        </div>
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="name" class="mb-1.5 block text-sm font-semibold text-ink">{{ copy('Jméno a příjmení', 'Full name') }}</label>
                                <input id="name" v-model="form.name" type="text" required autocomplete="name" class="ui-field" :class="{ 'border-danger': form.errors.name }" placeholder="Jana Nováková" />
                                <InputError class="mt-1.5" :message="form.errors.name" />
                            </div>
                            <div>
                                <label for="email" class="mb-1.5 block text-sm font-semibold text-ink">E-mail</label>
                                <input id="email" v-model="form.email" type="email" required autocomplete="email" inputmode="email" class="ui-field" :class="{ 'border-danger': form.errors.email }" placeholder="jana@example.cz" />
                                <InputError class="mt-1.5" :message="form.errors.email" />
                            </div>
                        </div>

                        <div>
                            <label for="type" class="mb-1.5 block text-sm font-semibold text-ink">{{ copy('Typ požadavku', 'Request type') }}</label>
                            <select id="type" v-model="form.type" required class="ui-field" :class="{ 'border-danger': form.errors.type }">
                                <option value="general">{{ copy('Obecný dotaz', 'General question') }}</option>
                                <option value="support">{{ copy('Pomoc s účtem nebo rezervací', 'Account or booking support') }}</option>
                                <option value="partnership">{{ copy('Spolupráce', 'Partnership') }}</option>
                                <option value="feedback">{{ copy('Zpětná vazba', 'Feedback') }}</option>
                            </select>
                            <InputError class="mt-1.5" :message="form.errors.type" />
                        </div>

                        <div>
                            <label for="subject" class="mb-1.5 block text-sm font-semibold text-ink">{{ copy('Předmět', 'Subject') }}</label>
                            <input id="subject" v-model="form.subject" type="text" required maxlength="200" class="ui-field" :class="{ 'border-danger': form.errors.subject }" :placeholder="copy('Například: změna e-mailu u rezervace', 'For example: changing a booking email')" />
                            <InputError class="mt-1.5" :message="form.errors.subject" />
                        </div>

                        <div>
                            <label for="message" class="mb-1.5 block text-sm font-semibold text-ink">{{ copy('Zpráva', 'Message') }}</label>
                            <textarea id="message" v-model="form.message" rows="6" required minlength="10" maxlength="5000" class="ui-field resize-y" :class="{ 'border-danger': form.errors.message }" :placeholder="copy('Popište prosím situaci a uveďte údaje, podle kterých ji dohledáme.', 'Describe the situation and include details that help us find it.')"></textarea>
                            <div class="mt-1.5 flex items-start justify-between gap-4">
                                <InputError :message="form.errors.message" />
                                <span class="ml-auto text-xs text-muted">{{ form.message.length }}/5000</span>
                            </div>
                        </div>

                        <button type="submit" :disabled="form.processing" class="ui-button ui-button-primary w-full sm:w-auto">
                            <LoaderCircle v-if="form.processing" :size="18" class="animate-spin" aria-hidden="true" />
                            {{ form.processing ? copy('Odesílání…', 'Sending…') : copy('Odeslat zprávu', 'Send message') }}
                        </button>
                    </form>
                </section>

                <aside class="space-y-5" aria-label="Další možnosti pomoci">
                    <section class="ui-card p-6">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700" aria-hidden="true"><HelpCircle :size="22" /></span>
                        <h2 class="mt-4 text-lg font-bold text-ink">{{ copy('Nejdřív zkuste nápovědu', 'Check the help centre first') }}</h2>
                        <p class="mt-2 text-sm leading-6 text-muted">{{ copy('Najdete tam postup rezervace, storno podmínky i informace pro poskytovatele.', 'Find booking steps, cancellation rules, and provider information there.') }}</p>
                        <Link :href="route('pages.faq')" class="ui-button ui-button-secondary mt-5 w-full">{{ copy('Otevřít časté dotazy', 'Open frequently asked questions') }}<ArrowRight :size="17" aria-hidden="true" /></Link>
                    </section>

                    <section class="rounded-2xl border border-line bg-brand-800 p-6 text-white">
                        <h2 class="font-bold">{{ copy('Co se stane po odeslání?', 'What happens after sending?') }}</h2>
                        <p class="mt-2 text-sm leading-6 text-brand-100">{{ copy('Požadavek uložíme pro tým podpory. Odpověď přijde na e-mail, který uvedete ve formuláři. Neslibujeme nepřetržitou podporu ani okamžitou odpověď.', 'We store your request for the support team. A reply will go to the email entered above. We do not promise 24/7 or instant support.') }}</p>
                    </section>
                </aside>
            </div>
        </main>
    </AppLayout>
</template>
