<!-- layouts/SettingsLayout.vue -->
<template>
    <DashboardLayout>
        <div class="flex h-full">
            <!-- Settings Sidebar -->
            <aside class="flex w-64 flex-col border-r border-slate-200 bg-white">
                <div class="border-b border-slate-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-500">
                            <Settings class="h-4 w-4 text-white" />
                        </div>
                        <h1 class="text-lg font-semibold text-slate-900">Settings</h1>
                    </div>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                    <SettingsNavItem v-for="item in navigationItems" :key="item.name" :item="item" :active="isActive(item.route)" />
                </nav>
            </aside>

            <!-- Settings Content -->
            <main class="flex-1 overflow-y-auto bg-slate-50">
                <slot />
            </main>
        </div>
    </DashboardLayout>
</template>

<script setup lang="ts">
import SettingsNavItem from '@/components/settings/SettingsNavItem.vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { Bell, Info, Package, Palette, Settings, Shield, Store } from 'lucide-vue-next';

interface NavigationItem {
    name: string;
    route: string;
    icon: any;
    description: string;
}

const page = usePage();

const navigationItems: NavigationItem[] = [
    {
        name: 'Appearance',
        route: 'home',
        icon: Palette,
        description: 'Dark and Light mode, Font size',
    },
    {
        name: 'Your Restaurant',
        route: 'home',
        icon: Store,
        description: 'Dark and Light mode, Font size',
    },
    {
        name: 'Products Management',
        route: 'home',
        icon: Package,
        description: 'Manage your product, pricing, etc',
    },
    {
        name: 'Notifications',
        route: 'home',
        icon: Bell,
        description: 'Customize your notifications',
    },
    {
        name: 'Security',
        route: 'home',
        icon: Shield,
        description: 'Configure Password, PIN, etc',
    },
    {
        name: 'About Us',
        route: 'home',
        icon: Info,
        description: 'Find out more about Posly',
    },
];

const isActive = (route: string) => {
    return page.component === route;
};
</script>
