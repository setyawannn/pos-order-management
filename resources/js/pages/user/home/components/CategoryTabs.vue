<!-- resources/js/pages/user/home/components/CategoryTabs.vue -->
<template>
    <div class="scrollbar-hide flex space-x-2 overflow-x-auto pb-2">
        <button
            @click="$emit('update:modelValue', 'all')"
            :class="[
                'rounded-lg px-4 py-2 text-sm font-medium whitespace-nowrap transition-colors',
                modelValue === 'all' ? 'bg-red-500 text-white' : 'border border-gray-200 bg-white text-gray-600',
            ]"
        >
            All
        </button>
        <button
            v-for="category in categories"
            :key="category.id"
            @click="$emit('update:modelValue', category.id)"
            :class="[
                'rounded-lg px-4 py-2 text-sm font-medium whitespace-nowrap transition-colors',
                modelValue === category.id ? 'bg-red-500 text-white' : 'border border-gray-200 bg-white text-gray-600',
            ]"
        >
            {{ category.name }}
        </button>
    </div>
</template>

<script setup lang="ts">
import type { Category } from '@/types';

interface Props {
    modelValue: number | 'all';
    categories: Category[];
}

defineProps<Props>();
defineEmits<{
    'update:modelValue': [value: number | 'all'];
}>();
</script>

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
