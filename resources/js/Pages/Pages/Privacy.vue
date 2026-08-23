<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ title: String, content: Object, lastUpdated: String });
const page = usePage();
const isCzech = computed(() => page.props.locale === 'cs');
const translations = {
    'Information We Collect': 'Jaké údaje shromažďujeme',
    'We collect information you provide directly, including name, email, phone number, account information, booking details, reviews, and support requests.': 'Shromažďujeme údaje, které nám sami poskytnete: jméno, e-mail, telefon, informace o účtu, údaje k rezervacím, recenze a požadavky na podporu.',
    'How We Use Your Information': 'Jak údaje používáme',
    'We use your information to process bookings, communicate with you, improve our platform, prevent fraud, and comply with legal obligations.': 'Údaje používáme ke zpracování rezervací, komunikaci, zlepšování platformy, prevenci zneužití a plnění zákonných povinností.',
    'Information Sharing': 'Sdílení údajů',
    'We share necessary information with vendors to fulfill bookings. We do not sell your personal information to third parties for marketing purposes.': 'Nezbytné údaje sdílíme s poskytovatelem, aby mohl rezervaci splnit. Vaše osobní údaje neprodáváme třetím stranám pro marketingové účely.',
    'Data Security': 'Zabezpečení údajů',
    'We use access controls and secure, hashed guest-management tokens to protect booking information. No payment-card information is collected by Domluveno.': 'Údaje o rezervacích chráníme řízením přístupu a bezpečně hashovanými tokeny pro správu rezervací hostů. Domluveno neshromažďuje údaje o platebních kartách.',
    'Your Rights': 'Vaše práva',
    'You have the right to access, correct, or delete your personal information. Contact us to exercise these rights or for any privacy-related questions.': 'Máte právo požádat o přístup ke svým osobním údajům, jejich opravu nebo výmaz. Pro uplatnění práv či dotaz k soukromí použijte kontaktní formulář.',
};
const localizedSections = computed(() => (props.content?.sections ?? []).map((section) => ({
    title: isCzech.value ? (translations[section.title] ?? section.title) : section.title,
    content: isCzech.value ? (translations[section.content] ?? section.content) : section.content,
})));
</script>

<template>
    <AppLayout>
        <Head :title="isCzech ? 'Ochrana osobních údajů' : title" />
        <section class="border-b border-line bg-white">
            <div class="ui-container py-10 sm:py-14">
                <p class="text-sm font-bold text-brand-700">{{ isCzech ? 'Právní informace' : 'Legal information' }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">{{ isCzech ? 'Ochrana osobních údajů' : title }}</h1>
                <p class="mt-3 text-sm text-muted">{{ isCzech ? 'Aktualizováno' : 'Last updated' }}: {{ lastUpdated }}</p>
            </div>
        </section>
        <main id="main-content" class="ui-container py-8 sm:py-10">
            <article class="ui-card mx-auto max-w-4xl p-6 sm:p-10">
                <div class="space-y-8">
                    <section v-for="section in localizedSections" :key="section.title">
                        <h2 class="text-xl font-bold text-ink">{{ section.title }}</h2>
                        <p class="mt-3 text-sm leading-7 text-muted sm:text-base">{{ section.content }}</p>
                    </section>
                </div>
                <div class="mt-10 border-t border-line pt-6 text-sm leading-6 text-muted">
                    {{ isCzech ? 'Máte-li dotaz k soukromí nebo chcete uplatnit svá práva, použijte' : 'For privacy questions or data requests, use the' }}
                    <Link :href="route('pages.contact')" class="ml-1 font-bold text-brand-700 hover:text-brand-800">{{ isCzech ? 'kontaktní formulář' : 'contact form' }}</Link>.
                </div>
            </article>
        </main>
    </AppLayout>
</template>
