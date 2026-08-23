<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Check, Languages } from '@lucide/vue';

const page = usePage();
const open = ref(false);
const languages = [
    { code: 'cs', label: 'Čeština', short: 'CS' },
    { code: 'en', label: 'English', short: 'EN' },
];

const currentLanguage = computed(() => languages.find((language) => language.code === page.props.locale) ?? languages[0]);

function handleClickOutside(event) {
    if (!event.target.closest('[data-language-switcher]')) open.value = false;
}

function closeOnEscape(event) {
    if (event.key === 'Escape') open.value = false;
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
    <div data-language-switcher class="relative">
        <button
            type="button"
            class="ui-icon-button gap-1"
            :title="currentLanguage.label"
            :aria-label="$t('Change language')"
            :aria-expanded="open"
            @click.stop="open = !open"
        >
            <Languages :size="18" aria-hidden="true" />
            <span class="text-[10px] font-extrabold">{{ currentLanguage.short }}</span>
        </button>

        <Transition
            enter-active-class="transition duration-150"
            enter-from-class="translate-y-1 opacity-0"
            leave-active-class="transition duration-100"
            leave-to-class="translate-y-1 opacity-0"
        >
            <div v-if="open" class="absolute right-0 top-full z-50 mt-2 w-40 overflow-hidden rounded-2xl border border-line bg-white p-1.5 shadow-lift">
                <Link
                    v-for="language in languages"
                    :key="language.code"
                    :href="route('language.switch', language.code)"
                    class="flex min-h-11 items-center justify-between rounded-xl px-3 text-sm font-semibold transition hover:bg-gray-50"
                    :class="page.props.locale === language.code ? 'bg-brand-50 text-brand-800' : 'text-ink'"
                    :aria-current="page.props.locale === language.code ? 'true' : undefined"
                    @click="open = false"
                >
                    <span>{{ language.label }}</span>
                    <Check v-if="page.props.locale === language.code" :size="16" aria-hidden="true" />
                </Link>
            </div>
        </Transition>
    </div>
</template>
