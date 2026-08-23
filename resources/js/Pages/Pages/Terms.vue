<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ title: String, content: Object, lastUpdated: String });
const page = usePage();
const isCzech = computed(() => page.props.locale === 'cs');
const translations = {
    '1. Acceptance of Terms': '1. Souhlas s podmínkami',
    'By using Domluveno, you agree to these terms. If you do not agree, do not use the platform.': 'Používáním Domluveno souhlasíte s těmito podmínkami. Pokud s nimi nesouhlasíte, platformu nepoužívejte.',
    '2. User Accounts': '2. Uživatelské účty',
    'You must create an account to use certain features. You are responsible for maintaining the confidentiality of your account credentials and for all activities under your account.': 'Pro některé funkce je nutné vytvořit účet. Odpovídáte za ochranu přihlašovacích údajů a aktivity provedené pod svým účtem.',
    '3. Vendors': '3. Poskytovatelé',
    'Providers are responsible for the accuracy of their shop, availability, qualifications, prices, and service information. Domluveno may remove accounts that misuse the platform.': 'Poskytovatelé odpovídají za správnost údajů o provozovně, dostupnosti, kvalifikaci, cenách a službách. Domluveno může odstranit účty, které platformu zneužívají.',
    '4. Bookings and cancellations': '4. Rezervace a storno',
    'Bookings are subject to provider confirmation and availability. Domluveno does not process payments. Customers and guests may cancel at least 24 hours before the appointment starts.': 'Rezervace závisí na dostupnosti a potvrzení poskytovatelem. Domluveno nezpracovává platby. Zákazníci a hosté mohou rezervaci zrušit nejpozději 24 hodin před začátkem.',
    '5. Reviews and Ratings': '5. Recenze a hodnocení',
    'Users may leave reviews for completed services. Reviews must be honest and based on actual experiences. We reserve the right to remove fake or inappropriate reviews.': 'Uživatelé mohou hodnotit dokončené služby. Recenze musí být pravdivá a vycházet ze skutečné zkušenosti. Falešné či nevhodné recenze můžeme odstranit.',
    '6. Limitation of Liability': '6. Omezení odpovědnosti',
    'Domluveno connects customers with independent providers. Providers remain responsible for the services they deliver.': 'Domluveno propojuje zákazníky s nezávislými poskytovateli. Za poskytnuté služby nadále odpovídá konkrétní poskytovatel.',
};
const localizedSections = computed(() => (props.content?.sections ?? []).map((section) => ({
    title: isCzech.value ? (translations[section.title] ?? section.title) : section.title,
    content: isCzech.value ? (translations[section.content] ?? section.content) : section.content,
})));
</script>

<template>
    <AppLayout>
        <Head :title="isCzech ? 'Podmínky používání' : title" />
        <section class="border-b border-line bg-white">
            <div class="ui-container py-10 sm:py-14">
                <p class="text-sm font-bold text-brand-700">{{ isCzech ? 'Právní informace' : 'Legal information' }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">{{ isCzech ? 'Podmínky používání' : title }}</h1>
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
                <p class="mt-10 border-t border-line pt-6 text-sm leading-6 text-muted">{{ isCzech ? 'Používáním Domluveno potvrzujete, že jste tyto podmínky četli a souhlasíte s nimi.' : 'By using Domluveno, you acknowledge that you have read and agree to these terms.' }}</p>
            </article>
        </main>
    </AppLayout>
</template>
