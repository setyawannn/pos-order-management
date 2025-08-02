<!-- resources/js/pages/user/home/components/ProductCard.vue -->
<template>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex space-x-4">
            <!-- Image -->
            <div class="flex-shrink-0">
                <img
                    :src="product.image || '/images/placeholder.jpg'"
                    :alt="product.name"
                    class="h-20 w-20 rounded-lg bg-gray-100 object-cover"
                    @error="handleImageError"
                />
            </div>

            <!-- Content -->
            <div class="min-w-0 flex-1">
                <h3 class="mb-1 text-sm leading-tight font-semibold text-gray-900">
                    {{ product.name }}
                </h3>
                <p v-if="product.description" class="mb-2 line-clamp-2 text-xs text-gray-500">
                    {{ product.description }}
                </p>
                <PriceDisplay :amount="product.price" class="mb-2 text-lg font-bold text-red-500" />

                <!-- Stock Info -->
                <div class="mb-3 flex items-center justify-between">
                    <p v-if="product.is_stock_managed" class="text-xs text-gray-500">{{ product.stock }} items available</p>
                    <p v-else class="text-xs text-green-600">Always available</p>
                </div>

                <!-- Add Button -->
                <button
                    @click="$emit('addToCart', product)"
                    :disabled="product.is_stock_managed && product.stock <= 0"
                    :class="[
                        'flex w-full items-center justify-center space-x-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors',
                        product.is_stock_managed && product.stock <= 0
                            ? 'cursor-not-allowed bg-gray-300 text-gray-500'
                            : 'bg-red-500 text-white hover:bg-red-600 active:scale-95',
                    ]"
                >
                    <Plus class="h-4 w-4" />
                    <span>{{ product.is_stock_managed && product.stock <= 0 ? 'Out of Stock' : 'Add to Cart' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import PriceDisplay from '@/components/reusable/PriceDisplay.vue';
import type { Product } from '@/types';
import { Plus } from 'lucide-vue-next';

interface Props {
    product: Product;
}

defineProps<Props>();
defineEmits<{
    addToCart: [product: Product];
}>();

const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement;
    img.src = '/images/placeholder.jpg';
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
