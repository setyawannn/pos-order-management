<!-- components/products/ProductModal.vue -->
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ImageIcon, Loader2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useToast } from 'vue-toastification';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';

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
    image: string | null; // This is the URL from DB
    stock: number;
    is_stock_managed: boolean;
    is_active: boolean;
}

const props = defineProps<{
    open: boolean;
    product?: Product | null;
    categories: Category[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    saved: [];
}>();

const showDialog = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});

const toast = useToast();

const form = useForm({
    _method: 'post',
    name: '',
    category_id: props.categories.length > 0 ? props.categories[0].id : 0,
    price: 0,
    // image field for NEW file upload
    image: null as File | null,
    // This flag indicates if the user explicitly clicked "Remove Image"
    // We send this as a separate field to the backend.
    should_remove_image: false as boolean,
    stock: 0,
    is_stock_managed: false as boolean,
    is_active: true as boolean,
    description: '',
});

const isEditing = computed(() => !!props.product);
const imagePreviewUrl = ref<string | null>(null);

watch(
    () => props.product,
    (newProduct) => {
        if (newProduct) {
            form.name = newProduct.name;
            form.category_id = newProduct.category_id;
            form.price = newProduct.price;
            // Existing image URL for preview and backend awareness
            imagePreviewUrl.value = newProduct.image;
            // Reset file input and remove flag for edit mode initially
            form.image = null;
            form.should_remove_image = false;

            form.stock = newProduct.stock;
            form.is_stock_managed = newProduct.is_stock_managed;
            form.is_active = newProduct.is_active;
            form.description = newProduct.description || '';
            form._method = 'put';
        } else {
            // Reset form for new product
            form.reset();
            form.category_id = props.categories.length > 0 ? props.categories[0].id : 0;
            form.is_stock_managed = false;
            form.is_active = true;
            form._method = 'post';
            imagePreviewUrl.value = null;
            form.should_remove_image = false;
        }
    },
    { immediate: true },
);

// Function to reset form to initial state when dialog opens (useful if cancelled)
const resetFormAndImage = () => {
    form.reset(); // Resets useForm instance
    // Manually reset image-related state because form.reset() doesn't always
    // perfectly handle complex file input states or custom refs
    if (isEditing.value && props.product) {
        // If editing, revert to original product's image
        imagePreviewUrl.value = props.product.image;
        form.image = null;
        form.should_remove_image = false;
    } else {
        // If creating, clear everything
        imagePreviewUrl.value = null;
        form.image = null;
        form.should_remove_image = false;
    }
    // Also clear validation errors
    form.clearErrors();
};

// Listen to dialog open state to reset form
watch(showDialog, (newVal) => {
    if (newVal) {
        // Dialog is opening
        // Populate form based on `props.product` handled by the initial watch.
        // No explicit reset needed here beyond what the initial watch already does
        // unless you want a full reset on every open regardless of edit/create context.
        // For consistent behavior, the `watch(() => props.product, ...)` is best.
    } else {
        // Dialog is closing
        resetFormAndImage(); // Reset when dialog closes to prevent stale data for next open
    }
});

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.image = file; // Set the file to the form data
        imagePreviewUrl.value = URL.createObjectURL(file); // Create URL for preview
        form.should_remove_image = false; // If a new image is selected, don't remove existing
    } else {
        // If file input is cleared (e.g., user selected a file then cancelled it)
        form.image = null;
        // Do NOT set should_remove_image here, as user didn't explicitly click remove.
        // Keep the current_image_url if it was there.
        if (!imagePreviewUrl.value) {
            // Only clear preview if no existing image and no new file
            imagePreviewUrl.value = null;
        }
    }
};

const removeImage = () => {
    form.image = null; // Clear any newly selected file
    imagePreviewUrl.value = null; // Clear image preview
    form.should_remove_image = true; // Signal to backend to remove the image
};

