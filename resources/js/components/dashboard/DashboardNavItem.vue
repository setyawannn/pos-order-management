<!-- components/dashboard/DashboardNavItem.vue -->
<template>
    <div class="group relative">
        <Link
            :href="route(item.route)"
            :class="[
                'relative flex h-16 w-16 items-center justify-center rounded-lg transition-all duration-200',
                active ? 'bg-red-500 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
            ]"
        >
            <component :is="item.icon" class="h-5 w-5" />
        </Link>

        <div
            class="pointer-events-none absolute top-1/2 left-full z-50 ml-3 -translate-y-1/2 rounded-lg bg-slate-900 px-3 py-2 text-sm whitespace-nowrap text-white opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100"
        >
            {{ item.name }}
            <div class="absolute top-1/2 right-full -translate-y-1/2 border-4 border-transparent border-r-slate-900"></div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface NavigationItem {
    name: string;
    route: string;
    icon: any;
}

defineProps<{
    item: NavigationItem;
    active: boolean;
}>();
</script>
