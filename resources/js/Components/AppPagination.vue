<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    meta: {
        type: Object,
        required: true,
    },
    links: {
        type: Array,
        required: true,
    },
});

// Build visible page numbers with ellipsis
const visiblePages = computed(() => {
    const current = props.meta.current_page;
    const last = props.meta.last_page;
    const pages = [];

    if (last <= 7) {
        for (let i = 1; i <= last; i++) pages.push(i);
        return pages;
    }

    pages.push(1);
    if (current > 3) pages.push('...');

    const start = Math.max(2, current - 1);
    const end = Math.min(last - 1, current + 1);
    for (let i = start; i <= end; i++) pages.push(i);

    if (current < last - 2) pages.push('...');
    pages.push(last);

    return pages;
});

function getPageUrl(page) {
    const link = props.links.find(l => l.label == page);
    return link?.url ?? null;
}
</script>

<template>
    <div class="flex flex-col items-center gap-4 mt-8">
        <!-- Load More (optional visual) -->
        <div class="text-sm text-gray-500">
            {{ meta.from }}–{{ meta.to }} of {{ meta.total }} results
        </div>

        <!-- Page numbers -->
        <div class="flex items-center gap-1">
            <!-- Prev -->
            <Link
                v-if="meta.current_page > 1"
                :href="links[0]?.url ?? '#'"
                class="px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition"
            >
                ‹
            </Link>

            <template v-for="page in visiblePages" :key="page">
                <span
                    v-if="page === '...'"
                    class="px-3 py-2 text-sm text-gray-400"
                >
                    ...
                </span>
                <Link
                    v-else
                    :href="getPageUrl(page) ?? '#'"
                    class="px-3 py-2 text-sm rounded-lg transition font-medium"
                    :class="page === meta.current_page
                        ? 'bg-blue-600 text-white'
                        : 'text-gray-600 hover:bg-gray-100'"
                >
                    {{ page }}
                </Link>
            </template>

            <!-- Next -->
            <Link
                v-if="meta.current_page < meta.last_page"
                :href="links[links.length - 1]?.url ?? '#'"
                class="px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition"
            >
                ›
            </Link>
        </div>
    </div>
</template>
