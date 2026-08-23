<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message" role="alert">
        <p class="text-sm font-medium text-danger">
            {{ message }}
        </p>
    </div>
</template>
