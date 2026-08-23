import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const messages = {
    providerSetup: { cs: 'Nastavení poskytovatele', en: 'Provider setup' },
    setupTitle: { cs: 'Začněte přijímat rezervace přes Domluveno', en: 'Start taking bookings with Domluveno' },
    setupIntro: { cs: 'Ve třech krátkých krocích nastavíte kontakt, provozovnu a první služby. Vše můžete později upravit.', en: 'Set up your contact details, shop and first services in three short steps. You can edit everything later.' },
    startSetup: { cs: 'Začít nastavení', en: 'Start setup' },
    alreadyPrepared: { cs: 'Co si připravit', en: 'What to prepare' },
    businessContact: { cs: 'Firemní kontakt', en: 'Business contact' },
    businessContactHelp: { cs: 'Název, telefon a e-mail pro zákazníky.', en: 'A name, phone number and customer-facing email.' },
    shopProfile: { cs: 'Provozovna', en: 'Shop profile' },
    shopProfileHelp: { cs: 'Kategorie, česká adresa, měna a otevírací doba.', en: 'Category, Czech address, currency and business hours.' },
    initialServices: { cs: 'První služby', en: 'Initial services' },
    initialServicesHelp: { cs: 'Alespoň jedna služba s cenou a délkou.', en: 'At least one service with its price and duration.' },
    honestNote: { cs: 'Účet poskytovatele aktivujeme po dokončení všech tří kroků.', en: 'Your provider account is activated after all three steps are complete.' },
    step: { cs: 'Krok', en: 'Step' },
    of: { cs: 'ze', en: 'of' },
    step1: { cs: 'Kontakt', en: 'Contact' },
    step2: { cs: 'Provozovna', en: 'Shop' },
    step3: { cs: 'Služby', en: 'Services' },
    step1Title: { cs: 'Firemní a kontaktní údaje', en: 'Business and contact details' },
    step1Intro: { cs: 'Tyto údaje použijeme pro vaši provozovnu a komunikaci se zákazníky.', en: 'We will use these details for your shop and customer communication.' },
    businessName: { cs: 'Název podnikání', en: 'Business name' },
    businessNamePlaceholder: { cs: 'např. Studio Klid', en: 'e.g. Studio Klid' },
    businessPhone: { cs: 'Firemní telefon', en: 'Business phone' },
    businessPhonePlaceholder: { cs: '+420 777 123 456', en: '+420 777 123 456' },
    businessEmail: { cs: 'Firemní e-mail', en: 'Business email' },
    businessEmailPlaceholder: { cs: 'rezervace@studio.cz', en: 'bookings@studio.cz' },
    customerVisible: { cs: 'Telefon a e-mail mohou zákazníci použít při řešení rezervace.', en: 'Customers may use this phone number and email to discuss a booking.' },
    cancel: { cs: 'Zrušit', en: 'Cancel' },
    back: { cs: 'Zpět', en: 'Back' },
    continue: { cs: 'Pokračovat', en: 'Continue' },
    saving: { cs: 'Ukládám…', en: 'Saving…' },
    step2Title: { cs: 'Nastavení provozovny', en: 'Set up your shop' },
    step2Intro: { cs: 'Popište, kde působíte, v jaké měně účtujete a kdy přijímáte zákazníky.', en: 'Tell customers where you work, which currency you use and when you are open.' },
    category: { cs: 'Kategorie', en: 'Category' },
    chooseCategory: { cs: 'Vyberte kategorii', en: 'Choose a category' },
    shopName: { cs: 'Název provozovny', en: 'Shop name' },
    shopNamePlaceholder: { cs: 'např. Studio Klid Karlín', en: 'e.g. Studio Klid Karlín' },
    description: { cs: 'Popis provozovny', en: 'Shop description' },
    descriptionPlaceholder: { cs: 'Popište své zkušenosti, přístup a služby, které u vás zákazníci najdou.', en: 'Describe your experience, approach and what customers can book with you.' },
    descriptionHint: { cs: 'Alespoň 50 znaků. Pište konkrétně a bez reklamních slibů.', en: 'At least 50 characters. Keep it specific and avoid unsupported claims.' },
    city: { cs: 'Město', en: 'City' },
    cityPlaceholder: { cs: 'Praha', en: 'Prague' },
    address: { cs: 'Adresa', en: 'Address' },
    addressPlaceholder: { cs: 'Sokolovská 42, Praha 8', en: 'Sokolovská 42, Prague 8' },
    currency: { cs: 'Měna', en: 'Currency' },
    currencyHint: { cs: 'Ceny služeb se zákazníkům zobrazí v této měně.', en: 'Customers will see service prices in this currency.' },
    businessHours: { cs: 'Otevírací doba', en: 'Business hours' },
    businessHoursHint: { cs: 'Časy uvádějte v časovém pásmu Praha. Zavřený den nebude možné rezervovat.', en: 'Enter times in the Prague timezone. Customers cannot book on a closed day.' },
    closed: { cs: 'Zavřeno', en: 'Closed' },
    opens: { cs: 'Od', en: 'Opens' },
    closes: { cs: 'Do', en: 'Closes' },
    monday: { cs: 'Pondělí', en: 'Monday' },
    tuesday: { cs: 'Úterý', en: 'Tuesday' },
    wednesday: { cs: 'Středa', en: 'Wednesday' },
    thursday: { cs: 'Čtvrtek', en: 'Thursday' },
    friday: { cs: 'Pátek', en: 'Friday' },
    saturday: { cs: 'Sobota', en: 'Saturday' },
    sunday: { cs: 'Neděle', en: 'Sunday' },
    step3Title: { cs: 'Přidejte první služby', en: 'Add your first services' },
    step3Intro: { cs: 'U každé služby nastavte jasný název, cenu a délku. Další můžete přidat kdykoliv později.', en: 'Give every service a clear name, price and duration. You can add more at any time.' },
    service: { cs: 'Služba', en: 'Service' },
    removeService: { cs: 'Odebrat službu', en: 'Remove service' },
    serviceName: { cs: 'Název služby', en: 'Service name' },
    serviceNamePlaceholder: { cs: 'např. Dámský střih', en: 'e.g. Women’s haircut' },
    serviceDescription: { cs: 'Co služba zahrnuje', en: 'What is included' },
    serviceDescriptionPlaceholder: { cs: 'Stručně popište průběh a výsledek služby.', en: 'Briefly describe the service and what customers can expect.' },
    price: { cs: 'Cena', en: 'Price' },
    duration: { cs: 'Délka', en: 'Duration' },
    minutes: { cs: 'min', en: 'min' },
    chooseDuration: { cs: 'Vyberte délku', en: 'Choose a duration' },
    addService: { cs: 'Přidat další službu', en: 'Add another service' },
    reviewTitle: { cs: 'Před dokončením zkontrolujte', en: 'Review before finishing' },
    reviewFallback: { cs: 'Údaje z předchozích kroků jsou bezpečně uložené v této relaci.', en: 'Details from previous steps are safely stored for this session.' },
    finish: { cs: 'Dokončit nastavení', en: 'Finish setup' },
    finishing: { cs: 'Dokončuji…', en: 'Finishing…' },
    priceExample: { cs: 'např. 750', en: 'e.g. 750' },
    optionalSummary: { cs: 'Souhrn provozovny', en: 'Shop summary' },
};

export function useOnboardingCopy() {
    const page = usePage();
    const locale = computed(() => page.props.locale === 'en' ? 'en' : 'cs');

    return (key) => messages[key]?.[locale.value] ?? key;
}

const draftKey = 'domluveno-provider-onboarding';

export function readOnboardingDraft() {
    if (typeof window === 'undefined') return {};

    try {
        return JSON.parse(window.sessionStorage.getItem(draftKey) ?? '{}');
    } catch {
        return {};
    }
}

export function saveOnboardingDraft(step, value) {
    if (typeof window === 'undefined') return;

    const draft = readOnboardingDraft();
    window.sessionStorage.setItem(draftKey, JSON.stringify({ ...draft, [step]: value }));
}

export function clearOnboardingDraft() {
    if (typeof window === 'undefined') return;
    window.sessionStorage.removeItem(draftKey);
}
