<script setup>
import { computed } from 'vue';

const props = defineProps({
    rating: {
        type: Number,
        default: 0,
    },
    max: {
        type: Number,
        default: 5,
    },
    size: {
        type: String,
        default: 'sm', // sm | md | lg
    },
});

const sizeClass = computed(() => {
    return {
        sm: 'w-4 h-4',
        md: 'w-5 h-5',
        lg: 'w-6 h-6',
    }[props.size] ?? 'w-4 h-4';
});

function starType(index) {
    const val = props.rating - (index - 1);
    if (val >= 1) return 'full';
    if (val >= 0.5) return 'half';
    return 'empty';
}
</script>

<template>
    <div class="flex items-center gap-0.5">
        <template v-for="i in max" :key="i">
            <!-- Full star -->
            <svg v-if="starType(i) === 'full'" :class="sizeClass" viewBox="0 0 20 20" fill="#F59E0B">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <!-- Half star -->
            <svg v-else-if="starType(i) === 'half'" :class="sizeClass" viewBox="0 0 20 20">
                <defs>
                    <linearGradient :id="`half-${i}`">
                        <stop offset="50%" stop-color="#F59E0B"/>
                        <stop offset="50%" stop-color="#D1D5DB"/>
                    </linearGradient>
                </defs>
                <path :fill="`url(#half-${i})`" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <!-- Empty star -->
            <svg v-else :class="sizeClass" viewBox="0 0 20 20" fill="#D1D5DB">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        </template>
    </div>
</template>
