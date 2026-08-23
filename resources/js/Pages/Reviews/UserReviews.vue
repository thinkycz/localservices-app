<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import StarRating from '@/Components/StarRating.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { CalendarDays, MessageSquareText, Star } from '@lucide/vue';

const props = defineProps({ reviews: Object });
const page = usePage();
const isCzech = computed(() => page.props.locale === 'cs');
const copy = (cs, en) => isCzech.value ? cs : en;
const locale = computed(() => isCzech.value ? 'cs-CZ' : 'en-GB');
const tagCopy = { Professional: 'Profesionální', 'On-time': 'Dochvilné', 'Quality Shop': 'Kvalitní služba', Friendly: 'Příjemné jednání', Clean: 'Čisté prostředí', 'Good Value': 'Dobrá hodnota', Expert: 'Odborné', Recommended: 'Doporučuji' };
const formatDate = (date) => new Intl.DateTimeFormat(locale.value, { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(`${String(date).slice(0, 10)}T12:00:00`));
const formatTime = (time) => String(time ?? '').slice(0, 5);
const displayTag = (tag) => isCzech.value ? (tagCopy[tag] ?? tag) : tag;
</script>

<template>
    <AppLayout>
        <Head :title="copy('Moje recenze', 'My reviews')" />

        <section class="border-b border-line bg-white">
            <div class="ui-container py-8 sm:flex sm:items-end sm:justify-between sm:gap-5 sm:py-10">
                <div>
                    <p class="text-sm font-bold text-brand-700">{{ copy('Vaše zkušenosti', 'Your experiences') }}</p>
                    <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-ink">{{ copy('Moje recenze', 'My reviews') }}</h1>
                    <p class="mt-2 text-sm leading-6 text-muted">{{ copy('Přehled hodnocení, která jste napsali po dokončených službách.', 'Reviews you wrote after completed services.') }}</p>
                </div>
                <Link :href="route('bookings.index')" class="ui-button ui-button-secondary mt-5 sm:mt-0"><CalendarDays :size="18" aria-hidden="true" />{{ copy('Moje rezervace', 'My bookings') }}</Link>
            </div>
        </section>

        <main id="main-content" class="ui-container py-8 sm:py-10">
            <div v-if="props.reviews.data?.length" class="mx-auto max-w-4xl space-y-4">
                <article v-for="review in props.reviews.data" :key="review.id" class="ui-card p-5 sm:p-6">
                    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <Link :href="route('shops.show', review.shop.slug)" class="text-base font-bold text-ink hover:text-brand-700">{{ review.shop.name }}</Link>
                            <p class="mt-1 text-xs text-muted">{{ copy('Napsáno', 'Reviewed') }} {{ formatDate(review.reviewed_at) }}</p>
                        </div>
                        <StarRating :rating="review.rating" size="sm" class="shrink-0" />
                    </header>
                    <div v-if="review.tags?.length" class="mt-4 flex flex-wrap gap-2">
                        <span v-for="tag in review.tags" :key="tag" class="rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-800">{{ displayTag(tag) }}</span>
                    </div>
                    <p class="mt-4 whitespace-pre-line text-sm leading-7 text-ink">{{ review.comment }}</p>
                    <footer class="mt-5 flex flex-wrap gap-x-5 gap-y-2 border-t border-line pt-4 text-xs text-muted">
                        <span class="flex items-center gap-1.5"><CalendarDays :size="15" aria-hidden="true" />{{ formatDate(review.booking.booking_date) }}</span>
                        <span>{{ formatTime(review.booking.start_time) }}</span>
                    </footer>
                </article>
            </div>

            <section v-else class="ui-card mx-auto max-w-2xl p-8 text-center sm:p-12">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-accent/10 text-accent" aria-hidden="true"><Star :size="27" /></span>
                <h2 class="mt-5 text-xl font-bold text-ink">{{ copy('Zatím jste nic nehodnotili', 'No reviews yet') }}</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-muted">{{ copy('Po dokončení služby můžete z historie rezervací napsat recenzi a pomoci ostatním s výběrem.', 'After a completed service, write a review from booking history to help others choose.') }}</p>
                <Link :href="route('bookings.index')" class="ui-button ui-button-primary mt-6"><MessageSquareText :size="18" aria-hidden="true" />{{ copy('Otevřít rezervace', 'Open bookings') }}</Link>
            </section>

            <nav v-if="props.reviews.links?.length > 3" class="mt-8 flex flex-wrap justify-center gap-2" :aria-label="copy('Stránkování recenzí', 'Review pagination')">
                <Link v-for="(link, index) in props.reviews.links" :key="index" :href="link.url || '#'" preserve-scroll class="ui-button min-w-11 px-3" :class="[link.active ? 'ui-button-primary' : 'ui-button-secondary', !link.url ? 'pointer-events-none opacity-45' : '']" :aria-current="link.active ? 'page' : undefined" :aria-disabled="!link.url" v-html="link.label" />
            </nav>
        </main>
    </AppLayout>
</template>
