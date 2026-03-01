<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import StarRating from '@/Components/StarRating.vue';

const props = defineProps({
    service: { type: Object, required: true },
});

const priceSymbol = computed(() => '$'.repeat(props.service.price_range));

const badgeClasses = computed(() => ({
    blue:  'bg-blue-50 text-blue-700 ring-blue-600/20',
    gray:  'bg-gray-50 text-gray-600 ring-gray-500/10',
    green: 'bg-green-50 text-green-700 ring-green-600/20',
}[props.service.badge_color] ?? 'bg-gray-50 text-gray-600 ring-gray-500/10'));

const formattedReviews = computed(() => {
    const n = props.service.reviews_count;
    if (n >= 1000) return (n / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
    return n.toLocaleString();
});
</script>

<template>
    <Link
        :href="route('services.show', service.slug)"
        class="bg-white rounded-xl border border-gray-100 overflow-hidden flex hover:shadow-md hover:border-gray-200 transition-all duration-200 cursor-pointer group"
    >
        <!-- Image -->
        <div class="w-32 sm:w-36 shrink-0 bg-gray-100 relative overflow-hidden">
            <img
                v-if="service.image"
                :src="service.image"
                :alt="service.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            />
            <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100">
                <svg class="w-10 h-10 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                </svg>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 p-4 flex flex-col justify-between min-w-0">
            <div>
                <!-- Top row -->
                <div class="flex items-start justify-between gap-2 mb-1">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span
                            v-if="service.badge"
                            :class="badgeClasses"
                            class="text-[10px] font-bold px-1.5 py-0.5 rounded ring-1 ring-inset uppercase tracking-wider"
                        >{{ service.badge }}</span>
                        <span class="text-[11px] text-gray-400 font-medium">{{ service.category?.name }}</span>
                    </div>
                </div>

                <!-- Name -->
                <h3 class="text-sm font-bold text-gray-900 mb-1 leading-snug group-hover:text-blue-700 transition-colors line-clamp-1">
                    {{ service.name }}
                </h3>

                <!-- Rating row -->
                <div class="flex items-center gap-1.5 mb-1.5">
                    <div class="flex items-center gap-1 text-xs">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="font-bold text-gray-800">{{ service.rating }}</span>
                        <span class="text-gray-400">({{ formattedReviews }})</span>
                    </div>
                    <span class="text-gray-300">·</span>
                    <span class="text-xs text-gray-400 font-medium">{{ priceSymbol }}</span>
                </div>

                <!-- Description -->
                <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">{{ service.description }}</p>
            </div>

            <!-- Bottom availability -->
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-50">
                <span v-if="service.available_at" class="text-[11px] font-medium text-green-600 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ service.available_at }}
                </span>
                <span class="text-[11px] font-semibold text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-0.5">
                    View
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            </div>
        </div>
    </Link>
</template>
