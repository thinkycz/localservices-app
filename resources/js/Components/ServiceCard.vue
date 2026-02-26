<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import BookmarkButton from '@/Components/BookmarkButton.vue';
import StarRating from '@/Components/StarRating.vue';

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
});

const priceSymbol = computed(() => '$'.repeat(props.service.price_range));

const badgeClasses = computed(() => {
    const color = props.service.badge_color;
    return {
        blue:  'bg-blue-100 text-blue-700',
        gray:  'bg-gray-100 text-gray-700',
        green: 'bg-green-100 text-green-700',
    }[color] ?? 'bg-gray-100 text-gray-700';
});

const availabilityIcon = computed(() => {
    const text = props.service.available_at ?? '';
    return text.toLowerCase().includes('today') ? 'calendar' : 'clock';
});

const formattedReviews = computed(() => {
    const n = props.service.reviews_count;
    if (n >= 1000) return (n / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
    return n.toLocaleString();
});
</script>

<template>
    <Link
        :href="route('services.show', service.slug)"
        class="bg-white rounded-xl border border-gray-200 overflow-hidden flex hover:shadow-md hover:border-blue-300 transition-all duration-200 cursor-pointer group"
    >

        <!-- Image -->
        <div class="w-44 shrink-0 bg-gray-100">
            <img
                v-if="service.image"
                :src="service.image"
                :alt="service.name"
                class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100">
                <svg class="w-16 h-16 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                </svg>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 p-5 flex flex-col justify-between min-w-0">
            <div>
                <!-- Top row: badge + price + bookmark -->
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span
                            v-if="service.badge"
                            :class="badgeClasses"
                            class="text-xs font-semibold px-2 py-0.5 rounded uppercase tracking-wide"
                        >
                            {{ service.badge }}
                        </span>
                        <span class="text-sm font-medium text-gray-500">{{ priceSymbol }}</span>
                    </div>
                    <!-- Bookmark -->
                    <BookmarkButton
                        :service-id="service.id"
                        :initial-bookmarked="service.is_bookmarked"
                        size="sm"
                        @click.stop
                    />
                </div>

                <!-- Name -->
                <h3 class="text-lg font-bold text-gray-900 mb-1 leading-tight">
                    {{ service.name }}
                </h3>

                <!-- Rating -->
                <div class="flex items-center gap-2 mb-2">
                    <StarRating :rating="service.rating" size="sm" />
                    <span class="text-sm font-semibold text-gray-800">{{ service.rating }}</span>
                    <span class="text-sm text-gray-500">({{ formattedReviews }} reviews)</span>
                    <span class="text-gray-300">•</span>
                    <span class="text-sm text-gray-500">{{ service.category?.name }}</span>
                </div>

                <!-- Description -->
                <p class="text-sm text-gray-600 line-clamp-2">
                    {{ service.description }}
                </p>
            </div>

            <!-- Bottom row: availability -->
            <div class="flex items-center mt-4 pt-3 border-t border-gray-100">
                <!-- Availability -->
                <div class="flex items-center gap-1.5 text-sm text-green-600 font-medium">
                    <!-- Calendar icon for today -->
                    <svg v-if="availabilityIcon === 'calendar'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <!-- Clock icon for tomorrow/future -->
                    <svg v-else class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span :class="availabilityIcon === 'clock' ? 'text-gray-500' : 'text-green-600'">
                        {{ service.available_at }}
                    </span>
                </div>
            </div>
        </div>
    </Link>
</template>
