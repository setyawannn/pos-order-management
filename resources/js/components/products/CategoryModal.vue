<!-- components/products/CategoryModal.vue -->
<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black p-4"
            @click.self="$emit('update:show', false)"
        >
            <div class="max-h-[80vh] w-full max-w-2xl overflow-hidden rounded-xl bg-white shadow-xl">
                <div class="border-b border-slate-200 p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Manage Categories</h3>
                        <button @click="$emit('update:show', false)" class="rounded-lg p-2 transition-colors hover:bg-slate-100">
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <div class="max-h-96 overflow-y-auto p-6">
                    <div class="space-y-3">
                        <div
                            v-for="category in localCategories"
                            :key="category.id"
                            class="flex items-center justify-between rounded-lg border border-slate-200 p-3"
                        >
                            <div class="flex-1">
                                <input
                                    v-model="category.name"
                                    class="w-full border-0 px-3 py-2 font-medium text-slate-900 focus:ring-0"
                                    :class="{ 'bg-slate-50': !category.editing }"
                                    :readonly="!category.editing"
                                />
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    v-if="!category.editing"
                                    @click="category.editing = true"
                                    class="rounded-lg p-2 transition-colors hover:bg-slate-100"
                                >
                                    <Edit3 class="h-4 w-4" />
                                </button>
                                <template v-else>
                                    <button
                                        @click="saveCategory(category)"
                                        class="rounded-lg p-2 text-green-600 transition-colors hover:bg-green-100"
                                    >
                                        <Check class="h-4 w-4" />
                                    </button>
                                    <button @click="cancelEdit(category)" class="rounded-lg p-2 text-red-600 transition-colors hover:bg-red-100">
                                        <X class="h-4 w-4" />
                                    </button>
                                </template>
                                <button @click="deleteCategory(category.id)" class="rounded-lg p-2 text-red-600 transition-colors hover:bg-red-100">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <button
                        @click="addNewCategory"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-slate-300 p-3 text-slate-600 transition-colors hover:border-red-400 hover:text-red-600"
                    >
                        <Plus class="h-4 w-4" />
                        Add New Category
                    </button>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 p-6">
                    <button
                        @click="$emit('update:show', false)"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-slate-700 transition-colors hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button @click="saveChanges" class="rounded-lg bg-red-500 px-4 py-2 text-white transition-colors hover:bg-red-600">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { Check, Edit3, Plus, Trash2, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    editing?: boolean;
    originalName?: string;
}

const props = defineProps<{
    show: boolean;
    categories: Category[];
}>();

const emit = defineEmits<{
    'update:show': [value: boolean];
    update: [categories: Category[]];
}>();

const localCategories = ref<Category[]>([]);

watch(
    () => props.categories,
    (newCategories) => {
        localCategories.value = newCategories.map((cat) => ({ ...cat, editing: false }));
    },
    { immediate: true },
);

const addNewCategory = () => {
    const newId = Math.max(...localCategories.value.map((c) => c.id)) + 1;
    localCategories.value.push({
        id: newId,
        name: 'New Category',
        slug: 'new-category',
        editing: true,
        originalName: 'New Category',
    });
};

const saveCategory = (category: Category) => {
    category.editing = false;
    category.slug = category.name.toLowerCase().replace(/\s+/g, '-');
};

const cancelEdit = (category: Category) => {
    if (category.originalName) {
        category.name = category.originalName;
    }
    category.editing = false;
};

const deleteCategory = (categoryId: number) => {
    localCategories.value = localCategories.value.filter((c) => c.id !== categoryId);
};

const saveChanges = () => {
    emit('update', localCategories.value);
    emit('update:show', false);
};
</script>
