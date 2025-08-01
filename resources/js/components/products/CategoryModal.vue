<!-- components/products/CategoryModal.vue -->
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Check, Edit3, Plus, Trash2, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useToast } from 'vue-toastification';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Loader2 } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
    description: string | null;
}

interface LocalCategory extends Category {
    editing?: boolean;
    originalName?: string;
    originalDescription?: string | null;
    isNew?: boolean;
    isSaving?: boolean;
    isDeleting?: boolean;
}

const props = defineProps<{
    open: boolean;
    categories: Category[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'update-categories': [];
}>();

const showDialog = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});

const localCategories = ref<LocalCategory[]>([]);
const toast = useToast();

const newCategoryForm = useForm({
    name: '',
    description: '', // Keep for backend model, but not displayed
});

watch(
    () => props.categories,
    (newCategories) => {
        localCategories.value = newCategories.map((cat) => ({
            ...cat,
            editing: false,
            originalName: cat.name,
            originalDescription: cat.description,
            isSaving: false, // Initialize loading states
            isDeleting: false,
        }));
    },
    { immediate: true },
);

const addNewCategoryField = () => {
    localCategories.value.push({
        id: Date.now(), // Temporary ID for new category
        name: '',
        description: '', // Default for new, even if not shown
        editing: true,
        isNew: true,
        isSaving: false,
        isDeleting: false,
    });
};

const saveExistingCategory = (category: LocalCategory) => {
    // Only send the fields that are actually being edited/relevant
    const form = useForm({
        name: category.name,
        // Description is still part of the model but not from UI input
        // If your backend validation strictly requires description, you might send
        // category.description or null. If it's nullable, no need to send if empty.
    });

    category.isSaving = true; // Set loading state

    form.put(route('admin.categories.update', category.id), {
        preserveScroll: true,
        onSuccess: () => {
            category.editing = false;
            category.originalName = category.name;
            // category.originalDescription is now implicitly not set by form input
            toast.success('Category updated successfully!');
            emit('update-categories');
        },
        onError: (errors) => {
            console.error('Update category error:', errors);
            const errorMessage = Object.values(errors)[0] || 'Failed to update category. Please try again.';
            toast.error(String(errorMessage));
            category.name = category.originalName || ''; // Revert name
            // category.description = category.originalDescription || ''; // Revert description if needed
            category.editing = false;
        },
        onFinish: () => {
            category.isSaving = false; // Reset loading state
        },
    });
};

const saveNewCategory = (category: LocalCategory) => {
    newCategoryForm.name = category.name;
    newCategoryForm.description = ''; // Ensure description is empty/null for new categories if not provided by UI

    category.isSaving = true; // Set loading state for the new item

    newCategoryForm.post(route('admin.categories.store'), {
        preserveScroll: true,
        onSuccess: () => {
            localCategories.value = localCategories.value.filter((c) => c.id !== category.id); // Remove temp item
            newCategoryForm.reset();
            toast.success('Category created successfully!');
            emit('update-categories');
        },
        onError: (errors) => {
            console.error('Create category error:', errors);
            const errorMessage = Object.values(errors)[0] || 'Failed to create category. Please try again.';
            toast.error(String(errorMessage));
        },
        onFinish: () => {
            category.isSaving = false; // Reset loading state
        },
    });
};

const handleSaveCategory = (category: LocalCategory) => {
    if (category.name.trim() === '') {
        toast.error('Category name cannot be empty.');
        return;
    }

    if (category.isNew) {
        saveNewCategory(category);
    } else {
        saveExistingCategory(category);
    }
};

const cancelEdit = (category: LocalCategory) => {
    if (category.isNew) {
        localCategories.value = localCategories.value.filter((c) => c.id !== category.id);
    } else {
        category.name = category.originalName || category.name;
        category.description = category.originalDescription || null; // Revert description
        category.editing = false;
    }
};

const deleteCategory = (categoryId: number) => {
    const categoryToDelete = localCategories.value.find((c) => c.id === categoryId);
    if (!categoryToDelete) return;

    if (confirm('Are you sure you want to delete this category? All associated products will also be deleted.')) {
        categoryToDelete.isDeleting = true; // Set loading state for deletion

        useForm({}).delete(route('admin.categories.destroy', categoryId), {
            preserveScroll: true,
            onSuccess: () => {
                localCategories.value = localCategories.value.filter((c) => c.id !== categoryId);
                toast.success('Category deleted successfully!');
                emit('update-categories');
            },
            onError: (errors) => {
                console.error('Delete category error:', errors);
                const errorMessage = errors.message || Object.values(errors)[0] || 'Failed to delete category.';
                toast.error(String(errorMessage));
            },
            onFinish: () => {
                if (categoryToDelete) {
                    // Check again as it might be removed from array
                    categoryToDelete.isDeleting = false; // Reset loading state
                }
            },
        });
    }
};
</script>

<template>
    <Dialog v-model:open="showDialog">
        <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-[425px] md:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Manage Categories</DialogTitle>
                <DialogDescription> Add, edit, or delete your product categories. Changes are saved instantly. </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <div v-for="category in localCategories" :key="category.id" class="flex items-center gap-3 rounded-lg border border-slate-200 p-3">
                    <div class="flex-1">
                        <!-- Only name input, description is removed from UI -->
                        <Label :for="`category-name-${category.id}`" class="sr-only">Category Name</Label>
                        <Input
                            :id="`category-name-${category.id}`"
                            v-model="category.name"
                            :class="{
                                'pointer-events-none bg-slate-50 opacity-80': !category.editing,
                                'focus-visible:ring-red-500': category.editing,
                            }"
                            :readonly="!category.editing"
                            placeholder="Category Name"
                        />
                    </div>
                    <div class="flex items-center gap-1">
                        <Button
                            v-if="!category.editing"
                            variant="ghost"
                            size="icon"
                            @click="category.editing = true"
                            :disabled="category.isSaving || category.isDeleting"
                            class="text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                        >
                            <Edit3 class="h-4 w-4" />
                        </Button>
                        <template v-else>
                            <Button
                                variant="ghost"
                                size="icon"
                                @click="handleSaveCategory(category)"
                                :disabled="category.isSaving || category.isDeleting"
                                class="text-green-600 hover:bg-green-100 hover:text-green-800"
                            >
                                <Loader2 v-if="category.isSaving" class="h-4 w-4 animate-spin" />
                                <Check v-else class="h-4 w-4" />
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon"
                                @click="cancelEdit(category)"
                                :disabled="category.isSaving || category.isDeleting"
                                class="text-red-600 hover:bg-red-100 hover:text-red-800"
                            >
                                <X class="h-4 w-4" />
                            </Button>
                        </template>
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="deleteCategory(category.id)"
                            :disabled="category.isNew || category.isSaving || category.isDeleting"
                            class="text-red-600 hover:bg-red-100 hover:text-red-800"
                        >
                            <Loader2 v-if="category.isDeleting" class="h-4 w-4 animate-spin" />
                            <Trash2 v-else class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <Button
                    @click="addNewCategoryField"
                    variant="outline"
                    class="mt-4 flex w-full items-center justify-center gap-2 border-dashed border-slate-300 text-slate-600 hover:border-red-400 hover:text-red-600"
                >
                    <Plus class="h-4 w-4" />
                    Add New Category
                </Button>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="showDialog = false">Close</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
