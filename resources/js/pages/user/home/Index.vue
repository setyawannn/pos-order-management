<!-- resources/js/pages/user/home/Index.vue -->
<template>
    <UserLayout>
        <div class="space-y-6 px-4 py-4">
            <!-- Search Bar -->
            <div class="relative">
                <Search class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search for food, coffee, etc..."
                    class="w-full rounded-xl border border-gray-200 bg-white py-3 pr-4 pl-10 text-sm text-gray-800 focus:border-transparent focus:ring-2 focus:ring-red-500 focus:outline-none"
                />
            </div>

            <!-- Category Tabs -->
            <CategoryTabs v-model="activeCategory" :categories="categories" />

            <!-- Order Type Selection -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900">Order Type</h3>
                        <p class="text-sm text-gray-500">Choose your preferred option</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button
                        @click="cartStore.setOrderType('dine_in')"
                        :class="[
                            'flex-1 rounded-lg px-4 py-2 text-sm font-medium transition-colors',
                            cartStore.orderType === 'dine_in' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                        ]"
                    >
                        Dine In
                    </button>
                    <button
                        @click="cartStore.setOrderType('take_away')"
                        :class="[
                            'flex-1 rounded-lg px-4 py-2 text-sm font-medium transition-colors',
                            cartStore.orderType === 'take_away' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                        ]"
                    >
                        Take Away
                    </button>
                </div>
            </div>

            <!-- Products Grid -->
            <div>
                <Title class="mb-4">Choose Dishes</Title>
                <div class="grid grid-cols-1 gap-4">
                    <ProductCard v-for="product in filteredProducts" :key="product.id" :product="product" @add-to-cart="handleAddToCart" />
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<script setup lang="ts">
import Title from '@/components/reusable/Title.vue';
import UserLayout from '@/layouts/UserLayout.vue';
import { useCartStore } from '@/stores/cartStore';
import type { Category, Product } from '@/types';
import { Search } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import CategoryTabs from './components/CategoryTabs.vue';
import ProductCard from './components/ProductCard.vue';

interface Props {
    products: Product[];
    categories: Category[];
}

const props = defineProps<Props>();
const cartStore = useCartStore();
const searchQuery = ref('');
const activeCategory = ref<number | 'all'>('all');

const filteredProducts = computed(() => {
    let filtered = props.products.filter((product) => product.is_active);

    if (activeCategory.value !== 'all') {
        filtered = filtered.filter((product) => product.category_id === activeCategory.value);
    }

    if (searchQuery.value) {
        filtered = filtered.filter(
            (product) =>
                product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                product.description?.toLowerCase().includes(searchQuery.value.toLowerCase()),
        );
    }

    return filtered;
});

const handleAddToCart = (product: Product) => {
    cartStore.addItem({
        id: product.id,
        name: product.name,
        price: product.price,
        image: product.image,
    });
};

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const tableNumber = urlParams.get('table_number');

    if (tableNumber) {
        cartStore.tableNumber = tableNumber;
        cartStore.setOrderType('dine_in');
    }
});
</script>
