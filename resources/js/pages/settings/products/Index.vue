<!-- pages/settings/products/Index.vue -->
<template>
    <SettingsLayout>
        <div class="p-6">
            <div class="mx-auto max-w-7xl">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="mb-1 text-2xl font-semibold text-slate-900">Products Management</h2>
                        <p class="text-slate-600">Manage your menu items, categories, and pricing</p>
                    </div>
                    <button
                        @click="showCategoryModal = true"
                        class="flex items-center gap-2 rounded-lg bg-red-500 px-4 py-2 text-white transition-colors hover:bg-red-600"
                    >
                        <Settings class="h-4 w-4" />
                        Manage Categories
                    </button>
                </div>

                <!-- Category Tabs -->
                <div class="mb-6" v-if="categories.length > 0">
                    <div class="border-b border-slate-200">
                        <nav class="flex space-x-8">
                            <button
                                v-for="category in categories"
                                :key="category.id"
                                @click="activeCategory = category.id"
                                :class="[
                                    'border-b-2 px-1 py-3 text-sm font-medium transition-colors',
                                    activeCategory === category.id
                                        ? 'border-red-500 text-red-600'
                                        : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700',
                                ]"
                            >
                                {{ category.name }}
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Empty State for Categories -->
                <div v-if="categories.length === 0" class="py-16 text-center">
                    <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-slate-100">
                        <Package class="h-12 w-12 text-slate-400" />
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-slate-900">No categories yet</h3>
                    <p class="mx-auto mb-6 max-w-md text-slate-600">Start by creating your first category to organize your menu items</p>
                    <button
                        @click="showCategoryModal = true"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-6 py-3 text-white transition-colors hover:bg-red-600"
                    >
                        <Plus class="h-5 w-5" />
                        Create First Category
                    </button>
                </div>

                <!-- Products Grid -->
                <div
                    v-else-if="filteredProducts.length > 0 || categories.length > 0"
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <!-- Add New Product Card -->
                    <div
                        @click="openProductModal()"
                        class="group flex min-h-[280px] cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 p-8 transition-all hover:border-red-400 hover:bg-red-50"
                    >
                        <Plus class="mb-3 h-8 w-8 text-slate-400 group-hover:text-red-500" />
                        <span class="font-medium text-slate-600 group-hover:text-red-600">Add new dish</span>
                    </div>

                    <!-- Product Cards -->
                    <ProductCard
                        v-for="product in filteredProducts"
                        :key="product.id"
                        :product="product"
                        @edit="editProduct"
                        @delete="deleteProduct"
                    />
                </div>

                <!-- Empty State for Products -->
                <div v-else-if="categories.length > 0 && filteredProducts.length === 0" class="py-16 text-center">
                    <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-slate-100">
                        <ChefHat class="h-12 w-12 text-slate-400" />
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-slate-900">No dishes in {{ currentCategoryName }}</h3>
                    <p class="mx-auto mb-6 max-w-md text-slate-600">Add your first dish to this category to get started</p>
                    <button
                        @click="openProductModal()"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-6 py-3 text-white transition-colors hover:bg-red-600"
                    >
                        <Plus class="h-5 w-5" />
                        Add First Dish
                    </button>
                </div>

                <!-- Action Buttons -->
                <div v-if="categories.length > 0" class="mt-8 flex justify-end gap-3 border-t border-slate-200 pt-6">
                    <button
                        @click="discardChanges"
                        class="rounded-lg border border-slate-300 px-6 py-2 text-slate-700 transition-colors hover:bg-slate-50"
                    >
                        Discard Changes
                    </button>
                    <button @click="saveChanges" class="rounded-lg bg-red-500 px-6 py-2 text-white transition-colors hover:bg-red-600">
                        Save Changes
                    </button>
                </div>

                <!-- Modals using Shadcn Dialog functionality only -->
                <CategoryModal v-model:open="showCategoryModal" :categories="categories" @update="updateCategories" />
                <ProductModal v-model:open="showProductModal" :product="selectedProduct" :categories="categories" @save="saveProduct" />
            </div>
        </div>
    </SettingsLayout>
</template>

