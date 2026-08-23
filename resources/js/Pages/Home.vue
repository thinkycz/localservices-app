<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    CalendarCheck2,
    CarFront,
    CheckCircle2,
    Coffee,
    Dumbbell,
    HeartHandshake,
    House,
    MapPin,
    PawPrint,
    Scissors,
    Search,
    Sparkles,
    Store,
    Wrench,
} from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import ShopCard from '@/Components/ShopCard.vue';

const props = defineProps({
    featuredShops: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const page = usePage();
const isEnglish = computed(() => page.props.locale === 'en');
const tr = (czech, english) => isEnglish.value ? english : czech;
const searchQuery = ref('');
const shopCount = computed(() => props.categories.reduce((sum, category) => sum + Number(category.shops_count ?? 0), 0));
const steps = computed(() => [
    [tr('Najděte službu', 'Find a service'), tr('Vyhledejte službu nebo místo a porovnejte dostupné podniky.', 'Search for a service or place and compare available providers.'), Search],
    [tr('Vyberte termín', 'Choose a time'), tr('U služby uvidíte cenu, délku a skutečně volné časy.', 'See the exact price, duration, and genuinely available times.'), CalendarCheck2],
    [tr('Potvrďte domluvu', 'Confirm the booking'), tr('Vyplňte kontakt, zkontrolujte souhrn a rezervaci odešlete.', 'Add your contact details, review the summary, and send the booking.'), CheckCircle2],
]);
const icons = {
    scissors: Scissors,
    sparkles: Sparkles,
    coffee: Coffee,
    dumbbell: Dumbbell,
    paw: PawPrint,
    'paw-print': PawPrint,
    'car-front': CarFront,
    wrench: Wrench,
    home: House,
};

function categoryIcon(category) {
    const key = String(category.icon ?? category.slug ?? '').toLowerCase();
    return icons[key] ?? icons[category.slug] ?? Store;
}

function search() {
    const query = searchQuery.value.trim();
    router.get(route('shops.index'), query ? { q: query } : {});
}
</script>

<template>
    <AppLayout>
        <section class="border-b border-line bg-canvas">
            <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-12 sm:px-6 md:py-20 lg:grid-cols-[minmax(0,1.1fr)_minmax(20rem,.9fr)] lg:px-8">
                <div>
                    <p class="mb-4 inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 text-sm font-bold text-brand-800">
                        <MapPin :size="16" aria-hidden="true" /> {{ tr('Služby ve vašem okolí', 'Services near you') }}
                    </p>
                    <h1 class="max-w-3xl text-4xl font-extrabold leading-tight tracking-[-0.04em] text-ink sm:text-5xl lg:text-6xl">
                        {{ tr('Najděte termín. Domluvte službu. Hotovo.', 'Find a time. Book the service. Done.') }}
                    </h1>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-muted sm:text-lg">
                        {{ tr('Vyberte si místní podnik, ověřte volný čas a rezervujte bez zbytečných kroků — klidně i bez účtu.', 'Choose a local provider, check a real opening, and book without unnecessary steps—even without an account.') }}
                    </p>

                    <form class="mt-8 flex max-w-2xl flex-col gap-3 sm:flex-row" role="search" @submit.prevent="search">
                        <label class="sr-only" for="home-search">{{ tr('Služba nebo místo', 'Service or place') }}</label>
                        <div class="relative min-w-0 flex-1">
                            <Search class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-muted" :size="20" aria-hidden="true" />
                            <input
                                id="home-search"
                                v-model="searchQuery"
                                type="search"
                                autocomplete="off"
                                :placeholder="tr('Např. kadeřnictví nebo Brno', 'For example, a barber or Brno')"
                                class="min-h-12 w-full rounded-2xl border border-line bg-white py-3 pl-12 pr-4 text-base text-ink shadow-sm placeholder:text-muted focus:border-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-100"
                            >
                        </div>
                        <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl bg-brand-700 px-6 font-extrabold text-white shadow-sm transition hover:bg-brand-800 focus:outline-none focus:ring-4 focus:ring-brand-200">
                            {{ tr('Hledat', 'Search') }} <ArrowRight :size="18" aria-hidden="true" />
                        </button>
                    </form>

                    <p v-if="shopCount" class="mt-4 text-sm text-muted">
                        {{ tr('Aktuálně vybíráte z', 'Choose from') }} <strong class="text-ink">{{ shopCount }}</strong> {{ tr('aktivních podniků.', 'active providers.') }}
                    </p>
                </div>

                <div class="relative overflow-hidden rounded-3xl border border-brand-100 bg-brand-800 p-7 text-white shadow-lift sm:p-9">
                    <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-white/10" aria-hidden="true" />
                    <CheckCircle2 :size="44" class="text-brand-100" :stroke-width="1.6" aria-hidden="true" />
                    <h2 class="mt-8 text-2xl font-extrabold tracking-tight">{{ tr('Jasná domluva od začátku', 'A clear agreement from the start') }}</h2>
                    <ul class="mt-5 space-y-4 text-sm leading-6 text-brand-50">
                        <li class="flex gap-3"><CalendarCheck2 :size="20" class="mt-0.5 flex-none" aria-hidden="true" /> {{ tr('Vidíte dostupný čas, cenu i délku služby před potvrzením.', 'See the available time, price, and duration before confirming.') }}</li>
                        <li class="flex gap-3"><HeartHandshake :size="20" class="mt-0.5 flex-none" aria-hidden="true" /> {{ tr('Rezervaci spravujete z účtu nebo přes bezpečný odkaz v e-mailu.', 'Manage the booking from your account or through a secure email link.') }}</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-extrabold uppercase tracking-[0.12em] text-brand-700">{{ tr('Kategorie', 'Categories') }}</p>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-ink sm:text-3xl">{{ tr('Co potřebujete zařídit?', 'What do you need help with?') }}</h2>
                </div>
                <Link :href="route('shops.index')" class="hidden min-h-11 items-center gap-1 rounded-xl px-3 text-sm font-bold text-brand-700 hover:bg-brand-50 sm:inline-flex">
                    {{ tr('Všechny služby', 'All services') }} <ArrowRight :size="17" aria-hidden="true" />
                </Link>
            </div>

            <div v-if="categories.length" class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                <Link
                    v-for="category in categories"
                    :key="category.id"
                    :href="route('shops.index', { categories: [category.slug] })"
                    class="group flex min-h-28 items-center gap-4 rounded-2xl border border-line bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-soft focus:outline-none focus:ring-4 focus:ring-brand-100"
                >
                    <span class="flex h-12 w-12 flex-none items-center justify-center rounded-2xl bg-brand-50 text-brand-700 transition group-hover:bg-brand-100">
                        <component :is="categoryIcon(category)" :size="24" :stroke-width="1.8" aria-hidden="true" />
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate font-extrabold text-ink">{{ category.name }}</span>
                        <span class="mt-1 block text-sm text-muted">{{ category.shops_count }} {{ tr('podniků', 'providers') }}</span>
                    </span>
                </Link>
            </div>
            <EmptyState v-else :title="tr('Kategorie se připravují', 'Categories are coming soon')" :description="tr('Jakmile přibudou první služby, najdete je tady.', 'The first services will appear here as soon as they are available.')" />
        </section>

        <section class="border-y border-line bg-white py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-extrabold uppercase tracking-[0.12em] text-brand-700">{{ tr('Doporučené podniky', 'Featured providers') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-ink sm:text-3xl">{{ tr('Objevte nabídky v katalogu', 'Explore the marketplace') }}</h2>
                    </div>
                    <Link :href="route('shops.index')" class="inline-flex min-h-11 items-center gap-1 rounded-xl px-3 text-sm font-bold text-brand-700 hover:bg-brand-50">
                        {{ tr('Zobrazit vše', 'View all') }} <ArrowRight :size="17" aria-hidden="true" />
                    </Link>
                </div>

                <div v-if="featuredShops.length" class="mt-7 grid gap-5 lg:grid-cols-2">
                    <ShopCard v-for="shop in featuredShops" :key="shop.id" :shop="shop" />
                </div>
                <EmptyState v-else :title="tr('Zatím tu nejsou žádné podniky', 'No providers yet')" :description="tr('Nové nabídky právě připravujeme.', 'New listings are being prepared.')" />
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-5 md:grid-cols-3">
                <article v-for="(step, index) in steps" :key="step[0]" class="rounded-2xl border border-line bg-white p-6 shadow-sm">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                        <component :is="step[2]" :size="22" aria-hidden="true" />
                    </div>
                    <p class="mt-5 text-xs font-extrabold uppercase tracking-[0.12em] text-brand-700">{{ tr('Krok', 'Step') }} {{ index + 1 }}</p>
                    <h3 class="mt-2 text-lg font-extrabold text-ink">{{ step[0] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-muted">{{ step[1] }}</p>
                </article>
            </div>

            <div class="mt-10 flex flex-col items-start justify-between gap-6 rounded-3xl bg-ink p-7 text-white sm:flex-row sm:items-center sm:p-9">
                <div>
                    <h2 class="text-2xl font-extrabold">{{ tr('Nabízíte místní služby?', 'Do you provide local services?') }}</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-300">{{ tr('Vytvořte profil podniku, nastavte služby a spravujte rezervace z jednoho místa.', 'Create a business profile, add services, and manage bookings in one place.') }}</p>
                </div>
                <Link :href="route('vendor.onboarding.index')" class="inline-flex min-h-12 flex-none items-center gap-2 rounded-xl bg-white px-5 font-extrabold text-ink transition hover:bg-brand-50 focus:outline-none focus:ring-4 focus:ring-white/30">
                    {{ tr('Začít jako poskytovatel', 'Get started as a provider') }} <ArrowRight :size="18" aria-hidden="true" />
                </Link>
            </div>
        </section>
    </AppLayout>
</template>
