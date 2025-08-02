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
import { BarChart3, Home, Package, Settings, ShoppingCart, Store, Users } from 'lucide-vue-next';

interface NavigationItem {
    name: string;
    route: string;
    icon: any;
}

const page = usePage();

const navigationItems: NavigationItem[] = [
    { name: 'Dashboard', route: 'dashboard', icon: Home },
    { name: 'Orders', route: 'dashboard', icon: ShoppingCart },
    { name: 'Menu', route: 'admin.products.index', icon: Package },
    { name: 'Customers', route: 'dashboard', icon: Users },
    { name: 'Analytics', route: 'dashboard', icon: BarChart3 },
];

const settingsItem: NavigationItem = {
    name: 'Settings',
    route: 'dashboard',
    icon: Settings,
};

const isActive = (route: string) => {
    const currentRoute = page.component;
    if (route === 'settings.products') {
        return currentRoute.startsWith('settings/');
    }
    return currentRoute === route;
};

useFlashToast();
</script>
