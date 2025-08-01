<!-- pages/admin/products/Index.vue -->
<script setup lang="ts">
import CategoryModal from '@/components/products/CategoryModal.vue';
import ProductCard from '@/components/products/ProductCard.vue';
import ProductModal from '@/components/products/ProductModal.vue';
import ConfirmationModal from '@/components/reusable/ConfirmationModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { router, usePage } from '@inertiajs/vue3'; // Import usePage
import { ChefHat, Package, Plus, Settings } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue'; // Removed onMounted, as it's not strictly necessary for this error
import { useToast } from 'vue-toastification';

// Define interfaces matching your backend models
interface Category {
    id: number;
    name: string;
    description: string | null;
}

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
    category?: Category; // Eager loaded relationship
}

interface PaginatedProducts {
    data: Product[];
    links: any[]; // Pagination links
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        links: Array<{ url: string | null; label: string; active: boolean }>; // More specific type for links
        path: string;
        per_page: number;
        to: number | null;
        total: number;
    };
}

// Props from Inertia Controller
// Use `PropType` if you need to provide a default function or more complex type
const props = defineProps<{
    products: PaginatedProducts; // Expect it to always be PaginatedProducts structure
    categories: Category[];
    filters: {
        search: string | null;
        category_id: number | null;
        is_active: boolean | null;
    };
}>();

const page = usePage();

const toast = useToast();

const showCategoryModal = ref(false);
const showProductModal = ref(false);
const selectedProduct = ref<Product | null>(null);

const showConfirmationModal = ref(false);
const productToDeleteId = ref<number | null>(null);

const activeCategoryTab = ref(props.filters.category_id || (props.categories.length > 0 ? props.categories[0].id : null));
const searchFilter = ref(props.filters.search || '');
const activeFilter = ref(props.filters.is_active === true ? 'true' : props.filters.is_active === false ? 'false' : 'all');

watch(activeCategoryTab, (newCategoryId) => {
    applyFilters(newCategoryId);
});

const filteredProductsOnFrontend = computed(() => {
    if (activeCategoryTab.value === null) {
        return props.products.data;
    }
    return props.products.data.filter((product) => product.category_id === activeCategoryTab.value);
});

const currentCategoryName = computed(() => {
    const category = props.categories.find((cat) => cat.id === activeCategoryTab.value);
    return category?.name || 'All Categories';
});

const openProductModal = (product?: Product) => {
    selectedProduct.value = product || null;
    showProductModal.value = true;
};

const confirmDeleteProduct = (productId: number) => {
    productToDeleteId.value = productId;
    showConfirmationModal.value = true;
};

const cancelDelete = () => {
    showConfirmationModal.value = false;
    productToDeleteId.value = null;
};

const executeDelete = () => {
    if (productToDeleteId.value === null) return;

    router.delete(route('admin.products.destroy', productToDeleteId.value), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Product deleted successfully!');
            cancelDelete();
        },
        onError: (errors) => {
            console.error('Failed to delete product:', errors);
            const errorMessage = errors.message || Object.values(errors)[0] || 'Error deleting product. Please try again.';
            toast.error(String(errorMessage));
            cancelDelete();
        },
    });
};

const saveProduct = () => {
    showProductModal.value = false;
    selectedProduct.value = null;
};

const updateCategories = () => {
    showCategoryModal.value = false;
};

const applyFilters = (categoryId: number | null = null) => {
    const newFilters: { search: string | null; category_id: number | null; is_active: string | null } = {
        search: searchFilter.value || null,
        category_id: categoryId !== null ? categoryId : activeCategoryTab.value,
        is_active: activeFilter.value,
    };

    router.get(route('admin.products.index'), newFilters, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    searchFilter.value = '';
    activeCategoryTab.value = props.categories.length > 0 ? props.categories[0].id : null;
    activeFilter.value = 'all';
    applyFilters(activeCategoryTab.value);
};

const goToPage = (pageUrl: string | null) => {
    if (pageUrl) {
        router.get(
            pageUrl,
            {
                search: searchFilter.value || null,
                category_id: activeCategoryTab.value,
                is_active: activeFilter.value,
            },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
            },
        );
    }
};
</script>

