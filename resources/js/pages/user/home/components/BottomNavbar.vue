<!-- resources/js/pages/user/home/components/BottomNavbar.vue -->
<template>
    <nav class="fixed right-0 bottom-0 left-0 z-30 border-t border-gray-200 bg-white">
        <div class="flex items-center justify-around py-2">
            <button
                v-for="item in navItems"
                :key="item.name"
                @click="handleNavClick(item)"
                :class="[
                    'relative flex flex-col items-center rounded-lg px-3 py-2 transition-colors',
                    item.active ? 'text-red-500' : 'text-gray-400 hover:text-gray-600',
                ]"
            >
                <component :is="item.icon" class="mb-1 h-6 w-6" />
                <span class="text-xs font-medium">{{ item.name }}</span>

                <!-- Badge for orders -->
                <span v-if="item.name === 'Orders' && orderHistoryStore.hasOrders" class="absolute -top-1 -right-1 h-3 w-3 rounded-full bg-red-500" />
            </button>
        </div>

        <!-- Order History Drawer -->
        <OrderHistoryDrawer v-model:open="showOrderHistory" />
    </nav>
</template>

<script setup lang="ts">
import { useOrderHistoryStore } from '@/stores/orderHistoryStore';
import { Home, Search, Settings, ShoppingBag, User } from 'lucide-vue-next';
import { ref } from 'vue';
import OrderHistoryDrawer from './OrderHistoryDrawer.vue';

const orderHistoryStore = useOrderHistoryStore();
const showOrderHistory = ref(false);

const navItems = [
    { name: 'Home', icon: Home, active: true, action: 'home' },
    { name: 'Search', icon: Search, active: false, action: 'search' },
    { name: 'Orders', icon: ShoppingBag, active: false, action: 'orders' },
    { name: 'Profile', icon: User, active: false, action: 'profile' },
    { name: 'Settings', icon: Settings, active: false, action: 'settings' },
];

const handleNavClick = (item: any) => {
    switch (item.action) {
        case 'orders':
            showOrderHistory.value = true;
            break;
        case 'home':
            // Already on home, maybe scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
            break;
        default:
            // Handle other navigation items
            console.log(`Navigate to ${item.name}`);
    }
};
</script>
