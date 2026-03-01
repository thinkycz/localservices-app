<script setup>
import { Link } from '@inertiajs/vue3';

// Available languages
const languages = [
    { code: 'en', name: 'English', flag: '🇬🇧' },
    { code: 'cs', name: 'Čeština', flag: '🇨🇿' }
];
</script>

<template>
    <div class="relative ms-3">
        <div class="flex items-center space-x-2">
            <Link
                v-for="lang in languages"
                :key="lang.code"
                :href="route('language.switch', lang.code)"
                class="flex items-center px-2 py-1 text-sm font-medium transition-colors rounded-md"
                :class="$page.props.locale === lang.code ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300'"
            >
                <span class="mr-1">{{ lang.flag }}</span>
                <span class="hidden sm:inline">{{ lang.name }}</span>
            </Link>
        </div>
    </div>
</template>