<template>
    <SettingsLayout>
        <div class="p-6">
            <div class="mx-auto max-w-7xl">
                <!-- Header -->
                <div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h2 class="mb-1 text-2xl font-semibold text-slate-900">Products Management</h2>
                        <p class="text-slate-600">Manage your menu items, categories, and pricing</p>
                    </div>
                    <Button @click="showCategoryModal = true" class="flex items-center gap-2 bg-red-500 hover:bg-red-600 focus:ring-red-500">
                        <Settings class="h-4 w-4" />
                        Manage Categories
                    </Button>
                </div>

                <!-- Filters Section -->
                <div class="mb-6 flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:flex-row md:items-end">
                    <div class="flex-1">
                        <label for="search" class="mb-1 block text-sm font-medium text-slate-700">Search Product</label>
                        <Input
                            id="search"
                            v-model="searchFilter"
                            placeholder="Search by name or description..."
                            class="w-full"
                            @keyup.enter="applyFilters()"
                        />
                    </div>
                    <div>
                        <label for="status" class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                        <Select v-model="activeFilter">
                            <SelectTrigger class="w-[180px]">
                                <SelectValue placeholder="All" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="true">Active</SelectItem>
                                <SelectItem value="false">Inactive</SelectItem>
                                <SelectItem value="all">All</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <Button @click="applyFilters()" class="bg-red-500 hover:bg-red-600 focus:ring-red-500">Apply Filters</Button>
                    <Button @click="resetFilters()" variant="outline">Reset</Button>
                </div>

                <!-- Category Tabs -->
                <div class="mb-6" v-if="props.categories.length > 0">
                    <div class="border-b border-slate-200">
                        <nav class="flex space-x-8 overflow-x-auto pb-2">
                            <Button
                                variant="ghost"
                                @click="activeCategoryTab = null"
                                :class="[
                                    'rounded-none border-b-2 px-1 py-3 text-sm font-medium whitespace-nowrap transition-colors',
                                    activeCategoryTab === null
                                        ? 'border-red-500 text-red-600 hover:text-red-600'
                                        : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700',
                                ]"
                            >
                                All Products
                            </Button>
                            <Button
                                v-for="category in props.categories"
                                :key="category.id"
                                variant="ghost"
                                @click="activeCategoryTab = category.id"
                                :class="[
                                    'rounded-none border-b-2 px-1 py-3 text-sm font-medium whitespace-nowrap transition-colors',
                                    activeCategoryTab === category.id
                                        ? 'border-red-500 text-red-600 hover:text-red-600'
                                        : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700',
                                ]"
                            >
                                {{ category.name }}
                            </Button>
                        </nav>
                    </div>
                </div>

                <!-- Empty State for Categories -->
                <div v-if="props.categories.length === 0" class="py-16 text-center">
                    <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-slate-100">
                        <Package class="h-12 w-12 text-slate-400" />
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-slate-900">No categories yet</h3>
                    <p class="mx-auto mb-6 max-w-md text-slate-600">Start by creating your first category to organize your menu items</p>
                    <Button
                        @click="showCategoryModal = true"
                        class="inline-flex items-center gap-2 bg-red-500 px-6 py-3 text-white transition-colors hover:bg-red-600"
                    >
                        <Plus class="h-5 w-5" />
                        Create First Category
                    </Button>
                </div>

                <div
                    v-else-if="props.products && (filteredProductsOnFrontend.length > 0 || props.categories.length > 0)"
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
                        v-for="product in filteredProductsOnFrontend"
                        :key="product.id"
                        :product="product"
                        @edit="openProductModal(product)"
                        @delete="confirmDeleteProduct(product.id)"
                    />
                </div>

                <div v-else-if="props.products && props.categories.length > 0 && filteredProductsOnFrontend.length === 0" class="py-16 text-center">
                    <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-slate-100">
                        <ChefHat class="h-12 w-12 text-slate-400" />
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-slate-900">No dishes in {{ currentCategoryName }}</h3>
                    <p class="mx-auto mb-6 max-w-md text-slate-600">Add your first dish to this category to get started</p>
                    <Button
                        @click="openProductModal()"
                        class="inline-flex items-center gap-2 bg-red-500 px-6 py-3 text-white transition-colors hover:bg-red-600"
                    >
                        <Plus class="h-5 w-5" />
                        Add First Dish
                    </Button>
                </div>

                <div v-if="props.products && props.products.meta && props.products.meta.last_page > 1" class="mt-8 flex justify-center">
                    <nav class="flex items-center gap-2">
                        <Button
                            v-for="(link, index) in props.products.meta.links"
                            :key="index"
                            @click="goToPage(link.url)"
                            :disabled="!link.url"
                            :variant="link.active ? 'default' : 'outline'"
                            :class="{ 'bg-red-500 text-white hover:bg-red-600': link.active }"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </div>

            <CategoryModal v-model:open="showCategoryModal" :categories="props.categories" @update-categories="updateCategories" />
            <ProductModal v-model:open="showProductModal" :product="selectedProduct" :categories="props.categories" @saved="saveProduct" />
            <ConfirmationModal
                :show="showConfirmationModal"
                title="Confirm Deletion"
                message="Are you sure you want to delete this product? This action cannot be undone and will permanently remove the product."
                @confirm="executeDelete"
                @cancel="cancelDelete"
            />
        </div>
    </SettingsLayout>
</template>
