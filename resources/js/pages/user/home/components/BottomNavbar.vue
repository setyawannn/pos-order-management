<!-- resources/js/pages/user/home/components/BottomNavbar.vue -->
<template>
    <nav class="fixed right-0 bottom-0 left-0 z-30 border-t border-gray-200 bg-white">
        <div class="flex items-center justify-around py-2">
            <!-- Home Button -->
            <button
                @click="handleNavClick('home')"
                :class="[
                    'flex flex-col items-center rounded-lg px-6 py-2 transition-colors',
                    activeTab === 'home' ? 'bg-red-50 text-red-500' : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600',
                ]"
            >
                <Home class="mb-1 h-6 w-6" />
                <span class="text-xs font-medium">Home</span>
            </button>

            <!-- Orders Button -->
            <button
                @click="handleNavClick('orders')"
                :class="[
                    'relative flex flex-col items-center rounded-lg px-6 py-2 transition-colors',
                    activeTab === 'orders' ? 'bg-red-50 text-red-500' : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600',
                ]"
            >
                <ShoppingBag class="mb-1 h-6 w-6" />
                <span class="text-xs font-medium">Orders</span>

                <!-- Badge for orders -->
                <span v-if="orderHistoryStore.hasOrders" class="absolute -top-1 -right-1 h-3 w-3 rounded-full bg-red-500" />
            </button>
        </div>

        <!-- Order History Drawer -->
        <OrderHistoryDrawer v-model:open="showOrderHistory" />
    </nav>
</template>

<script setup lang="ts">
import { useOrderHistoryStore } from '@/stores/orderHistoryStore';
import { router, usePage } from '@inertiajs/vue3';
import { Home, ShoppingBag } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import OrderHistoryDrawer from './OrderHistoryDrawer.vue';

const orderHistoryStore = useOrderHistoryStore();
const showOrderHistory = ref(false);

// Determine active tab based on current route
const activeTab = ref('home');
watch(
    () => usePage().url,
    (newUrl) => {
        if (newUrl.startsWith('/order')) {
            activeTab.value = 'orders';
        } else if (newUrl.startsWith('/user/home') || newUrl === '/') {
            activeTab.value = 'home';
        }
    },
    { immediate: true },
);

const handleNavClick = (tab: 'home' | 'orders') => {
    if (tab === 'home') {
        router.visit(route('user.home')); // Navigate to home page
        showOrderHistory.value = false; // Ensure history drawer is closed
    } else if (tab === 'orders') {
        showOrderHistory.value = true; // Open the order history drawer
    }
};
</script>
