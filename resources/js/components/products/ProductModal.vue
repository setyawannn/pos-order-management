<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black p-4"
            @click.self="$emit('update:show', false)"
        >
            <div class="max-h-[90vh] w-full max-w-2xl overflow-hidden rounded-xl bg-white shadow-xl">
                <div class="border-b border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">
                            {{ isEditing ? 'Edit Dish' : 'Add New Dish' }}
                        </h3>
                        <button @click="$emit('update:show', false)" class="rounded-lg p-2 transition-colors hover:bg-slate-100">
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <form @submit.prevent="handleSubmit" class="max-h-[calc(90vh-140px)] space-y-6 overflow-y-auto p-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Dish Image</label>
                        <div class="flex items-center gap-4">
                            <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-lg bg-slate-100">
                                <img v-if="form.image" :src="form.image" alt="Dish preview" class="h-full w-full object-cover" />
                                <ImageIcon v-else class="h-8 w-8 text-slate-400" />
                            </div>
                            <div class="flex-1">
                                <input
                                    v-model="form.image"
                                    type="url"
                                    placeholder="Enter image URL"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-red-500 focus:ring-2 focus:ring-red-500"
                                />
                                <p class="mt-1 text-xs text-slate-500">Enter a valid image URL</p>
                            </div>
                        </div>
                    </div>

                    <!-- Dish Name -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Dish Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Enter dish name"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-red-500 focus:ring-2 focus:ring-red-500"
                        />
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Category</label>
                        <select
                            v-model="form.category_id"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-red-500 focus:ring-2 focus:ring-red-500"
                        >
                            <option value="">Select a category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Price and Bowls -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Price ($)</label>
                            <input
                                v-model.number="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                placeholder="0.00"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-red-500 focus:ring-2 focus:ring-red-500"
                            />
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Available Bowls</label>
                            <input
                                v-model.number="form.bowls"
                                type="number"
                                min="0"
                                required
                                placeholder="0"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-red-500 focus:ring-2 focus:ring-red-500"
                            />
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Description (Optional)</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Enter dish description"
                            class="w-full resize-none rounded-lg border border-slate-300 px-3 py-2 focus:border-red-500 focus:ring-2 focus:ring-red-500"
                        ></textarea>
                    </div>
                </form>

                <div class="flex justify-end gap-3 border-t border-slate-200 p-6">
                    <button
                        type="button"
                        @click="$emit('update:show', false)"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-slate-700 transition-colors hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button @click="handleSubmit" class="rounded-lg bg-red-500 px-4 py-2 text-white transition-colors hover:bg-red-600">
                        {{ isEditing ? 'Update Dish' : 'Add Dish' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { ImageIcon, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

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

const props = defineProps<{
    show: boolean;
    product?: Product | null;
    categories: Category[];
}>();

const emit = defineEmits<{
    'update:show': [value: boolean];
    save: [product: Product];
}>();

const form = ref({
    name: '',
    price: 0,
    image: '',
    category_id: 0,
    bowls: 0,
    description: '',
});

const isEditing = computed(() => !!props.product);

watch(
    () => props.product,
    (newProduct) => {
        if (newProduct) {
            form.value = {
                name: newProduct.name,
                price: newProduct.price,
                image: newProduct.image,
                category_id: newProduct.category_id,
                bowls: newProduct.bowls,
                description: newProduct.description || '',
            };
        } else {
            // Reset form for new product
            form.value = {
                name: '',
                price: 0,
                image: '',
                category_id: props.categories.length > 0 ? props.categories[0].id : 0,
                bowls: 0,
                description: '',
            };
        }
    },
    { immediate: true },
);

const handleSubmit = () => {
    if (!form.value.name || !form.value.category_id || form.value.price <= 0) {
        return;
    }

    const productData: Product = {
        id: props.product?.id || 0,
        name: form.value.name,
        price: form.value.price,
        image: form.value.image || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=400&fit=crop',
        category_id: form.value.category_id,
        bowls: form.value.bowls,
        description: form.value.description,
    };

    emit('save', productData);
};
</script>
