<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-slate-50 p-4 dark:bg-slate-950">
        <div class="w-full max-w-[400px] space-y-6">
            <div class="flex justify-center">
                <Link :href="route('user.home')" class="group flex items-center gap-2 transition-opacity hover:opacity-80">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-600 text-white shadow-md shadow-red-600/20">
                        <AppLogoIcon class="h-6 w-6 fill-current" />
                    </div>
                    <span class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">Ciboox</span>
                </Link>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-6 text-center">
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">{{ title }}</h1>
                    <p v-if="description" class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ description }}</p>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
