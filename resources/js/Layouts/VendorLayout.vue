<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    ChevronDown,
    CircleHelp,
    ClipboardList,
    House,
    LayoutDashboard,
    LogOut,
    Star,
    Store,
    UserRound,
    Users,
} from '@lucide/vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';
import ToastRegion from '@/Components/ToastRegion.vue';

const props = defineProps({
    activePage: {
        type: String,
        default: 'dashboard',
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const showUserMenu = ref(false);

const navigation = [
    { name: 'dashboard', label: 'Dashboard', route: 'vendor.dashboard', icon: LayoutDashboard },
    { name: 'calendar', label: 'Calendar', route: 'vendor.calendar', icon: CalendarDays },
    { name: 'bookings', label: 'Bookings', route: 'vendor.bookings.index', icon: ClipboardList },
    { name: 'customers', label: 'Customers', route: 'vendor.customers.index', icon: Users },
    { name: 'shops', label: 'Shops', route: 'vendor.shops.index', icon: Store },
];

const activeLabel = computed(() => navigation.find((item) => item.name === props.activePage)?.label ?? 'Dashboard');
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

function navClass(name) {
    return props.activePage === name
        ? 'bg-brand-50 text-brand-800'
        : 'text-muted hover:bg-gray-50 hover:text-ink';
}

function logout() {
    showUserMenu.value = false;
    router.post(route('logout'));
}

function handleClickOutside(event) {
    if (!event.target.closest('[data-vendor-user-menu]')) {
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
    <div class="min-h-screen min-w-0 bg-canvas lg:flex lg:h-screen lg:overflow-hidden">
        <aside class="hidden w-64 flex-none border-r border-line bg-white lg:flex lg:flex-col">
            <div class="border-b border-line px-5 py-5">
                <Link :href="route('home')" class="inline-flex rounded-xl focus-visible:outline-none">
                    <ApplicationLogo />
                </Link>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5" :aria-label="$t('Provider navigation')">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="route(item.route)"
                    class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition"
                    :class="navClass(item.name)"
                    :aria-current="activePage === item.name ? 'page' : undefined"
                >
                    <component :is="item.icon" :size="20" :stroke-width="2" aria-hidden="true" />
                    {{ $t(item.label) }}
                </Link>
            </nav>

            <div class="border-t border-line p-3">
                <Link
                    :href="route('pages.contact')"
                    class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-semibold text-muted transition hover:bg-gray-50 hover:text-ink"
                >
                    <CircleHelp :size="20" aria-hidden="true" />
                    {{ $t('Help') }}
                </Link>
            </div>
        </aside>

        <div class="min-w-0 flex-1 lg:flex lg:flex-col lg:overflow-hidden">
            <header class="sticky top-0 z-40 border-b border-line bg-white/95 backdrop-blur lg:hidden">
                <div class="flex h-16 min-w-0 items-center justify-between gap-3 px-4">
                    <Link :href="route('home')" class="min-w-0 rounded-xl focus-visible:outline-none">
                        <ApplicationLogo />
                    </Link>
                    <div class="flex flex-none items-center gap-1">
                        <LanguageSwitcher />
                        <NotificationDropdown />
                        <div data-vendor-user-menu class="relative">
                            <button
                                type="button"
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-600 text-sm font-bold text-white focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2"
                                :aria-label="$t('Open account menu')"
                                :aria-expanded="showUserMenu"
                                @click.stop="showUserMenu = !showUserMenu"
                            >{{ userInitials }}</button>

                            <div
                                v-if="showUserMenu"
                                class="absolute right-0 top-full z-50 mt-2 w-64 overflow-hidden rounded-2xl border border-line bg-white shadow-lift"
                            >
                                <div class="border-b border-line px-4 py-3">
                                    <p class="truncate text-sm font-bold text-ink">{{ user?.name || $t('Provider') }}</p>
                                    <p class="mt-0.5 truncate text-xs text-muted">{{ user?.email }}</p>
                                </div>
                                <div class="p-1.5">
                                    <Link :href="route('profile.edit')" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-ink hover:bg-gray-50" @click="showUserMenu = false">
                                        <UserRound :size="18" aria-hidden="true" />{{ $t('Profile') }}
                                    </Link>
                                    <Link :href="route('home')" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-ink hover:bg-gray-50" @click="showUserMenu = false">
                                        <House :size="18" aria-hidden="true" />{{ $t('Client Area') }}
                                    </Link>
                                    <button type="button" class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold text-danger hover:bg-red-50" @click="logout">
                                        <LogOut :size="18" aria-hidden="true" />{{ $t('Log out') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="border-t border-line/70 px-4 py-3">
                    <h1 class="truncate text-lg font-extrabold tracking-[-0.02em] text-ink">{{ $t(activeLabel) }}</h1>
                </div>
            </header>

            <header class="hidden h-[72px] flex-none items-center justify-between border-b border-line bg-white px-8 lg:flex">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-brand-700">{{ $t('Provider area') }}</p>
                    <h1 class="mt-1 text-xl font-extrabold tracking-[-0.025em] text-ink">{{ $t(activeLabel) }}</h1>
                </div>

                <div class="flex items-center gap-2">
                    <LanguageSwitcher />
                    <NotificationDropdown />
                    <div class="mx-1 h-8 w-px bg-line" aria-hidden="true"></div>
                    <div data-vendor-user-menu class="relative">
                        <button
                            type="button"
                            class="flex min-h-11 items-center gap-3 rounded-xl px-2 py-1.5 text-left transition hover:bg-gray-50"
                            :aria-expanded="showUserMenu"
                            @click.stop="showUserMenu = !showUserMenu"
                        >
                            <span class="min-w-0">
                                <span class="block max-w-40 truncate text-sm font-bold text-ink">{{ user?.name || $t('Provider') }}</span>
                                <span class="mt-0.5 block text-xs text-muted">{{ $t('Provider') }}</span>
                            </span>
                            <span class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-brand-600 text-sm font-bold text-white">{{ userInitials }}</span>
                            <ChevronDown :size="16" class="text-muted" aria-hidden="true" />
                        </button>

                        <div
                            v-if="showUserMenu"
                            class="absolute right-0 top-full z-50 mt-2 w-64 overflow-hidden rounded-2xl border border-line bg-white shadow-lift"
                        >
                            <div class="border-b border-line px-4 py-3">
                                <p class="truncate text-sm font-bold text-ink">{{ user?.name || $t('Provider') }}</p>
                                <p class="mt-0.5 truncate text-xs text-muted">{{ user?.email }}</p>
                            </div>
                            <div class="p-1.5">
                                <Link :href="route('profile.edit')" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-ink hover:bg-gray-50" @click="showUserMenu = false">
                                    <UserRound :size="18" aria-hidden="true" />{{ $t('Profile') }}
                                </Link>
                                <Link :href="route('home')" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-ink hover:bg-gray-50" @click="showUserMenu = false">
                                    <House :size="18" aria-hidden="true" />{{ $t('Client Area') }}
                                </Link>
                                <Link :href="route('bookings.index')" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-ink hover:bg-gray-50" @click="showUserMenu = false">
                                    <CalendarDays :size="18" aria-hidden="true" />{{ $t('My Bookings') }}
                                </Link>
                                <Link :href="route('reviews.user')" class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm text-ink hover:bg-gray-50" @click="showUserMenu = false">
                                    <Star :size="18" aria-hidden="true" />{{ $t('My Reviews') }}
                                </Link>
                                <button type="button" class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 text-left text-sm font-semibold text-danger hover:bg-red-50" @click="logout">
                                    <LogOut :size="18" aria-hidden="true" />{{ $t('Log out') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main id="main-content" class="min-w-0 px-4 py-5 pb-28 sm:px-6 lg:flex-1 lg:overflow-y-auto lg:px-8 lg:py-7 lg:pb-8">
                <slot />
            </main>
        </div>

        <nav
            class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-white/95 px-2 pb-[env(safe-area-inset-bottom)] shadow-[0_-10px_30px_-24px_rgba(23,33,31,0.45)] backdrop-blur lg:hidden"
            :aria-label="$t('Provider navigation')"
        >
            <div class="mx-auto grid max-w-lg grid-cols-5">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="route(item.route)"
                    class="flex min-h-16 min-w-0 flex-col items-center justify-center gap-1 rounded-xl px-1 py-2 text-[10px] font-bold transition sm:text-xs"
                    :class="activePage === item.name ? 'text-brand-700' : 'text-muted hover:text-ink'"
                    :aria-current="activePage === item.name ? 'page' : undefined"
                >
                    <span class="flex h-7 w-10 items-center justify-center rounded-full" :class="activePage === item.name ? 'bg-brand-50' : ''">
                        <component :is="item.icon" :size="20" :stroke-width="activePage === item.name ? 2.4 : 2" aria-hidden="true" />
                    </span>
                    <span class="w-full truncate text-center">{{ $t(item.label) }}</span>
                </Link>
            </div>
        </nav>

        <ToastRegion />
    </div>
</template>
