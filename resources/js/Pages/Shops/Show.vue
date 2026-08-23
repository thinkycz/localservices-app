<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    CarFront,
    Check,
    Clock3,
    Dumbbell,
    Mail,
    MapPin,
    PawPrint,
    Phone,
    Scissors,
    Sparkles,
    Star,
    Store,
} from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import ShopCard from '@/Components/ShopCard.vue';

const props = defineProps({
    shop: { type: Object, required: true },
    related: { type: Array, default: () => [] },
});

const page = usePage();
const isEnglish = computed(() => page.props.locale === 'en');
const locale = computed(() => isEnglish.value ? 'en-US' : 'cs-CZ');
const tr = (czech, english) => isEnglish.value ? english : czech;
const selectedService = ref(null);
const services = computed(() => props.shop.services ?? []);
const reviews = computed(() => props.shop.approved_reviews ?? []);
const hours = computed(() => props.shop.business_hours ?? []);
const hasRating = computed(() => Number(props.shop.reviews_count ?? 0) > 0);
const categoryIcons = {
    scissors: Scissors,
    'car-front': CarFront,
    dumbbell: Dumbbell,
    'paw-print': PawPrint,
    sparkles: Sparkles,
};
const categoryArtwork = computed(() => categoryIcons[props.shop.category?.icon] ?? Store);
const badgeLabel = computed(() => ({
    NEW: tr('Novinka', 'New'),
    POPULAR: tr('Oblíbené', 'Popular'),
    'TOP RATED': tr('Nejlépe hodnocené', 'Top rated'),
}[props.shop.computed_badge?.text] ?? props.shop.computed_badge?.text));
const days = computed(() => isEnglish.value
    ? ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
    : ['Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota']);

function money(value) {
    return new Intl.NumberFormat(locale.value, {
        style: 'currency',
        currency: props.shop.currency ?? 'CZK',
        maximumFractionDigits: Number(value) % 1 === 0 ? 0 : 2,
    }).format(Number(value ?? 0));
}

function time(value) {
    return String(value ?? '').slice(0, 5);
}

function reviewDate(value) {
    return new Intl.DateTimeFormat(locale.value, { dateStyle: 'medium' }).format(new Date(value));
}

function initials(name) {
    return String(name || tr('Zákazník', 'Customer')).split(/\s+/).map((part) => part[0]).join('').slice(0, 2).toUpperCase();
}

function choose(service) {
    selectedService.value = service;
}

function continueToBooking(service = selectedService.value) {
    if (!service) return;
    router.get(route('shops.book', props.shop.slug), { service_id: service.id });
}
</script>

