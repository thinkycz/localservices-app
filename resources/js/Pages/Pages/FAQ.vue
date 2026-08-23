<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ChevronDown, MessageSquareText } from '@lucide/vue';

const props = defineProps({ title: String, faqs: { type: Array, default: () => [] } });
const page = usePage();
const isCzech = computed(() => page.props.locale === 'cs');

const categoryCopy = { General: 'Základní informace', Booking: 'Rezervace', Vendors: 'Poskytovatelé', Support: 'Podpora' };
const questionCopy = {
    'What is Domluveno?': 'Co je Domluveno?',
    'How do I create an account?': 'Jak si vytvořím účet?',
    'Which locations are supported?': 'Ve kterých městech služba funguje?',
    'How do I book a service?': 'Jak si rezervuji službu?',
    'Can I cancel or reschedule a booking?': 'Mohu rezervaci zrušit nebo přesunout?',
    'Does Domluveno process payments?': 'Zpracovává Domluveno platby?',
    'How do I become a vendor?': 'Jak se stanu poskytovatelem?',
    'Does Domluveno verify provider qualifications?': 'Ověřuje Domluveno kvalifikaci poskytovatelů?',
    'How are payments handled?': 'Jak probíhají platby?',
    'How do I contact customer support?': 'Jak kontaktuji podporu?',
    'What if I have an issue with a service?': 'Co když mám problém se službou?',
    'Does Domluveno offer a satisfaction guarantee?': 'Nabízí Domluveno záruku spokojenosti?',
};
const answerCopy = {
    'Domluveno helps customers find local providers, compare services, and request an appointment in one place.': 'Domluveno pomáhá najít místní poskytovatele, porovnat jejich služby a rezervovat si termín na jednom místě.',
    'An account is optional for booking. Create one if you want all bookings and reviews in one place, or verify your email before setting up a provider profile.': 'K rezervaci účet nepotřebujete. Vytvořte si ho, pokud chcete mít rezervace a recenze pohromadě. Pro založení poskytovatelského profilu je nutné ověřit e-mail.',
    'The shop list shows the cities currently represented by active providers. Domluveno does not yet use live proximity or location tracking.': 'V přehledu jsou města, ve kterých aktuálně působí aktivní poskytovatelé. Domluveno zatím nevyužívá živou polohu ani řazení podle skutečné vzdálenosti.',
    'Browse services, select one you like, choose your preferred date and time, and complete the booking process. You will receive a confirmation email.': 'Vyberte službu, datum a volný čas, doplňte kontaktní údaje a zkontrolujte shrnutí. Potvrzení dostanete e-mailem.',
    'You can cancel a pending or confirmed booking at least 24 hours before it starts. Guests use the secure link sent by email; account customers use My bookings.': 'Čekající nebo potvrzenou rezervaci můžete zrušit nejpozději 24 hodin před začátkem. Hosté použijí bezpečný odkaz z e-mailu, přihlášení zákazníci sekci Moje rezervace.',
    'No. The displayed price is booking information; payment arrangements are handled directly with the provider.': 'Ne. Uvedená cena slouží jako informace k rezervaci. Platbu si domluvíte přímo s poskytovatelem.',
    'Verify your email, choose Become a provider, and complete the three setup steps for your shop, hours, and services.': 'Ověřte svůj e-mail, zvolte Stát se poskytovatelem a projděte tři kroky nastavení provozovny, otevírací doby a služeb.',
    'Domluveno verifies the provider email address but does not currently conduct background or professional-license checks. Providers are responsible for truthful profile information.': 'Domluveno ověřuje e-mail poskytovatele, ale v tuto chvíli neprovádí prověrky ani kontrolu profesních oprávnění. Za pravdivost profilu odpovídá poskytovatel.',
    'Domluveno does not process provider payouts. Agree payment details directly with the customer.': 'Domluveno nezpracovává platby ani výplaty poskytovatelům. Platební podmínky si domluvte přímo se zákazníkem.',
    'Use the Contact page. Your request is stored for the support team and a copy is sent to the configured support mailbox.': 'Použijte kontaktní formulář. Požadavek se uloží pro tým podpory a odešle do nastavené schránky.',
    'Contact the vendor first. If unresolved, reach out to our support team within 48 hours and we will help mediate.': 'Nejprve kontaktujte poskytovatele. Pokud se problém nepodaří vyřešit, popište situaci podpoře prostřednictvím kontaktního formuláře.',
    'No automatic refund or satisfaction guarantee is offered. Contact the provider first and use the Contact page if you need to report a platform issue.': 'Automatické vrácení peněz ani záruku spokojenosti nenabízíme. Nejprve kontaktujte poskytovatele; problém s platformou nám pošlete přes kontaktní formulář.',
};