<script setup lang="ts">
import CategoryModal from '@/components/products/CategoryModal.vue';
import ProductCard from '@/components/products/ProductCard.vue';
import ProductModal from '@/components/products/ProductModal.vue';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { ChefHat, Package, Plus, Settings } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Product {
    id: number;
    name: string;
    price: number;
    image: string;
    category_id: number;
    bowls: number;
    description?: string;
}

// Dummy data - replace with real data from backend
const categories = ref<Category[]>([
    { id: 1, name: 'Hot Dishes', slug: 'hot-dishes' },
    { id: 2, name: 'Cold Dishes', slug: 'cold-dishes' },
    { id: 3, name: 'Soup', slug: 'soup' },
    { id: 4, name: 'Grill', slug: 'grill' },
    { id: 5, name: 'Appetizer', slug: 'appetizer' },
    { id: 6, name: 'Dessert', slug: 'dessert' },
]);

const products = ref<Product[]>([
    {
        id: 1,
        name: 'Spicy seasoned seafood noodles',
        price: 2.29,
        image: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400&h=400&fit=crop',
        category_id: 1,
        bowls: 20,
        description: 'Delicious spicy noodles with fresh seafood',
    },
    {
        id: 2,
        name: 'Salted Pasta with mushroom sauce',
        price: 2.69,
        image: 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?w=400&h=400&fit=crop',
        category_id: 1,
        bowls: 30,
        description: 'Creamy pasta with rich mushroom sauce',
    },
    {
        id: 3,
        name: 'Beef dumpling in hot and sour soup',
        price: 2.99,
        image: 'https://images.unsplash.com/photo-1496116218417-1a781b1c416c?w=400&h=400&fit=crop',
        category_id: 3,
        bowls: 16,
        description: 'Traditional dumplings in flavorful broth',
    },
    {
        id: 4,
        name: 'Healthy noodle with spinach leaf',
        price: 3.29,
        image: 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?w=400&h=400&fit=crop',
        category_id: 1,
        bowls: 22,
        description: 'Nutritious noodles with fresh spinach',
    },
    {
        id: 5,
        name: 'Hot spicy fried rice with omelet',
        price: 3.49,
        image: 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=400&h=400&fit=crop',
        category_id: 1,
        bowls: 15,
        description: 'Spicy fried rice topped with fluffy omelet',
    },
    {
        id: 6,
        name: 'Spicy instant noodle with special omelet',
        price: 3.59,
        image: 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400&h=400&fit=crop',
        category_id: 1,
        bowls: 17,
        description: 'Quick and spicy noodles with special omelet',
    },
]);

const activeCategory = ref(1);
const showCategoryModal = ref(false);
const showProductModal = ref(false);
const selectedProduct = ref<Product | null>(null);

const filteredProducts = computed(() => {
    return products.value.filter((product) => product.category_id === activeCategory.value);
});

const currentCategoryName = computed(() => {
    const category = categories.value.find((cat) => cat.id === activeCategory.value);
    return category?.name || '';
});

const openProductModal = (product?: Product) => {
    selectedProduct.value = product || null;
    showProductModal.value = true;
};

const editProduct = (product: Product) => {
    selectedProduct.value = product;
    showProductModal.value = true;
};

const deleteProduct = (productId: number) => {
    const index = products.value.findIndex((p) => p.id === productId);
    if (index > -1) {
        products.value.splice(index, 1);
    }
};

const saveProduct = (product: Product) => {
    if (selectedProduct.value) {
        // Update existing product
        const index = products.value.findIndex((p) => p.id === product.id);
        if (index > -1) {
            products.value[index] = product;
        }
    } else {
        // Add new product
        const newId = Math.max(...products.value.map((p) => p.id), 0) + 1;
        products.value.push({ ...product, id: newId });
    }

    showProductModal.value = false;
    selectedProduct.value = null;
};

const updateCategories = (newCategories: Category[]) => {
    categories.value = newCategories;
    // If active category was deleted, switch to first available
    if (!categories.value.find((cat) => cat.id === activeCategory.value) && categories.value.length > 0) {
        activeCategory.value = categories.value[0].id;
    }
};

const discardChanges = () => {
    // Reset to original state - in real app, refetch from backend
    console.log('Discard changes');
};

const saveChanges = () => {
    // Save to backend
    console.log('Save changes');
};
</script>
