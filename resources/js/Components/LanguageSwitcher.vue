<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const open = ref(false);

const languages = [
    { code: 'en', flag: '🇬🇧' },
    { code: 'cs', flag: '🇨🇿' },
];

const currentLang = () => languages.find(l => l.code === page.props.locale) || languages[0];

function handleClickOutside(e) {
    if (!e.target.closest('#lang-switcher')) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div id="lang-switcher" class="relative">
        <button
            @click.stop="open = !open"
            class="flex items-center justify-center w-9 h-9 rounded-xl hover:bg-gray-100 transition-colors text-xl cursor-pointer"
            :title="currentLang().code.toUpperCase()"
        >
            {{ currentLang().flag }}
        </button>

        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="open"
                class="absolute right-0 top-full mt-1.5 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50 min-w-[48px]"
            >
                <Link
                    v-for="lang in languages"
                    :key="lang.code"
                    :href="route('language.switch', lang.code)"
                    class="flex items-center justify-center px-3 py-2 text-xl hover:bg-gray-50 transition-colors rounded-lg mx-1"
                    :class="page.props.locale === lang.code ? 'bg-blue-50' : ''"
                    @click="open = false"
                >
                    {{ lang.flag }}
                </Link>
            </div>
        </transition>
    </div>
</template>
