<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';

const page = usePage();
const auth = page.props.auth;

const searchQuery = ref('');
const showUserMenu = ref(false);

// Check if current route is homepage
const isOnHomePage = computed(() => {
    return route().current() === 'home';
});

function handleSearch() {
    if (searchQuery.value.trim()) {
        router.get(route('services.index'), { q: searchQuery.value });
    }
}

function logout() {
    router.post(route('logout'));
}

function handleClickOutside(e) {
    if (!e.target.closest('#user-menu-wrapper')) {
        showUserMenu.value = false;
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center h-16 gap-4">

                <!-- Logo -->
                <Link :href="route('home')" class="flex items-center gap-2 shrink-0">
                    <ApplicationLogo />
                </Link>

                <!-- Search Bar -->
                <form @submit.prevent="handleSearch" class="flex-1 max-w-xl mx-auto">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="What service do you need?"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                </form>

                <!-- Right side -->
                <div class="flex items-center gap-3 ml-auto shrink-0">
                    <!-- Notification Bell -->
                    <NotificationDropdown v-if="auth?.user" />

                    <!-- User Avatar / Auth -->
                    <template v-if="auth?.user">
                        <div id="user-menu-wrapper" class="relative">
                            <button
                                @click.stop="showUserMenu = !showUserMenu"
                                class="w-9 h-9 bg-gray-300 rounded-full flex items-center justify-center hover:bg-gray-400 transition focus:outline-none"
                            >
                                <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                                </svg>
                            </button>

                            <!-- Dropdown -->
                            <div
                                v-if="showUserMenu"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                            >
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ auth.user.name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ auth.user.email }}</p>
                                </div>
                                <Link
                                    v-if="auth.user.is_admin"
                                    :href="route('admin.dashboard')"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                                    @click="showUserMenu = false"
                                >
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Admin Dashboard
                                </Link>
                                <Link
                                    v-if="auth.user.is_service_provider"
                                    :href="route('vendor.dashboard')"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                                    @click="showUserMenu = false"
                                >
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                                    </svg>
                                    Vendor Dashboard
                                </Link>
                                <Link
                                    :href="route('profile.edit')"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                                    @click="showUserMenu = false"
                                >
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Profile
                                </Link>
                                <Link
                                    :href="route('bookings.index')"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                                    @click="showUserMenu = false"
                                >
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    My Bookings
                                </Link>
                                <Link
                                    :href="route('bookmarks.index')"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                                    @click="showUserMenu = false"
                                >
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                    My Bookmarks
                                </Link>
                                <Link
                                    :href="route('reviews.user')"
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                                    @click="showUserMenu = false"
                                >
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                    My Reviews
                                </Link>
                                <button
                                    @click="logout"
                                    class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Log out
                                </button>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="text-sm font-medium text-gray-700 hover:text-blue-600">
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="text-sm font-medium bg-blue-600 text-white px-4 py-2 rounded-full hover:bg-blue-700 transition"
                        >
                            Sign up
                        </Link>
                    </template>
                </div>

            </div>
        </div>
    </header>
</template>
