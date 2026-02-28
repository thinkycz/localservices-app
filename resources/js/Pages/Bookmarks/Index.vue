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

        <!-- Gradient Header -->
        <div class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">My Bookmarks</h1>
                        <p class="text-sm text-blue-200 mt-1">Services you've saved for later</p>
                    </div>
                    <Link
                        :href="route('services.index')"
                        class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 backdrop-blur border border-white/20 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        Browse Services
                    </Link>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Bookmarks Grid -->
            <div v-if="bookmarks.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div
                    v-for="bookmark in bookmarks.data"
                    :key="bookmark.id"
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all group"
                >
                    <!-- Service Image/Header -->
                    <div class="h-36 bg-gradient-to-br from-blue-600 to-indigo-700 relative overflow-hidden">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.1),transparent)]"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-14 h-14 bg-white/15 backdrop-blur rounded-2xl flex items-center justify-center">
                                <span class="text-white text-xl font-bold">{{ getInitials(bookmark.service.name) }}</span>
                            </div>
                        </div>
                        <!-- Remove Button -->
                        <button
                            @click="removeBookmark(bookmark.service.id)"
                            :disabled="removing[bookmark.service.id]"
                            class="absolute top-3 right-3 w-8 h-8 bg-white/15 hover:bg-white/30 backdrop-blur rounded-xl flex items-center justify-center transition-colors"
                            title="Remove from bookmarks"
                        >
                            <svg v-if="!removing[bookmark.service.id]" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            <svg v-else class="w-4 h-4 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                        <!-- Category badge -->
                        <div class="absolute bottom-3 left-3">
                            <span class="text-[10px] font-bold text-white bg-white/20 backdrop-blur px-2 py-1 rounded-lg">
                                {{ bookmark.service.category?.name || 'Service' }}
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold text-gray-900 text-sm line-clamp-1">{{ bookmark.service.name }}</h3>
                            <div v-if="bookmark.service.rating" class="flex items-center gap-1 shrink-0 ml-2">
                                <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span class="text-xs font-bold text-gray-700">{{ bookmark.service.rating }}</span>
                                <span class="text-[10px] text-gray-400">({{ bookmark.service.reviews_count }})</span>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 mb-3 line-clamp-2 leading-relaxed">
                            {{ bookmark.service.description || 'No description available' }}
                        </p>

                        <!-- Price -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-sm font-bold text-gray-900">
                                <span>
                                    {{ bookmark.service.offerings?.length > 0
                                        ? formatPrice(Math.min(...bookmark.service.offerings.map(o => o.price)))
                                        : 'Contact' }}
                                </span>
                                <span v-if="bookmark.service.offerings?.length > 1" class="text-gray-400 font-normal">
                                    – {{ formatPrice(Math.max(...bookmark.service.offerings.map(o => o.price))) }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <Link
                                :href="route('services.show', bookmark.service.slug)"
                                class="flex-1 bg-gray-50 text-gray-700 text-center py-2 rounded-xl text-xs font-semibold hover:bg-gray-100 transition-colors border border-gray-100"
                            >
                                View Details
                            </Link>
                            <Link
                                :href="route('services.book', bookmark.service.slug)"
                                class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-center py-2 rounded-xl text-xs font-semibold transition-all shadow-sm"
                            >
                                Book Now
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">No bookmarks yet</h3>
                <p class="text-gray-500 mb-6 max-w-sm mx-auto text-sm">
                    Save your favorite services to quickly find and book them later.
                </p>
                <Link
                    :href="route('services.index')"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-5 py-2.5 rounded-xl font-semibold transition-all shadow-sm text-sm"
                >
                    Browse Services
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="bookmarks.links && bookmarks.links.length > 3" class="mt-8 flex justify-center">
                <div class="flex gap-1.5">
                    <Link
                        v-for="(link, index) in bookmarks.links"
                        :key="index"
                        :href="link.url"
                        :class="[
                            'px-3.5 py-2 rounded-xl text-sm font-semibold transition-all',
                            link.active
                                ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-sm'
                                : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300',
                            !link.url && 'opacity-40 cursor-not-allowed pointer-events-none'
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
