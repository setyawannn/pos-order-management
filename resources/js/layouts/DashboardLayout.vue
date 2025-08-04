<!-- resources/js/layouts/DashboardLayout.vue -->
<template>
    <div class="flex h-screen bg-slate-50">
        <aside class="flex h-screen w-32 flex-col items-center border-r border-slate-200 bg-white">
            <div class="border-b border-slate-200 p-3">
                <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-red-500">
                    <Store class="h-6 w-6 text-white" />
                </div>
            </div>

            <nav class="flex-1 space-y-2 p-2">
                <DashboardNavItem v-for="item in navigationItems" :key="item.name" :item="item" :active="isActive(item)" />
            </nav>

            <div class="border-t border-slate-200 p-2">
                <button
                    @click="showLogoutModal = true"
                    class="relative flex h-16 w-16 items-center justify-center rounded-lg text-slate-600 transition-all duration-200 hover:bg-slate-100 hover:text-slate-900"
                >
                    <LogOut class="h-5 w-5" />
                    <div
                        class="pointer-events-none absolute top-1/2 left-full z-50 ml-3 -translate-y-1/2 rounded-lg bg-slate-900 px-3 py-2 text-sm whitespace-nowrap text-white opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100"
                    >
                        Logout
                        <div class="absolute top-1/2 right-full -translate-y-1/2 border-4 border-transparent border-r-slate-900"></div>
                    </div>
                </button>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto">
            <div class="h-fit">
                <slot />
            </div>
        </main>

        <ConfirmationModal
            :show="showLogoutModal"
            title="Confirm Logout"
            message="Are you sure you want to log out?"
            confirmText="Logout"
            confirmVariant="destructive"
            icon="LogOut"
            cancelText="Cancel"
            iconClass="text-red-600"
            confirmIcon="LogOut"
            @confirm="confirmLogout"
            @cancel="showLogoutModal = false"
        />
    </div>
</template>

<script setup lang="ts">
import DashboardNavItem from '@/components/dashboard/DashboardNavItem.vue';
import ConfirmationModal from '@/components/reusable/ConfirmationModal.vue';
import { router, usePage } from '@inertiajs/vue3';
import { BarChart3, Home, LogOut, ShoppingCart, Store, Users, UtensilsCrossed } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useToast } from 'vue-toastification';

interface NavigationItem {
    name: string;
    urlSegment: string;
    icon: any;
}

const page = usePage();

const currentUrl = computed<string>(() => page.url);

const navigationItems: NavigationItem[] = [
    { name: 'Dashboard', urlSegment: '/dashboard', icon: Home },
    { name: 'Order Management', urlSegment: '/admin/orders', icon: ShoppingCart },
    { name: 'Kitchen Orders', urlSegment: '/admin/kitchen', icon: UtensilsCrossed },
    { name: 'Customers', urlSegment: '/dashboard/customers', icon: Users },
    { name: 'Analytics', urlSegment: '/dashboard/analytics', icon: BarChart3 },
];

const isActive = (navItem: NavigationItem): boolean => {
    if (navItem.urlSegment === '/dashboard') {
        return currentUrl.value === '/dashboard' || currentUrl.value === '/';
    }
    return currentUrl.value.startsWith(navItem.urlSegment);
};

const showLogoutModal = ref(false);
const toast = useToast();

const confirmLogout = () => {
    showLogoutModal.value = false;
    router.post(
        route('logout'),
        {},
        {
            onSuccess: () => {
                toast.success('Logged out successfully.');
            },
            onError: (errors) => {
                toast.error('Failed to logout.' + (errors.message || ''));
            },
        },
    );
};
</script>
