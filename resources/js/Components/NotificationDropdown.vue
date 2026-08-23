<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Bell, CheckCheck, Trash2, X } from '@lucide/vue';

const page = usePage();
const isOpen = ref(false);
const isLoading = ref(false);
const failed = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const dropdownRef = ref(null);

const badgeLabel = computed(() => unreadCount.value > 99 ? '99+' : String(unreadCount.value));

async function request(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'include',
        ...options,
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            ...options.headers,
        },
    });

    if (!response.ok) throw new Error(`Notification request failed (${response.status})`);
    return response.status === 204 ? null : response.json();
}

async function fetchNotifications() {
    isLoading.value = true;
    failed.value = false;

    try {
        const data = await request(route('notifications.recent'));
        notifications.value = Array.isArray(data?.notifications) ? data.notifications : [];
        unreadCount.value = Number(data?.unread_count ?? 0);
    } catch {
        failed.value = true;
    } finally {
        isLoading.value = false;
    }
}

function toggleDropdown() {
    isOpen.value = !isOpen.value;
    if (isOpen.value) fetchNotifications();
}

async function markAllAsRead() {
    try {
        await request(route('notifications.markAllRead'), { method: 'POST' });
        notifications.value = notifications.value.map((notification) => ({
            ...notification,
            read_at: notification.read_at ?? new Date().toISOString(),
        }));
        unreadCount.value = 0;
    } catch {
        failed.value = true;
    }
}

async function handleNotificationClick(notification) {
    if (!notification.read_at) {
        try {
            await request(route('notifications.read', notification.id), { method: 'POST' });
            notification.read_at = new Date().toISOString();
            unreadCount.value = Math.max(0, unreadCount.value - 1);
        } catch {
            failed.value = true;
            return;
        }
    }

    isOpen.value = false;
    if (notification.action_url) router.visit(notification.action_url);
}

async function deleteNotification(notification) {
    try {
        await request(route('notifications.destroy', notification.id), { method: 'DELETE' });
        notifications.value = notifications.value.filter((item) => item.id !== notification.id);
        if (!notification.read_at) unreadCount.value = Math.max(0, unreadCount.value - 1);
    } catch {
        failed.value = true;
    }
}

function typeColor(type) {
    return {
        booking: 'bg-brand-600',
        review: 'bg-accent',
        reminder: 'bg-amber-600',
        system: 'bg-muted',
    }[type] ?? 'bg-muted';
}

function formatTime(value) {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';

    const seconds = Math.round((date.getTime() - Date.now()) / 1000);
    const locale = page.props.locale === 'cs' ? 'cs-CZ' : 'en-US';
    const formatter = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });

    if (Math.abs(seconds) < 60) return formatter.format(seconds, 'second');
    if (Math.abs(seconds) < 3600) return formatter.format(Math.round(seconds / 60), 'minute');
    if (Math.abs(seconds) < 86400) return formatter.format(Math.round(seconds / 3600), 'hour');
    if (Math.abs(seconds) < 604800) return formatter.format(Math.round(seconds / 86400), 'day');
    return new Intl.DateTimeFormat(locale, { dateStyle: 'medium' }).format(date);
}

function handleClickOutside(event) {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) isOpen.value = false;
}

function closeOnEscape(event) {
    if (event.key === 'Escape') isOpen.value = false;
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', closeOnEscape);
    fetchNotifications();
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', closeOnEscape);
});
</script>

<template>
    <div ref="dropdownRef" class="relative">
        <button
            type="button"
            class="ui-icon-button relative"
            :aria-label="$t('Notifications')"
            :aria-expanded="isOpen"
            @click.stop="toggleDropdown"
        >
            <Bell :size="21" aria-hidden="true" />
            <span v-if="unreadCount > 0" class="absolute right-1 top-1 flex min-h-4 min-w-4 items-center justify-center rounded-full bg-danger px-1 text-[9px] font-extrabold leading-none text-white">
                {{ badgeLabel }}
            </span>
        </button>

        <Transition
            enter-active-class="transition duration-150"
            enter-from-class="translate-y-1 opacity-0"
            leave-active-class="transition duration-100"
            leave-to-class="translate-y-1 opacity-0"
        >
            <section v-if="isOpen" class="fixed inset-x-4 top-16 z-50 overflow-hidden rounded-2xl border border-line bg-white shadow-lift sm:absolute sm:left-auto sm:right-0 sm:top-full sm:mt-2 sm:w-96" :aria-label="$t('Notifications')">
                <header class="flex min-h-14 items-center justify-between gap-3 border-b border-line px-4">
                    <h2 class="font-extrabold text-ink">{{ $t('Notifications') }}</h2>
                    <div class="flex items-center gap-1">
                        <button v-if="unreadCount > 0" type="button" class="inline-flex min-h-10 items-center gap-1.5 rounded-xl px-2 text-xs font-bold text-brand-700 hover:bg-brand-50" @click="markAllAsRead">
                            <CheckCheck :size="16" aria-hidden="true" />{{ $t('Mark all read') }}
                        </button>
                        <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl text-muted hover:bg-gray-50 hover:text-ink sm:hidden" :aria-label="$t('Close')" @click="isOpen = false">
                            <X :size="18" aria-hidden="true" />
                        </button>
                    </div>
                </header>

                <div class="max-h-[min(28rem,70vh)] overflow-y-auto">
                    <p v-if="isLoading" class="px-4 py-8 text-center text-sm text-muted">{{ $t('Loading notifications…') }}</p>
                    <div v-else-if="failed" class="px-5 py-8 text-center">
                        <p class="text-sm font-semibold text-danger">{{ $t('Notifications could not be loaded.') }}</p>
                        <button type="button" class="mt-3 text-sm font-bold text-brand-700 hover:text-brand-800" @click="fetchNotifications">{{ $t('Try again') }}</button>
                    </div>
                    <p v-else-if="notifications.length === 0" class="px-4 py-10 text-center text-sm text-muted">{{ $t('No notifications yet') }}</p>

                    <article
                        v-for="notification in notifications"
                        v-else
                        :key="notification.id"
                        class="group flex cursor-pointer gap-3 border-b border-line px-4 py-4 transition last:border-b-0 hover:bg-gray-50"
                        :class="!notification.read_at ? 'bg-brand-50/60' : ''"
                        tabindex="0"
                        @click="handleNotificationClick(notification)"
                        @keydown.enter="handleNotificationClick(notification)"
                    >
                        <span class="mt-1.5 h-2.5 w-2.5 flex-none rounded-full" :class="typeColor(notification.type)" aria-hidden="true"></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-ink">{{ notification.title }}</p>
                            <p class="mt-1 line-clamp-2 text-sm leading-5 text-muted">{{ notification.message }}</p>
                            <time class="mt-1.5 block text-xs text-muted" :datetime="notification.created_at">{{ formatTime(notification.created_at) }}</time>
                        </div>
                        <button type="button" class="flex h-10 w-10 flex-none items-center justify-center rounded-xl text-muted opacity-80 hover:bg-red-50 hover:text-danger sm:opacity-0 sm:group-hover:opacity-100 sm:focus:opacity-100" :aria-label="$t('Delete notification')" @click.stop="deleteNotification(notification)">
                            <Trash2 :size="17" aria-hidden="true" />
                        </button>
                    </article>
                </div>
            </section>
        </Transition>
    </div>
</template>
