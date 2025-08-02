<!-- resources/js/pages/user/home/components/DishCard.vue -->
<template>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex space-x-4">
            <!-- Image -->
            <div class="flex-shrink-0">
                <img :src="dish.image" :alt="dish.name" class="h-20 w-20 rounded-lg object-cover" />
            </div>

            <!-- Content -->
            <div class="min-w-0 flex-1">
                <h3 class="mb-1 text-sm leading-tight font-semibold text-gray-900">
                    {{ dish.name }}
                </h3>
                <PriceDisplay :amount="dish.price" class="mb-2 text-lg font-bold text-red-500" />
                <p class="mb-3 text-xs text-gray-500">{{ dish.bowlsAvailable }} Bowls available</p>

                <!-- Add Button -->
                <button
                    @click="$emit('addToCart', dish)"
                    class="flex w-full items-center justify-center space-x-2 rounded-lg bg-red-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-600"
                >
                    <Plus class="h-4 w-4" />
                    <span>Add to Cart</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import PriceDisplay from '@/components/reusable/PriceDisplay.vue';
import { Plus } from 'lucide-vue-next';

interface Dish {
    id: number;
    name: string;
    price: number;
    image: string;
    category: string;
    bowlsAvailable: number;
}

interface Props {
    dish: Dish;
}

defineProps<Props>();
defineEmits<{
    addToCart: [dish: Dish];
}>();
</script>