const handleSubmit = () => {
    if (!form.name || form.category_id === 0 || form.price <= 0 || (form.is_stock_managed && form.stock < 0)) {
        toast.error('Please fill in all required fields and ensure price/stock are valid.');
        return;
    }

    const inertiaOptions = {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            showDialog.value = false;
            // No need to call form.reset() here directly after closing, as the watch(showDialog) handles it.
            toast.success(isEditing.value ? 'Product updated successfully!' : 'Product created successfully!');
        },
        onError: (errors: Record<string, string>) => {
            console.error('Product save error:', errors);
            const errorMessage =
                errors.message || (Object.values(errors).length > 0 ? Object.values(errors)[0] : 'Failed to save product. Please try again.');
            toast.error(String(errorMessage));
        },
        onFinish: () => {
            // Note: form.processing automatically resets
        },
    };

    if (isEditing.value && props.product) {
        // When updating, send 'should_remove_image' flag if explicitly set.
        // 'image' will be sent if it's a File (new upload), otherwise it won't be sent
        // unless it's explicitly null and should_remove_image is false (not possible with current logic)
        form.post(route('admin.products.update', props.product.id), inertiaOptions);
    } else {
        form.post(route('admin.products.store'), inertiaOptions);
    }
};
</script>

<template>
    <Dialog v-model:open="showDialog">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[425px] md:max-w-2xl">
            <DialogHeader>
                <DialogTitle>{{ isEditing ? 'Edit Dish' : 'Add New Dish' }}</DialogTitle>
                <DialogDescription>
                    {{ isEditing ? 'Update the details of your dish.' : 'Add a new dish to your menu.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-6 py-4">
                <div>
                    <Label for="dish-image">Dish Image</Label>
                    <div class="mt-2 flex items-center gap-4">
                        <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-lg bg-slate-100">
                            <img v-if="imagePreviewUrl" :src="imagePreviewUrl" alt="Dish preview" class="h-full w-full object-cover" />
                            <ImageIcon v-else class="h-8 w-8 text-slate-400" />
                        </div>
                        <div class="flex-1">
                            <Input id="dish-image" type="file" accept="image/*" @change="handleImageChange" class="w-full" />
                            <p class="mt-1 text-xs text-slate-500">Upload an image file (Max 2MB)</p>
                            <Button v-if="imagePreviewUrl" type="button" variant="link" size="sm" @click="removeImage" class="text-red-500">
                                Remove Image
                            </Button>
                            <p v-if="form.errors.image" class="mt-1 text-sm text-red-500">{{ form.errors.image }}</p>
                        </div>
                    </div>
                </div>

                <div>
                    <Label for="name">Dish Name</Label>
                    <Input id="name" v-model="form.name" type="text" placeholder="Enter dish name" class="mt-1" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                </div>

                <div>
                    <Label for="category">Category</Label>
                    <Select v-model="form.category_id" class="mt-1">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select a category" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-500">{{ form.errors.category_id }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label for="price">Price (IDR)</Label>
                        <Input id="price" v-model.number="form.price" type="number" min="0" placeholder="0" class="mt-1" />
                        <p v-if="form.errors.price" class="mt-1 text-sm text-red-500">{{ form.errors.price }}</p>
                    </div>
                    <div class="flex flex-col">
                        <Label for="stock-managed">Stock Management</Label>
                        <div class="mt-2 flex items-center gap-2">
                            <Switch id="stock-managed" v-model:checked="form.is_stock_managed" />
                            <span>{{ form.is_stock_managed ? 'Managed' : 'Not Managed' }}</span>
                        </div>
                        <p v-if="form.errors.is_stock_managed" class="mt-1 text-sm text-red-500">{{ form.errors.is_stock_managed }}</p>
                    </div>
                </div>

                <div v-if="form.is_stock_managed">
                    <Label for="stock">Available Stock</Label>
                    <Input id="stock" v-model.number="form.stock" type="number" min="0" placeholder="0" class="mt-1" />
                    <p v-if="form.errors.stock" class="mt-1 text-sm text-red-500">{{ form.errors.stock }}</p>
                </div>

                <div>
                    <Label for="is_active">Active on Menu</Label>
                    <div class="mt-2 flex items-center gap-2">
                        <Switch id="is_active" v-model:checked="form.is_active" />
                        <span>{{ form.is_active ? 'Visible' : 'Hidden' }}</span>
                    </div>
                    <p v-if="form.errors.is_active" class="mt-1 text-sm text-red-500">{{ form.errors.is_active }}</p>
                </div>

                <div>
                    <Label for="description">Description (Optional)</Label>
                    <Textarea id="description" v-model="form.description" rows="3" placeholder="Enter dish description" class="mt-1"></Textarea>
                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
                </div>
            </form>

            <DialogFooter>
                <Button type="button" variant="outline" @click="showDialog = false">Cancel</Button>
                <Button type="submit" @click="handleSubmit" :disabled="form.processing" class="bg-red-500 hover:bg-red-600">
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    {{ isEditing ? 'Update Dish' : 'Add Dish' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
