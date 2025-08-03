<!-- resources/js/pages/errors/TooManyRequests.vue -->
<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'; // Import router for back button
import { computed } from 'vue';

const props = defineProps<{
    status: number;
    message?: string;
    title?: string;
}>();

const defaultMessage = computed(() => {
    switch (props.status) {
        case 403:
            return 'Sorry, you are forbidden from accessing this page.';
        case 404:
            return 'Sorry, the page you are looking for could not be found.';
        case 419:
            return 'Sorry, your session has expired. Please refresh and try again.';
        case 429:
            return props.message || 'Sorry, you are making too many requests to our servers.';
        case 500:
            return 'Whoops, something went wrong on our servers.';
        default:
            return 'An unexpected error occurred.';
    }
});

const defaultTitle = computed(() => {
    switch (props.status) {
        case 403:
            return 'Forbidden';
        case 404:
            return 'Page Not Found';
        case 419:
            return 'Session Expired';
        case 429:
            return props.title || 'Too Many Requests';
        case 500:
            return 'Server Error';
        default:
            return 'Error';
    }
});

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit('/');
    }
};
</script>

<template>
    <Head :title="defaultTitle" />

    <div class="flex min-h-screen items-center justify-center bg-gray-100 text-gray-800">
        <div class="w-full max-w-md rounded-lg bg-white p-8 text-center shadow-xl">
            <h1 class="mb-4 text-6xl font-bold text-red-500">{{ status }}</h1>
            <h2 class="mb-6 text-2xl font-semibold">{{ defaultTitle }}</h2>
            <p class="mb-8 text-gray-600">{{ defaultMessage }}</p>

            <button
                @click="goBack"
                class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
            >
                Go Back
            </button>
        </div>
    </div>
</template>
