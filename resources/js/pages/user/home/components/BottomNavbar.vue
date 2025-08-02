<!-- resources/js/pages/user/home/components/BottomNavbar.vue -->
<template>
    <nav class="fixed right-0 bottom-0 left-0 z-30 border-t border-gray-200 bg-white">
        <div class="flex items-center justify-around py-2">
            <button
                v-for="item in navItems"
                :key="item.name"
                :class="[
                    'flex flex-col items-center rounded-lg px-3 py-2 transition-colors',
                    item.active ? 'text-red-500' : 'text-gray-400 hover:text-gray-600',
                ]"
            >
                <component :is="item.icon" class="mb-1 h-6 w-6" />
                <span class="text-xs font-medium">{{ item.name }}</span>
            </button>
        </div>
    </nav>
</template>

<script setup lang="ts">
import { Home, Search, Settings, ShoppingBag, User } from 'lucide-vue-next';

const navItems = [
    { name: 'Home', icon: Home, active: true },
    { name: 'Search', icon: Search, active: false },
    { name: 'Orders', icon: ShoppingBag, active: false },
    { name: 'Profile', icon: User, active: false },
    { name: 'Settings', icon: Settings, active: false },
];
</script>
