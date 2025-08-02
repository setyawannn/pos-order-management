<!-- resources/js/pages/user/order/Success.vue -->
<template>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="mx-auto max-w-md px-4">
            <!-- Success Icon -->
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-green-100">
                    <CheckCircle class="h-12 w-12 text-green-500" />
                </div>
                <h1 class="mb-2 text-2xl font-bold text-gray-900">Order Placed Successfully!</h1>
                <p class="text-gray-600">Thank you for your order. We'll prepare it shortly.</p>
            </div>

            <!-- Order Details Card -->
            <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-6 text-center">
                    <h2 class="mb-1 text-lg font-semibold text-gray-900">Order Code</h2>
                    <p class="text-2xl font-bold text-red-500">{{ order.order_code }}</p>
                    <p class="mt-1 text-sm text-gray-500">Please save this code for tracking</p>
                </div>

                <!-- Customer Info -->
                <div class="mb-4 border-t border-gray-200 pt-4">
                    <h3 class="mb-3 font-semibold text-gray-900">Customer Information</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Name:</span>
                            <span class="font-medium">{{ order.customer_name }}</span>
                        </div>
                        <div v-if="order.customer_phone" class="flex justify-between">
                            <span class="text-gray-600">Phone:</span>
                            <span class="font-medium">{{ order.customer_phone }}</span>
                        </div>
                        <div v-if="order.customer_email" class="flex justify-between">
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium">{{ order.customer_email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Order Type:</span>
                            <span class="font-medium capitalize">{{ order.order_type.replace('_', ' ') }}</span>
                        </div>
                        <div v-if="order.table_number" class="flex justify-between">
                            <span class="text-gray-600">Table:</span>
                            <span class="font-medium">{{ order.table_number }}</span>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="mb-4 border-t border-gray-200 pt-4">
                    <h3 class="mb-3 font-semibold text-gray-900">Order Items</h3>
                    <div class="space-y-3">
                        <div v-for="item in order.items" :key="item.id" class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ item.product.name }}</p>
                                <p v-if="item.notes" class="mt-1 text-xs text-gray-500">Note: {{ item.notes }}</p>
                            </div>
                            <div class="ml-4 text-right">
                                <p class="text-sm font-medium">{{ item.quantity }}x</p>
                                <PriceDisplay :amount="item.subtotal" class="text-sm text-gray-600" />
                                <span :class="getItemStatusClass(item.is_done)" class="ml-2 rounded-full px-2 py-1 text-xs font-medium">
                                    {{ getItemStatusLabel(item.is_done) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Notes -->
                <div v-if="order.notes" class="mb-4 border-t border-gray-200 pt-4">
                    <h3 class="mb-2 font-semibold text-gray-900">Order Notes</h3>
                    <p class="text-sm text-gray-600">{{ order.notes }}</p>
                </div>

                <!-- Total -->
                <div class="border-t border-gray-200 pt-4">
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-semibold text-gray-900">Total</span>
                        <PriceDisplay :amount="order.total_amount" class="text-xl font-bold text-red-500" />
                    </div>
                </div>

                <!-- Overall Order Status -->
                <div class="mt-4 rounded-lg bg-blue-50 p-3">
                    <div class="flex items-center">
                        <Clock class="mr-2 h-5 w-5 text-blue-500" />
                        <div>
                            <p class="text-sm font-medium text-blue-900">Overall Status: {{ statusOptions[order.status] }}</p>
                            <p class="text-xs text-blue-700">We'll notify you when your order is ready</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <button
                    @click="$inertia.visit(route('user.order.show', order.order_code))"
                    class="w-full rounded-lg bg-red-500 px-4 py-3 font-medium text-white transition-colors hover:bg-red-600 active:scale-95"
                >
                    Track Order
                </button>

                <button
                    @click="$inertia.visit(route('user.home'))"
                    class="w-full rounded-lg bg-gray-100 px-4 py-3 font-medium text-gray-700 transition-colors hover:bg-gray-200 active:scale-95"
                >
                    Order More Items
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import PriceDisplay from '@/components/reusable/PriceDisplay.vue';
import type { Order } from '@/types';
import { CheckCircle, Clock } from 'lucide-vue-next';

interface Props {
    order: Order;
    statusOptions: Record<string, string>;
}

const props = defineProps<Props>();

const getItemStatusClass = (isDone: boolean) => {
    return isDone ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800';
};

const getItemStatusLabel = (isDone: boolean) => {
    return isDone ? 'Done' : 'In Progress';
};
</script>