const localizedFaqs = computed(() => props.faqs.map((group) => ({
    ...group,
    label: isCzech.value ? (categoryCopy[group.category] ?? group.category) : group.category,
    questions: group.questions.map((item) => ({
        question: isCzech.value ? (questionCopy[item.question] ?? item.question) : item.question,
        answer: isCzech.value ? (answerCopy[item.answer] ?? item.answer) : item.answer,
    })),
})));
const activeCategory = ref(props.faqs[0]?.category ?? null);
const openFAQ = ref(null);
const activeGroup = computed(() => localizedFaqs.value.find((group) => group.category === activeCategory.value));
const selectCategory = (category) => { activeCategory.value = category; openFAQ.value = null; };
const toggleFAQ = (index) => { openFAQ.value = openFAQ.value === index ? null : index; };
</script>

<template>
    <AppLayout>
        <Head :title="isCzech ? 'Časté dotazy' : 'Frequently asked questions'" />

        <section class="border-b border-line bg-white">
            <div class="ui-container py-10 sm:py-14">
                <p class="text-sm font-bold text-brand-700">{{ isCzech ? 'Nápověda' : 'Help centre' }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">{{ isCzech ? 'Časté dotazy' : 'Frequently asked questions' }}</h1>
                <p class="mt-3 max-w-2xl text-base leading-7 text-muted">{{ isCzech ? 'Stručné odpovědi k rezervacím, účtům a poskytovatelskému profilu.' : 'Clear answers about bookings, accounts, and provider profiles.' }}</p>
            </div>
        </section>

        <main id="main-content" class="ui-container py-8 sm:py-10">
            <div class="mx-auto max-w-4xl">
                <div class="mb-6 flex gap-2 overflow-x-auto pb-2" role="tablist" :aria-label="isCzech ? 'Kategorie dotazů' : 'Question categories'">
                    <button v-for="group in localizedFaqs" :key="group.category" type="button" role="tab" :aria-selected="activeCategory === group.category" class="ui-button shrink-0" :class="activeCategory === group.category ? 'ui-button-primary' : 'ui-button-secondary'" @click="selectCategory(group.category)">{{ group.label }}</button>
                </div>

                <div class="space-y-3">
                    <section v-for="(faq, index) in activeGroup?.questions ?? []" :key="faq.question" class="ui-card overflow-hidden">
                        <h2>
                            <button type="button" class="flex min-h-14 w-full items-center justify-between gap-5 px-5 py-4 text-left font-bold text-ink hover:bg-brand-50 sm:px-6" :aria-expanded="openFAQ === index" :aria-controls="`faq-${index}`" @click="toggleFAQ(index)">
                                <span>{{ faq.question }}</span><ChevronDown :size="20" class="shrink-0 text-muted transition-transform" :class="{ 'rotate-180': openFAQ === index }" aria-hidden="true" />
                            </button>
                        </h2>
                        <div v-show="openFAQ === index" :id="`faq-${index}`" class="border-t border-line px-5 py-4 text-sm leading-7 text-muted sm:px-6">{{ faq.answer }}</div>
                    </section>
                </div>

                <section class="mt-8 rounded-2xl border border-line bg-brand-50 p-6 sm:flex sm:items-center sm:justify-between sm:gap-6">
                    <div class="flex gap-4"><MessageSquareText :size="23" class="mt-0.5 shrink-0 text-brand-700" aria-hidden="true" /><div><h2 class="font-bold text-ink">{{ isCzech ? 'Nenašli jste odpověď?' : 'Still need help?' }}</h2><p class="mt-1 text-sm leading-6 text-muted">{{ isCzech ? 'Pošlete nám podrobnosti přes kontaktní formulář.' : 'Send us the details through the contact form.' }}</p></div></div>
                    <Link :href="route('pages.contact')" class="ui-button ui-button-primary mt-5 w-full shrink-0 sm:mt-0 sm:w-auto">{{ isCzech ? 'Napsat podpoře' : 'Contact support' }}</Link>
                </section>
            </div>
        </main>
    </AppLayout>
</template>
