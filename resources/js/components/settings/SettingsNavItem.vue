<!-- components/settings/SettingsNavItem.vue -->
<template>
    <Link
        :href="route(item.route)"
        :class="[
            'group flex items-center gap-3 rounded-xl p-3 text-left transition-all duration-200',
            active ? 'border border-red-200 bg-red-50 text-red-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
        ]"
    >
        <component :is="item.icon" :class="['h-5 w-5 flex-shrink-0', active ? 'text-red-600' : 'text-slate-500']" />
        <div class="min-w-0 flex-1">
            <div class="text-sm font-medium">{{ item.name }}</div>
            <div class="mt-0.5 text-xs text-slate-500">{{ item.description }}</div>
        </div>
    </Link>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface NavigationItem {
    name: string;
    route: string;
    icon: any;
    description: string;
}

defineProps<{
    item: NavigationItem;
    active: boolean;
}>();
</script>
