<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';

const props = defineProps({
    activePage: {
        type: String,
        default: 'dashboard',
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const userInitials = computed(() => {
    if (!user.value?.name) return 'AJ';
    return user.value.name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});

const showUserMenu = ref(false);

function logout() {
    router.post(route('logout'));
}

function handleClickOutside(e) {
    if (!e.target.closest('#vendor-user-menu-wrapper')) {
        showUserMenu.value = false;
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));

function navClass(name) {
    return props.activePage === name
        ? 'flex items-center gap-3 px-3 py-2.5 rounded-xl bg-blue-600 text-white font-medium text-sm'
        : 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-500 hover:bg-gray-50 hover:text-gray-700 font-medium text-sm transition-colors';
}
</script>

<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden">
        <!-- ===== Sidebar ===== -->
        <aside class="w-60 bg-white flex flex-col border-r border-gray-100 flex-shrink-0">
            <!-- Logo -->
            <div class="px-5 py-6 border-b border-gray-50">
                <Link :href="route('home')">
                    <ApplicationLogo />
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-0.5">
                <!-- Dashboard -->
                <Link :href="route('vendor.dashboard')" :class="navClass('dashboard')">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                    </svg>
                    Dashboard
                </Link>

                <!-- Calendar -->
                <Link :href="route('vendor.calendar')" :class="navClass('calendar')">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Calendar
                </Link>

                <!-- Bookings -->
                <Link :href="route('vendor.bookings.index')" :class="navClass('bookings')">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Bookings
                </Link>

                <!-- Customers -->
                <Link :href="route('vendor.customers.index')" :class="navClass('customers')">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Customers
                </Link>

                <!-- Services -->
                <Link :href="route('vendor.services.index')" :class="navClass('services')">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Services
                </Link>

            </nav>

        </aside>

        <!-- ===== Main Content Area ===== -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header
                class="bg-white border-b border-gray-100 px-6 h-16 flex items-center justify-between flex-shrink-0"
            >
                <!-- Search -->
                <div class="relative flex-1 max-w-md">
                    <svg
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                    <input
                        type="text"
                        placeholder="Search appointments, customers..."
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>

                <!-- Right side: Bell + User -->
                <div class="flex items-center gap-4 ml-6">
                    <!-- Notification Bell -->
                    <NotificationDropdown />

                    <!-- Divider -->
                    <div class="w-px h-8 bg-gray-200"></div>

                    <!-- User Info -->
                    <div id="vendor-user-menu-wrapper" class="relative flex items-center gap-3">
                        <button
                            @click.stop="showUserMenu = !showUserMenu"
                            class="flex items-center gap-3 focus:outline-none"
                        >
                            <div class="text-right">
                                <div class="text-sm font-semibold text-gray-900 leading-tight">
                                    {{ user?.name || 'Alex Johnson' }}
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">Owner</div>
                            </div>
                            <div
                                class="w-9 h-9 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0"
                            >
                                {{ userInitials }}
                            </div>
                        </button>

                        <!-- Dropdown -->
                        <div
                            v-if="showUserMenu"
                            class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50"
                        >
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ user?.name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                            </div>
                            <Link
                                v-if="user?.is_admin"
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
                                :href="route('home')"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                                @click="showUserMenu = false"
                            >
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/>
                                </svg>
                                Client Area
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
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-auto bg-gray-50 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
