<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import SettingsLayout from '@/layouts/SettingsLayout.vue';

defineProps<{
    product: any;
}>();
</script>

<template>
    <Head title="Edit Product" />
    <SettingsLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold">Edit Product</h1>
            <p class="mt-4">Editing functionality is currently handled via the modal on the products list.</p>
            <Link :href="route('admin.products.index')" class="mt-4 inline-block text-blue-600 hover:underline">
                Back to Products
            </Link>
        </div>
    </SettingsLayout>
</template>
