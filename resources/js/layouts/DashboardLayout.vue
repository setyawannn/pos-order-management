<!-- layouts/DashboardLayout.vue -->
<template>
    <div class="flex h-screen bg-slate-50">
        <aside class="flex w-32 flex-col items-center border-r border-slate-200 bg-white">
            <div class="border-b border-slate-200 p-3">
                <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-red-500">
                    <Store class="h-6 w-6 text-white" />
                </div>
            </div>

            <nav class="flex-1 space-y-2 p-2">
                <DashboardNavItem v-for="item in navigationItems" :key="item.name" :item="item" :active="isActive(item.route)" />
            </nav>

            <div class="border-t border-slate-200 p-2">
                <DashboardNavItem :item="settingsItem" :active="isActive(settingsItem.route)" />
            </div>
        </aside>

        <main class="flex-1 overflow-hidden">
            <slot />
        </main>
    </div>
</template>

<script setup lang="ts">
import DashboardNavItem from '@/components/dashboard/DashboardNavItem.vue';
import { useFlashToast } from '@/composables/useFlashToast';
import { usePage } from '@inertiajs/vue3';
import { BarChart3, Home, Package, Settings, Store, Users, UtensilsCrossed } from 'lucide-vue-next';
import { computed } from 'vue';

interface NavigationItem {
    name: string;
    route: string;
    icon: any;
}

const page = usePage();

const currentRouteName = computed(() => {
    return page.props.ziggy && page.props.ziggy.route ? page.props.ziggy.route.name : null;
});

const navigationItems: NavigationItem[] = [
    { name: 'Dashboard', route: 'dashboard', icon: Home },
    { name: 'Orders', route: 'admin.kitchen.index', icon: UtensilsCrossed },
    { name: 'Menu', route: 'admin.products.index', icon: Package },
    { name: 'Customers', route: 'dashboard', icon: Users },
    { name: 'Analytics', route: 'dashboard', icon: BarChart3 },
];

const settingsItem: NavigationItem = {
    name: 'Settings',
    route: 'dashboard', // Or a specific settings route like 'admin.settings.index'
    icon: Settings,
};

const isActive = (route: string) => {
    // Use the safely accessed currentRouteName
    const current = currentRouteName.value;
    if (!current) {
        return false;
    }

    if (route === 'admin.products.index' && current.startsWith('admin.products')) {
        return true;
    }
    if (route === 'admin.kitchen.index' && current.startsWith('admin.kitchen')) {
        return true;
    }
    return current === route; // Direct match for dashboard etc.
};

useFlashToast();
</script>
