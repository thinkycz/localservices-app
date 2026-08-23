<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    ChevronDown,
    LayoutDashboard,
    LogOut,
    Search,
    Star,
    Store,
    UserRound,
} from '@lucide/vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const isAuthenticated = computed(() => Boolean(user.value));
const showSiteSearch = computed(() => page.component !== 'Home');
const searchQuery = ref(page.props.filters?.q ?? '');
const showUserMenu = ref(false);

const userInitials = computed(() => {
    const name = user.value?.name?.trim();
    if (!name) return 'D';

    return name
        .split(/\s+/)
        .map((part) => part.charAt(0))
        .join('')
        .toUpperCase()
        .slice(0, 2);
});

function handleSearch() {
    const q = searchQuery.value.trim();
    router.get(route('shops.index'), q ? { q } : {});
}

function logout() {
    showUserMenu.value = false;
    router.post(route('logout'));
}

function handleClickOutside(event) {
    if (!event.target.closest('[data-customer-user-menu]')) {
        showUserMenu.value = false;
    }
}

function closeOnEscape(event) {
    if (event.key === 'Escape') showUserMenu.value = false;
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', closeOnEscape);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', closeOnEscape);
});
</script>

<template>
    <header class="sticky top-0 z-40 border-b border-line bg-white/95 backdrop-blur">
        <a href="#main-content" class="sr-only left-4 top-3 z-[60] min-h-11 items-center rounded-lg bg-white px-4 py-2 font-bold text-brand-700 focus:not-sr-only focus:fixed focus:flex">
            {{ $t('Skip to content') }}
        </a>

        <div class="ui-container">
            <div class="flex h-16 min-w-0 items-center gap-3 sm:gap-4">
                <Link :href="route('home')" class="flex min-h-11 min-w-0 flex-none items-center rounded-xl focus-visible:outline-none">
                    <ApplicationLogo />
                </Link>

                <form v-if="showSiteSearch" class="mx-auto hidden min-w-0 max-w-xl flex-1 md:block" role="search" @submit.prevent="handleSearch">
                    <label for="desktop-site-search" class="sr-only">{{ $t('Search services or places') }}</label>
                    <div class="relative">
                        <Search class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" :size="19" aria-hidden="true" />
                        <input
                            id="desktop-site-search"
                            v-model="searchQuery"
                            type="search"
                            :placeholder="$t('Service or place')"
                            class="ui-field rounded-full pl-11 pr-4"
                        />
                    </div>
                </form>

                <nav class="ml-auto flex flex-none items-center gap-1 sm:gap-2" :aria-label="$t('Account navigation')">
                    <Link :href="route('shops.index')" class="hidden min-h-11 items-center rounded-xl px-3 text-sm font-semibold text-muted hover:bg-gray-50 hover:text-ink lg:flex">
                        {{ $t('Browse Shops') }}
                    </Link>

                    <Link
                        v-if="user && !user.is_vendor"
                        :href="route('vendor.onboarding.index')"
                        class="hidden min-h-11 items-center rounded-xl px-3 text-sm font-semibold text-muted hover:bg-gray-50 hover:text-brand-700 xl:flex"
                    >{{ $t('Become a provider') }}</Link>

                    <LanguageSwitcher />
                    <NotificationDropdown v-if="isAuthenticated" />

                    <div v-if="isAuthenticated" data-customer-user-menu class="relative">
                        <button
                            type="button"
                            class="flex min-h-11 items-center gap-2 rounded-xl p-1.5 pl-2 transition hover:bg-gray-50"
                            :aria-label="$t('Open account menu')"
                            :aria-expanded="showUserMenu"
                            @click.stop="showUserMenu = !showUserMenu"
                        >
                            <span class="hidden max-w-28 truncate text-sm font-bold text-ink xl:block">{{ user?.name }}</span>
                            <span class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-brand-600 text-sm font-bold text-white">{{ userInitials }}</span>
                            <ChevronDown :size="15" class="hidden text-muted sm:block" aria-hidden="true" />
                        </button>

                        <div
                            v-if="showUserMenu"
                            class="absolute right-0 top-full z-50 mt-2 w-64 overflow-hidden rounded-2xl border border-line bg-white shadow-lift"
                        >
                            <div class="border-b border-line px-4 py-3">
                                <p class="truncate text-sm font-bold text-ink">{{ user?.name }}</p>
                                <p class="mt-0.5 truncate text-xs text-muted">{{ user?.email }}</p>
                            </div>
                            <div class="p-1.5">
                                <Link
                                    v-if="user?.is_vendor"
                                    :href="route('vendor.dashboard')"
                                    class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-ink hover:bg-gray-50"
                                    @click="showUserMenu = false"
                                ><LayoutDashboard :size="18" aria-hidden="true" />{{ $t('Provider dashboard') }}</Link>
                                <Link :href="route('bookings.index')" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-ink hover:bg-gray-50" @click="showUserMenu = false">
                                    <CalendarDays :size="18" aria-hidden="true" />{{ $t('My Bookings') }}
                                </Link>
                                <Link :href="route('reviews.user')" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-ink hover:bg-gray-50" @click="showUserMenu = false">
                                    <Star :size="18" aria-hidden="true" />{{ $t('My Reviews') }}
                                </Link>
                                <Link :href="route('profile.edit')" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-ink hover:bg-gray-50" @click="showUserMenu = false">
                                    <UserRound :size="18" aria-hidden="true" />{{ $t('Profile') }}
                                </Link>
                                <Link
                                    v-if="!user?.is_vendor"
                                    :href="route('vendor.onboarding.index')"
                                    class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-ink hover:bg-gray-50 xl:hidden"
                                    @click="showUserMenu = false"
                                ><Store :size="18" aria-hidden="true" />{{ $t('Become a provider') }}</Link>
                                <button type="button" class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold text-danger hover:bg-red-50" @click="logout">
                                    <LogOut :size="18" aria-hidden="true" />{{ $t('Log out') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <template v-else>
                        <Link :href="route('login')" class="inline-flex min-h-11 items-center rounded-xl px-2.5 text-sm font-bold text-ink hover:bg-gray-50 hover:text-brand-700 sm:px-3">
                            {{ $t('Log in') }}
                        </Link>
                        <Link :href="route('register')" class="ui-button ui-button-primary hidden sm:inline-flex">
                            {{ $t('Sign up') }}
                        </Link>
                    </template>
                </nav>
            </div>

            <form v-if="showSiteSearch" class="pb-3 md:hidden" role="search" @submit.prevent="handleSearch">
                <label for="mobile-site-search" class="sr-only">{{ $t('Search services or places') }}</label>
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-muted" :size="19" aria-hidden="true" />
                    <input
                        id="mobile-site-search"
                        v-model="searchQuery"
                        type="search"
                        :placeholder="$t('Service or place')"
                        class="ui-field rounded-full pl-11 pr-4"
                    />
                </div>
            </form>
        </div>
    </header>
</template>
