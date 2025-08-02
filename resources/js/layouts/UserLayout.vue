<!-- resources/js/layouts/UserLayout.vue -->
<template>
    <div class="relative min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="sticky top-0 z-40 border-b border-gray-200 bg-white shadow-sm">
            <div class="px-4 py-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-500">
                            <span class="text-lg font-bold text-white">JR</span>
                        </div>
                        <div>
                            <h1 class="text-lg font-semibold text-gray-900">Cerita Sena</h1>
                            <p class="text-sm text-gray-500">{{ currentDate }}</p>
                        </div>
                    </div>

                    <!-- Cart Button -->
                    <button
                        @click="cartStore.openDrawer"
                        class="relative rounded-lg bg-red-500 p-2 text-white shadow-lg transition-transform active:scale-95"
                    >
                        <ShoppingCart class="h-6 w-6" />
                        <span
                            v-if="cartStore.totalItems > 0"
                            class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs font-medium text-white"
                        >
                            {{ cartStore.totalItems }}
                        </span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="min-h-screen pb-20">
            <slot />
        </main>

        <!-- Bottom Navigation -->
        <BottomNavbar />

        <!-- Cart Drawer -->
        <CartDrawer />
    </div>
</template>

<script setup lang="ts">
import BottomNavbar from '@/pages/user/home/components/BottomNavbar.vue';
import CartDrawer from '@/pages/user/home/components/CartDrawer.vue';
import { useCartStore } from '@/stores/cartStore';
import { ShoppingCart } from 'lucide-vue-next';
import { computed, onUnmounted } from 'vue';

const cartStore = useCartStore();

const currentDate = computed(() => {
    return new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
});

// Cleanup on unmount
onUnmounted(() => {
    document.body.style.overflow = '';
});
</script>