<template>
    <Head :title="shop.name" />

    <AppLayout>
        <section class="relative overflow-hidden border-b border-line bg-ink">
            <img v-if="shop.cover_image_url" :src="shop.cover_image_url" :alt="tr(`Provozovna ${shop.name}`, `${shop.name} shop`)" class="absolute inset-0 h-full w-full object-cover opacity-45">
            <div v-else class="absolute inset-0 bg-brand-800" aria-hidden="true">
                <component :is="categoryArtwork" class="absolute -right-12 -top-10 text-white/10" :size="260" :stroke-width="1" />
            </div>
            <div class="absolute inset-0 bg-ink/55" aria-hidden="true" />

            <div class="relative mx-auto flex min-h-80 max-w-7xl flex-col justify-end px-4 py-9 text-white sm:px-6 lg:px-8">
                <Link :href="route('shops.index')" class="mb-auto inline-flex min-h-11 w-fit items-center gap-2 rounded-xl bg-black/25 px-3 text-sm font-bold backdrop-blur hover:bg-black/40">
                    <ArrowLeft :size="17" aria-hidden="true" /> {{ tr('Zpět na výsledky', 'Back to results') }}
                </Link>
                <span v-if="badgeLabel" class="mb-3 w-fit rounded-full bg-white px-3 py-1 text-xs font-extrabold uppercase tracking-wide text-brand-800">{{ badgeLabel }}</span>
                <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ shop.name }}</h1>
                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-gray-200">
                    <span v-if="hasRating" class="inline-flex items-center gap-1.5"><Star :size="17" class="fill-accent text-accent" aria-hidden="true" /><strong class="text-white">{{ Number(shop.rating).toFixed(1) }}</strong> ({{ shop.reviews_count }} {{ tr('hodnocení', 'reviews') }})</span>
                    <span v-else>{{ tr('Zatím bez hodnocení', 'No reviews yet') }}</span>
                    <span v-if="shop.category?.name">{{ shop.category.name }}</span>
                    <span v-if="shop.city" class="inline-flex items-center gap-1.5"><MapPin :size="17" aria-hidden="true" />{{ shop.city }}</span>
                </div>
            </div>
        </section>

        <main class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:px-8">
            <div class="min-w-0 space-y-10">
                <section aria-labelledby="services-title">
                    <div>
                        <p class="text-sm font-extrabold uppercase tracking-[0.12em] text-brand-700">{{ tr('Nabídka', 'Services') }}</p>
                        <h2 id="services-title" class="mt-2 text-2xl font-extrabold tracking-tight text-ink">{{ tr('Vyberte službu', 'Choose a service') }}</h2>
                        <p class="mt-2 text-sm text-muted">{{ tr('Před pokračováním vždy uvidíte přesnou cenu, délku a dostupné termíny.', 'See the exact price, duration, and available times before continuing.') }}</p>
                    </div>

                    <div v-if="services.length" class="mt-6 space-y-3">
                        <button
                            v-for="service in services"
                            :key="service.id"
                            type="button"
                            class="flex min-h-24 w-full items-center justify-between gap-5 rounded-2xl border bg-white p-5 text-left shadow-sm transition focus:outline-none focus:ring-4 focus:ring-brand-100"
                            :class="selectedService?.id === service.id ? 'border-brand-600 ring-1 ring-brand-600' : 'border-line hover:border-brand-300'"
                            :aria-pressed="selectedService?.id === service.id"
                            @click="choose(service)"
                        >
                            <span class="min-w-0">
                                <span class="block font-extrabold text-ink">{{ service.name }}</span>
                                <span v-if="service.description" class="mt-1 block text-sm leading-5 text-muted">{{ service.description }}</span>
                                <span class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-muted"><Clock3 :size="15" aria-hidden="true" />{{ service.duration_minutes }} min</span>
                            </span>
                            <span class="flex flex-none items-center gap-3">
                                <strong class="text-base text-ink">{{ money(service.price) }}</strong>
                                <span class="flex h-8 w-8 items-center justify-center rounded-full border" :class="selectedService?.id === service.id ? 'border-brand-700 bg-brand-700 text-white' : 'border-line text-transparent'">
                                    <Check :size="17" :stroke-width="3" aria-hidden="true" />
                                </span>
                            </span>
                        </button>
                    </div>
                    <EmptyState v-else :title="tr('Služby se připravují', 'Services are coming soon')" :description="tr('Tento podnik zatím nezveřejnil žádnou aktivní službu.', 'This provider has not published an active service yet.')" />
                </section>

                <section v-if="shop.description" aria-labelledby="about-title" class="rounded-2xl border border-line bg-white p-6 shadow-sm">
                    <h2 id="about-title" class="text-xl font-extrabold text-ink">{{ tr('O podniku', 'About this provider') }}</h2>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-muted">{{ shop.description }}</p>
                </section>

                <section aria-labelledby="reviews-title">
                    <div class="flex items-center justify-between gap-4">
                        <h2 id="reviews-title" class="text-2xl font-extrabold tracking-tight text-ink">{{ tr('Hodnocení zákazníků', 'Customer reviews') }}</h2>
                        <span v-if="hasRating" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-50 px-3 py-2 font-extrabold text-amber-900"><Star :size="18" class="fill-accent text-accent" aria-hidden="true" />{{ Number(shop.rating).toFixed(1) }}</span>
                    </div>

                    <div v-if="reviews.length" class="mt-6 grid gap-4 md:grid-cols-2">
                        <article v-for="review in reviews" :key="review.id" class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-brand-50 text-sm font-extrabold text-brand-800">{{ initials(review.user?.name) }}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-extrabold text-ink">{{ review.user?.name || tr('Zákazník Domluveno', 'Domluveno customer') }}</p>
                                    <p class="text-xs text-muted">{{ reviewDate(review.reviewed_at) }}</p>
                                </div>
                            </div>
                            <div class="mt-4 flex gap-0.5 text-accent" role="img" :aria-label="tr(`${review.rating} z 5 hvězdiček`, `${review.rating} out of 5 stars`)">
                                <Star v-for="star in 5" :key="star" :size="16" :class="star <= review.rating ? 'fill-accent' : 'text-line'" aria-hidden="true" />
                            </div>
                            <p v-if="review.comment" class="mt-3 text-sm leading-6 text-muted">{{ review.comment }}</p>
                        </article>
                    </div>
                    <EmptyState v-else :title="tr('Zatím bez hodnocení', 'No reviews yet')" :description="tr('První hodnocení mohou přidat zákazníci po dokončené rezervaci.', 'Customers can add the first review after a completed booking.')" />
                </section>

                <section v-if="related.length" aria-labelledby="related-title">
                    <h2 id="related-title" class="text-2xl font-extrabold tracking-tight text-ink">{{ tr('Podobné podniky', 'Similar providers') }}</h2>
                    <div class="mt-6 grid gap-5 xl:grid-cols-2">
                        <ShopCard v-for="item in related" :key="item.id" :shop="item" />
                    </div>
                </section>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-2xl border border-line bg-white p-5 shadow-soft">
                    <h2 class="text-lg font-extrabold text-ink">{{ tr('Pokračovat k rezervaci', 'Continue to booking') }}</h2>
                    <template v-if="selectedService">
                        <div class="mt-4 rounded-xl bg-canvas p-4">
                            <p class="font-extrabold text-ink">{{ selectedService.name }}</p>
                            <div class="mt-2 flex items-center justify-between text-sm text-muted">
                                <span>{{ selectedService.duration_minutes }} min</span>
                                <strong class="text-ink">{{ money(selectedService.price) }}</strong>
                            </div>
                        </div>
                        <button type="button" class="ui-button ui-button-primary mt-4 w-full" @click="continueToBooking()">
                            <CalendarDays :size="18" aria-hidden="true" /> {{ tr('Vybrat termín', 'Choose a time') }}
                        </button>
                    </template>
                    <p v-else class="mt-3 text-sm leading-6 text-muted">{{ tr('Nejdříve vyberte jednu ze služeb vlevo.', 'Choose one of the services first.') }}</p>
                    <p class="mt-4 border-t border-line pt-4 text-xs leading-5 text-muted">{{ tr('Rezervovat můžete s účtem i bez něj. Časy se zobrazují v pásmu', 'You can book with or without an account. Times are shown in') }} {{ shop.timezone || 'Europe/Prague' }}.</p>
                </div>

                <div class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                    <h2 class="font-extrabold text-ink">{{ tr('Kde nás najdete', 'Where to find us') }}</h2>
                    <address class="mt-3 space-y-3 text-sm not-italic text-muted">
                        <p v-if="shop.address || shop.city" class="flex gap-2"><MapPin :size="18" class="mt-0.5 flex-none text-brand-700" aria-hidden="true" /><span>{{ shop.address }}<br v-if="shop.address && shop.city">{{ shop.city }}</span></p>
                        <a v-if="shop.contact_phone" :href="`tel:${shop.contact_phone}`" class="flex min-h-11 items-center gap-2 hover:text-brand-700"><Phone :size="18" class="flex-none text-brand-700" aria-hidden="true" />{{ shop.contact_phone }}</a>
                        <a v-if="shop.contact_email" :href="`mailto:${shop.contact_email}`" class="flex min-h-11 items-center gap-2 break-all hover:text-brand-700"><Mail :size="18" class="flex-none text-brand-700" aria-hidden="true" />{{ shop.contact_email }}</a>
                    </address>
                </div>

                <div v-if="hours.length" class="rounded-2xl border border-line bg-white p-5 shadow-sm">
                    <h2 class="font-extrabold text-ink">{{ tr('Otevírací doba', 'Business hours') }}</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div v-for="item in hours" :key="item.id" class="flex justify-between gap-3">
                            <dt class="text-muted">{{ days[item.day_of_week] }}</dt>
                            <dd class="font-bold text-ink">{{ item.is_closed ? tr('Zavřeno', 'Closed') : `${time(item.time_from)}–${time(item.time_to)}` }}</dd>
                        </div>
                    </dl>
                </div>
            </aside>
        </main>
    </AppLayout>
</template>
