<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';

const props = defineProps({
    meta: { type: Object, required: true },
    links: { type: Array, required: true },
});

const currentPage = computed(() => Number(props.meta.current_page ?? 1));
const lastPage = computed(() => Number(props.meta.last_page ?? 1));
const visiblePages = computed(() => {
    const pages = [];

    if (lastPage.value <= 7) {
        for (let page = 1; page <= lastPage.value; page += 1) pages.push(page);
        return pages;
    }

    pages.push(1);
    if (currentPage.value > 3) pages.push('start-ellipsis');

    const start = Math.max(2, currentPage.value - 1);
    const end = Math.min(lastPage.value - 1, currentPage.value + 1);
    for (let page = start; page <= end; page += 1) pages.push(page);

    if (currentPage.value < lastPage.value - 2) pages.push('end-ellipsis');
    pages.push(lastPage.value);
    return pages;
});

const previousUrl = computed(() => props.links[0]?.url ?? null);
const nextUrl = computed(() => props.links[props.links.length - 1]?.url ?? null);

function pageUrl(page) {
    return props.links.find((link) => Number(link.label) === page)?.url ?? null;
}
</script>

<template>
    <nav v-if="lastPage > 1" class="mt-8 flex flex-col items-center gap-4" :aria-label="$t('Pagination')">
        <p class="text-sm text-muted">
            {{ meta.from ?? 0 }}–{{ meta.to ?? 0 }} {{ $t('of') }} {{ meta.total ?? 0 }}
        </p>

        <div class="flex flex-wrap items-center justify-center gap-1">
            <Link v-if="previousUrl" :href="previousUrl" class="ui-icon-button" rel="prev" :aria-label="$t('Previous page')">
                <ChevronLeft :size="19" aria-hidden="true" />
            </Link>
            <span v-else class="h-11 w-11" aria-hidden="true"></span>

            <template v-for="page in visiblePages" :key="page">
                <span v-if="typeof page === 'string'" class="flex h-11 min-w-8 items-center justify-center px-1 text-sm text-muted" aria-hidden="true">…</span>
                <Link
                    v-else
                    :href="pageUrl(page) ?? '#'"
                    class="flex h-11 min-w-11 items-center justify-center rounded-xl px-2 text-sm font-bold transition"
                    :class="page === currentPage ? 'bg-brand-600 text-white' : 'text-muted hover:bg-brand-50 hover:text-brand-800'"
                    :aria-current="page === currentPage ? 'page' : undefined"
                    :aria-label="`${$t('Page')} ${page}`"
                >{{ page }}</Link>
            </template>

            <Link v-if="nextUrl" :href="nextUrl" class="ui-icon-button" rel="next" :aria-label="$t('Next page')">
                <ChevronRight :size="19" aria-hidden="true" />
            </Link>
            <span v-else class="h-11 w-11" aria-hidden="true"></span>
        </div>
    </nav>
</template>
