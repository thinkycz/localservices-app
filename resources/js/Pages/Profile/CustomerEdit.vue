<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, nextTick } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const page = usePage();
const user = page.props.auth.user;

const userInitials = user.name
    ? user.name.split(' ').map((n) => n[0]).join('').toUpperCase().slice(0, 2)
    : 'U';

// Active tab
const activeTab = ref('profile');

// ── Profile Form ──────────────────────────────────────────────────────────
const profileForm = useForm({
    name: user.name,
    email: user.email,
});

// ── Password Form ─────────────────────────────────────────────────────────
const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};

// ── Delete Account Form ───────────────────────────────────────────────────
const confirmingDeletion = ref(false);
const deletePasswordInput = ref(null);

const deleteForm = useForm({
    password: '',
});

const confirmDeletion = () => {
    confirmingDeletion.value = true;
    nextTick(() => deletePasswordInput.value?.focus());
};

const deleteAccount = () => {
    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => { confirmingDeletion.value = false; },
        onError: () => deletePasswordInput.value?.focus(),
        onFinish: () => deleteForm.reset(),
    });
};
</script>

<template>
    <Head title="My Profile" />

    <AppLayout>
        <!-- Page Header -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your account settings and preferences</p>
            </div>

            <!-- Profile Header Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex items-center gap-5">
                    <!-- Avatar -->
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
                        {{ userInitials }}
                    </div>
                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold text-gray-900 truncate">{{ user.name }}</h2>
                        <p class="text-sm text-gray-400 mt-0.5 truncate">{{ user.email }}</p>
                    </div>
                    <!-- Role Badge -->
                    <span :class="[
                        'px-3 py-1 text-xs font-bold rounded-full flex-shrink-0',
                        user.is_service_provider ? 'bg-purple-50 text-purple-600' : 'bg-green-50 text-green-600'
                    ]">
                        {{ user.is_service_provider ? 'Service Provider' : 'Customer' }}
                    </span>
                </div>
            </div>

            <!-- Main Content: Tabs + Panel -->
            <div class="flex gap-6">

                <!-- Left Tab Navigation -->
                <div class="w-52 flex-shrink-0">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-2 space-y-0.5">
                        <button
                            @click="activeTab = 'profile'"
                            :class="[
                                'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors text-left',
                                activeTab === 'profile'
                                    ? 'bg-blue-600 text-white'
                                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700',
                            ]"
                        >
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile Info
                        </button>

                        <button
                            @click="activeTab = 'security'"
                            :class="[
                                'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors text-left',
                                activeTab === 'security'
                                    ? 'bg-blue-600 text-white'
                                    : 'text-gray-500 hover:bg-gray-50 hover:text-gray-700',
                            ]"
                        >
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Security
                        </button>

                        <button
                            @click="activeTab = 'danger'"
                            :class="[
                                'w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors text-left',
                                activeTab === 'danger'
                                    ? 'bg-red-500 text-white'
                                    : 'text-gray-500 hover:bg-red-50 hover:text-red-600',
                            ]"
                        >
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Danger Zone
                        </button>
                    </div>
                </div>

                <!-- Right Content Panel -->
                <div class="flex-1 min-w-0">

                    <!-- ── Profile Info Tab ──────────────────────────────── -->
                    <div v-if="activeTab === 'profile'" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="mb-6">
                            <h3 class="text-base font-bold text-gray-900">Profile Information</h3>
                            <p class="text-sm text-gray-400 mt-1">Update your name and email address.</p>
                        </div>

                        <form @submit.prevent="profileForm.patch(route('profile.update'))" class="space-y-5">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                                    Full Name
                                </label>
                                <input
                                    id="name"
                                    v-model="profileForm.name"
                                    type="text"
                                    required
                                    autofocus
                                    autocomplete="name"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    placeholder="Your full name"
                                />
                                <p v-if="profileForm.errors.name" class="mt-1.5 text-xs text-red-500">
                                    {{ profileForm.errors.name }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                                    Email Address
                                </label>
                                <input
                                    id="email"
                                    v-model="profileForm.email"
                                    type="email"
                                    required
                                    autocomplete="username"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    placeholder="your@email.com"
                                />
                                <p v-if="profileForm.errors.email" class="mt-1.5 text-xs text-red-500">
                                    {{ profileForm.errors.email }}
                                </p>
                            </div>

                            <!-- Email verification notice -->
                            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                                <p class="text-sm text-amber-700">
                                    Your email address is unverified.
                                    <Link
                                        :href="route('verification.send')"
                                        method="post"
                                        as="button"
                                        class="font-semibold underline hover:text-amber-900 transition"
                                    >
                                        Resend verification email.
                                    </Link>
                                </p>
                                <p v-if="status === 'verification-link-sent'" class="mt-1 text-sm font-medium text-green-600">
                                    A new verification link has been sent to your email.
                                </p>
                            </div>

                            <!-- Save Button -->
                            <div class="flex items-center gap-4 pt-1">
                                <button
                                    type="submit"
                                    :disabled="profileForm.processing"
                                    class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors shadow-sm"
                                >
                                    Save Changes
                                </button>
                                <Transition
                                    enter-active-class="transition ease-in-out duration-200"
                                    enter-from-class="opacity-0 translate-y-1"
                                    leave-active-class="transition ease-in-out duration-200"
                                    leave-to-class="opacity-0 translate-y-1"
                                >
                                    <span v-if="profileForm.recentlySuccessful" class="text-sm font-medium text-green-600 flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Saved successfully
                                    </span>
                                </Transition>
                            </div>
                        </form>
                    </div>

                    <!-- ── Security Tab ──────────────────────────────────── -->
                    <div v-if="activeTab === 'security'" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="mb-6">
                            <h3 class="text-base font-bold text-gray-900">Update Password</h3>
                            <p class="text-sm text-gray-400 mt-1">Use a long, random password to keep your account secure.</p>
                        </div>

                        <form @submit.prevent="updatePassword" class="space-y-5">
                            <!-- Current Password -->
                            <div>
                                <label for="current_password" class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                                    Current Password
                                </label>
                                <input
                                    id="current_password"
                                    ref="currentPasswordInput"
                                    v-model="passwordForm.current_password"
                                    type="password"
                                    autocomplete="current-password"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    placeholder="Enter current password"
                                />
                                <p v-if="passwordForm.errors.current_password" class="mt-1.5 text-xs text-red-500">
                                    {{ passwordForm.errors.current_password }}
                                </p>
                            </div>

                            <!-- New Password -->
                            <div>
                                <label for="password" class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                                    New Password
                                </label>
                                <input
                                    id="password"
                                    ref="passwordInput"
                                    v-model="passwordForm.password"
                                    type="password"
                                    autocomplete="new-password"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    placeholder="Enter new password"
                                />
                                <p v-if="passwordForm.errors.password" class="mt-1.5 text-xs text-red-500">
                                    {{ passwordForm.errors.password }}
                                </p>
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                                    Confirm New Password
                                </label>
                                <input
                                    id="password_confirmation"
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    placeholder="Confirm new password"
                                />
                                <p v-if="passwordForm.errors.password_confirmation" class="mt-1.5 text-xs text-red-500">
                                    {{ passwordForm.errors.password_confirmation }}
                                </p>
                            </div>

                            <!-- Save Button -->
                            <div class="flex items-center gap-4 pt-1">
                                <button
                                    type="submit"
                                    :disabled="passwordForm.processing"
                                    class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors shadow-sm"
                                >
                                    Update Password
                                </button>
                                <Transition
                                    enter-active-class="transition ease-in-out duration-200"
                                    enter-from-class="opacity-0 translate-y-1"
                                    leave-active-class="transition ease-in-out duration-200"
                                    leave-to-class="opacity-0 translate-y-1"
                                >
                                    <span v-if="passwordForm.recentlySuccessful" class="text-sm font-medium text-green-600 flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Password updated
                                    </span>
                                </Transition>
                            </div>
                        </form>
                    </div>

                    <!-- ── Danger Zone Tab ───────────────────────────────── -->
                    <div v-if="activeTab === 'danger'" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="mb-6">
                            <h3 class="text-base font-bold text-gray-900">Delete Account</h3>
                            <p class="text-sm text-gray-400 mt-1">
                                Once your account is deleted, all data will be permanently removed. This action cannot be undone.
                            </p>
                        </div>

                        <!-- Warning Banner -->
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-sm text-red-700">
                                Please download any data you wish to retain before deleting your account.
                            </p>
                        </div>

                        <!-- Confirm deletion form -->
                        <div v-if="!confirmingDeletion">
                            <button
                                @click="confirmDeletion"
                                class="bg-red-500 hover:bg-red-600 text-white font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors shadow-sm"
                            >
                                Delete My Account
                            </button>
                        </div>

                        <div v-else class="space-y-5">
                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                                <p class="text-sm font-semibold text-gray-800 mb-1">Confirm account deletion</p>
                                <p class="text-sm text-gray-500">Enter your password to permanently delete your account.</p>
                            </div>

                            <div>
                                <label for="delete_password" class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">
                                    Your Password
                                </label>
                                <input
                                    id="delete_password"
                                    ref="deletePasswordInput"
                                    v-model="deleteForm.password"
                                    type="password"
                                    placeholder="Enter your password"
                                    @keyup.enter="deleteAccount"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent transition"
                                />
                                <p v-if="deleteForm.errors.password" class="mt-1.5 text-xs text-red-500">
                                    {{ deleteForm.errors.password }}
                                </p>
                            </div>

                            <div class="flex items-center gap-3">
                                <button
                                    @click="deleteAccount"
                                    :disabled="deleteForm.processing"
                                    class="bg-red-500 hover:bg-red-600 disabled:opacity-50 text-white font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors shadow-sm"
                                >
                                    Confirm Delete
                                </button>
                                <button
                                    @click="confirmingDeletion = false; deleteForm.reset()"
                                    class="border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

