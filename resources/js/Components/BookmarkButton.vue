<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    serviceId: {
        type: Number,
        required: true,
    },
    initialBookmarked: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg
    },
});

const isBookmarked = ref(props.initialBookmarked);
const isLoading = ref(false);

const sizeClasses = {
    sm: 'w-8 h-8',
    md: 'w-10 h-10',
    lg: 'w-12 h-12',
};

const iconSizes = {
    sm: 'w-4 h-4',
    md: 'w-5 h-5',
    lg: 'w-6 h-6',
};

onMounted(() => {
    // Check bookmark status if not provided
    if (!props.initialBookmarked) {
        checkBookmarkStatus();
    }
});

function checkBookmarkStatus() {
    fetch(route('bookmarks.check', props.serviceId), {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    })
    .then(res => res.json())
    .then(data => {
        isBookmarked.value = data.bookmarked;
    })
    .catch(() => {
        // Silently fail - user might not be logged in
    });
}

function getCsrfToken() {
    // Try multiple methods to get CSRF token
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }
    
    // Check window.Laravel (common in Laravel apps)
    if (window.Laravel && window.Laravel.csrfToken) {
        return window.Laravel.csrfToken;
    }
    
    return '';
}

function toggleBookmark() {
    if (isLoading.value) return;
    
    isLoading.value = true;
    
    const csrfToken = getCsrfToken();
    
    fetch(route('bookmarks.toggle', props.serviceId), {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    })
    .then(res => {
        if (res.status === 401) {
            // Redirect to login
            window.location.href = route('login');
            return;
        }
        return res.json();
    })
    .then(data => {
        if (data) {
            isBookmarked.value = data.bookmarked;
        }
    })
    .catch(err => {
        console.error('Bookmark error:', err);
    })
    .finally(() => {
        isLoading.value = false;
    });
}
</script>

<template>
    <button
        @click="toggleBookmark"
        :disabled="isLoading"
        :class="[
            sizeClasses[size],
            'rounded-full flex items-center justify-center transition-all duration-200',
            isBookmarked
                ? 'bg-red-50 hover:bg-red-100'
                : 'bg-white/90 hover:bg-white shadow-sm',
            isLoading && 'opacity-70 cursor-not-allowed'
        ]"
        :title="isBookmarked ? 'Remove from bookmarks' : 'Add to bookmarks'"
    >
        <svg
            :class="[
                iconSizes[size],
                'transition-colors duration-200',
                isBookmarked ? 'text-red-500 fill-current' : 'text-gray-400 hover:text-red-400'
            ]"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
            fill="none"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
            />
        </svg>
    </button>
</template>
