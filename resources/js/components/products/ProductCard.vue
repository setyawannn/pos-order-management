<!-- components/products/ProductCard.vue -->
<template>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-shadow hover:shadow-md">
        <div class="relative aspect-square overflow-hidden">
            <img :src="product.image" :alt="product.name" class="h-full w-full object-cover" @error="handleImageError" />
            <div class="absolute top-3 right-3">
                <button
                    @click="$emit('delete', product.id)"
                    class="bg-opacity-90 hover:bg-opacity-100 rounded-lg bg-white p-2 shadow-sm transition-all"
                >
                    <Trash2 class="h-4 w-4 text-red-500" />
                </button>
            </div>
        </div>

        <div class="p-4">
            <h3 class="mb-1 line-clamp-2 font-semibold text-slate-900">{{ product.name }}</h3>
            <div class="mb-3 flex items-center justify-between">
                <span class="text-lg font-semibold text-slate-900">${{ product.price.toFixed(2) }}</span>
                <span class="text-sm text-slate-500">{{ product.bowls }} Bowls</span>
            </div>

            <button
                @click="$emit('edit', product)"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-slate-100 px-4 py-2 text-slate-700 transition-colors hover:bg-slate-200"
            >
                <Edit3 class="h-4 w-4" />
                Edit dish
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Edit3, Trash2 } from 'lucide-vue-next';

interface Product {
    id: number;
    name: string;
    price: number;
    image: string;
    category_id: number;
    bowls: number;
    description?: string;
}

defineProps<{
    product: Product;
}>();

defineEmits<{
    edit: [product: Product];
    delete: [productId: number];
}>();

const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement;
    img.src = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=400&fit=crop';
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
