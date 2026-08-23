<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, CarFront, CheckCircle2, Dumbbell, PawPrint, Scissors, Sparkles, Star, Store } from '@lucide/vue';

const props = defineProps({
    shop: { type: Object, required: true },
});

const page = usePage();
const badgeLabel = computed(() => {
    const labels = {
        NEW: { cs: 'Nové', en: 'New' },
        POPULAR: { cs: 'Oblíbené', en: 'Popular' },
        'TOP RATED': { cs: 'Nejlépe hodnocené', en: 'Top rated' },
    };
    const label = labels[props.shop.computed_badge?.text];

    return label ? label[page.props.locale === 'en' ? 'en' : 'cs'] : props.shop.computed_badge?.text;
});

const badgeClasses = computed(() => ({
    blue: 'bg-brand-50 text-brand-800 ring-brand-600/20',
    gray: 'bg-gray-100 text-gray-700 ring-gray-500/15',
    green: 'bg-green-50 text-green-800 ring-green-600/20',
    amber: 'bg-amber-50 text-amber-800 ring-amber-600/20',
}[props.shop.computed_badge?.color] ?? 'bg-gray-100 text-gray-700 ring-gray-500/15'));

const reviewCount = computed(() => Number(props.shop.reviews_count ?? 0));
const formattedReviews = computed(() => new Intl.NumberFormat(undefined, { notation: reviewCount.value >= 1000 ? 'compact' : 'standard' }).format(reviewCount.value));
const rating = computed(() => Number(props.shop.rating ?? 0));
const hasRating = computed(() => reviewCount.value > 0 && rating.value > 0);
const categoryIcons = {
    scissors: Scissors,
    'car-front': CarFront,
    dumbbell: Dumbbell,
    'paw-print': PawPrint,
    sparkles: Sparkles,
};
const fallbackIcon = computed(() => categoryIcons[props.shop.category?.icon] ?? Store);
</script>

<template>
    <Link
        :href="route('shops.show', shop.slug)"
        class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-line bg-white shadow-soft transition duration-200 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-lift sm:flex-row"
    >
        <div class="relative h-44 flex-none overflow-hidden bg-brand-50 sm:h-auto sm:min-h-48 sm:w-40">
            <img
                v-if="shop.cover_image_url"
                :src="shop.cover_image_url"
                :alt="shop.name"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                loading="lazy"
            />
            <div v-else class="relative flex h-full w-full items-center justify-center overflow-hidden bg-brand-800 text-brand-100" aria-hidden="true">
                <component :is="fallbackIcon" :size="48" :stroke-width="1.4" />
                <component :is="fallbackIcon" class="absolute -bottom-8 -right-8 text-white/10" :size="124" :stroke-width="1" />
            </div>
        </div>

        <div class="flex min-w-0 flex-1 flex-col p-4 sm:p-5">
            <div class="flex flex-wrap items-center gap-2">
                <span v-if="shop.computed_badge" class="rounded-full px-2 py-1 text-[10px] font-extrabold uppercase tracking-[0.08em] ring-1 ring-inset" :class="badgeClasses">
                    {{ badgeLabel }}
                </span>
                <span v-if="shop.category?.name" class="text-xs font-semibold text-muted">{{ shop.category.name }}</span>
            </div>

            <h3 class="mt-2 truncate text-base font-extrabold tracking-[-0.02em] text-ink transition group-hover:text-brand-700">
                {{ shop.name }}
            </h3>

            <div class="mt-2 flex min-h-5 items-center gap-1.5 text-xs">
                <template v-if="hasRating">
                    <Star :size="16" class="fill-accent text-accent" aria-hidden="true" />
                    <span class="font-extrabold text-ink">{{ rating.toFixed(1) }}</span>
                    <span class="text-muted">({{ formattedReviews }})</span>
                </template>
                <span v-else class="text-muted">{{ $t('No reviews yet') }}</span>
            </div>

            <p v-if="shop.description" class="mt-3 line-clamp-2 text-sm leading-5 text-muted">{{ shop.description }}</p>

            <div class="mt-auto flex items-end justify-between gap-3 border-t border-line pt-4" :class="shop.description ? 'mt-4' : 'mt-5'">
                <span v-if="shop.available_at" class="inline-flex min-w-0 items-center gap-1.5 text-xs font-bold text-success">
                    <CheckCircle2 :size="15" class="flex-none" aria-hidden="true" />
                    <span class="truncate">{{ shop.available_at }}</span>
                </span>
                <span v-else></span>
                <span class="inline-flex flex-none items-center gap-1 text-xs font-bold text-brand-700">
                    {{ $t('View') }}<ArrowRight :size="15" aria-hidden="true" />
                </span>
            </div>
        </div>
    </Link>
</template>
