<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import StarRating from '@/Components/StarRating.vue';

const props = defineProps({
    bookmarks: Object,
});

const removing = ref({});

function removeBookmark(serviceId) {
    removing.value[serviceId] = true;
    router.delete(route('bookmarks.destroy', serviceId), {
        preserveScroll: true,
        onFinish: () => {
            removing.value[serviceId] = false;
        },
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
}

function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
}
</script>

<template>
    <AppLayout>
        <Head title="My Bookmarks" />

        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">My Bookmarks</h1>
                    <p class="text-gray-600 mt-2">Services you've saved for later</p>
                </div>

                <!-- Bookmarks Grid -->
                <div v-if="bookmarks.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="bookmark in bookmarks.data"
                        :key="bookmark.id"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow"
                    >
                        <!-- Service Image/Header -->
                        <div class="h-40 bg-gradient-to-br from-blue-500 to-purple-600 relative">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                                    <span class="text-white text-2xl font-bold">{{ getInitials(bookmark.service.name) }}</span>
                                </div>
                            </div>
                            <!-- Remove Button -->
                            <button
                                @click="removeBookmark(bookmark.service.id)"
                                :disabled="removing[bookmark.service.id]"
                                class="absolute top-3 right-3 w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center transition-colors"
                                title="Remove from bookmarks"
                            >
                                <svg v-if="!removing[bookmark.service.id]" class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                <svg v-else class="w-4 h-4 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">
                                        {{ bookmark.service.category?.name || 'Service' }}
                                    </span>
                                </div>
                                <div v-if="bookmark.service.rating" class="flex items-center gap-1">
                                    <StarRating :rating="Math.round(bookmark.service.rating)" size="sm" />
                                    <span class="text-sm text-gray-600">({{ bookmark.service.reviews_count }})</span>
                                </div>
                            </div>

                            <h3 class="font-bold text-gray-900 mb-2 line-clamp-1">{{ bookmark.service.name }}</h3>
                            
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                {{ bookmark.service.description || 'No description available' }}
                            </p>

                            <!-- Price Range -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-sm text-gray-600">
                                    <span class="font-medium text-gray-900">
                                        {{ bookmark.service.offerings?.length > 0 
                                            ? formatPrice(Math.min(...bookmark.service.offerings.map(o => o.price)))
                                            : 'Contact for price' }}
                                    </span>
                                    <span v-if="bookmark.service.offerings?.length > 1">
                                        - {{ formatPrice(Math.max(...bookmark.service.offerings.map(o => o.price))) }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ bookmark.service.city }}, {{ bookmark.service.state }}
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <Link
                                    :href="route('services.show', bookmark.service.slug)"
                                    class="flex-1 bg-gray-100 text-gray-700 text-center py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors"
                                >
                                    View Details
                                </Link>
                                <Link
                                    :href="route('services.book', bookmark.service.slug)"
                                    class="flex-1 bg-blue-600 text-white text-center py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors"
                                >
                                    Book Now
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No bookmarks yet</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        Save your favorite services to quickly find and book them later. Click the bookmark icon on any service to add it here.
                    </p>
                    <Link
                        :href="route('services.index')"
                        class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors"
                    >
                        Browse Services
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="bookmarks.links && bookmarks.links.length > 3" class="mt-8 flex justify-center">
                    <div class="flex gap-2">
                        <Link
                            v-for="(link, index) in bookmarks.links"
                            :key="index"
                            :href="link.url"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium',
                                link.active
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50',
                                !link.url && 'opacity-50 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
