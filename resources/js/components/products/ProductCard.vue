<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Edit3, Trash2 } from 'lucide-vue-next';

interface Product {
    id: number;
    category_id: number;
    name: string;
    description: string | null;
    price: number;
    image: string | null;
    stock: number;
    is_stock_managed: boolean;
    is_active: boolean;
    category?: any;
}

const props = defineProps<{
    product: Product;
}>();

defineEmits<{
    edit: [product: Product];
    delete: [productId: number];
}>();

const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement;
    img.src = '/placeholder-food.jpg';
    img.alt = 'No image available';
};
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-shadow hover:shadow-md">
        <div class="relative aspect-square overflow-hidden">
            <img :src="product.image || '/placeholder-food.jpg'" :alt="product.name" class="h-full w-full object-cover" @error="handleImageError" />
            <div class="absolute top-3 right-3 flex gap-2">
                <Button
                    variant="ghost"
                    size="icon"
                    @click.stop="$emit('delete', product.id)"
                    class="bg-white/90 text-red-500 shadow-sm hover:bg-white hover:text-red-600"
                    title="Delete product"
                >
                    <Trash2 class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <div class="p-4">
            <h3 class="mb-1 line-clamp-2 font-semibold text-slate-900">{{ product.name }}</h3>
            <div class="mb-3 flex items-center justify-between">
                <span class="text-lg font-semibold text-slate-900">IDR {{ product.price.toLocaleString('id-ID') }}</span>
                <span v-if="product.is_stock_managed" class="text-sm text-slate-500">Stock: {{ product.stock }}</span>
                <span v-else class="text-sm text-slate-500">Unlimited Stock</span>
            </div>

            <Button
                @click="$emit('edit', product)"
                class="flex w-full items-center justify-center gap-2 bg-slate-100 text-slate-700 transition-colors hover:bg-slate-200"
                variant="secondary"
            >
                <Edit3 class="h-4 w-4" />
                Edit Dish
            </Button>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
