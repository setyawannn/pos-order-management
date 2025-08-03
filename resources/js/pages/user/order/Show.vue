<!-- resources/js/pages/user/order/Show.vue -->
<template>
    <UserLayout>
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="mx-auto max-w-md px-4">
                <!-- Back Button & Header -->
                <div class="mb-6 flex items-center justify-between">
                    <button @click="goBack" class="rounded-lg p-2 text-gray-600 hover:bg-gray-100">
                        <ArrowLeft class="h-5 w-5" />
                    </button>
                    <h1 class="-ml-10 flex-grow text-center text-xl font-bold text-gray-900">Order #{{ order.order_code }}</h1>
                    <button
                        @click="refreshOrder"
                        :disabled="orderHistoryStore.isOrderLoading(order.order_code)"
                        class="rounded-lg p-2 text-blue-600 hover:bg-blue-50 disabled:opacity-50"
                    >
                        <RefreshCw :class="['h-5 w-5', orderHistoryStore.isOrderLoading(order.order_code) ? 'animate-spin' : '']" />
                    </button>
                </div>

                <!-- Overall Order Status Card -->
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm">
                    <h2 class="mb-2 text-lg font-semibold text-gray-900">Current Status</h2>
                    <span :class="getOverallStatusClass(order.status)" class="rounded-full px-4 py-2 text-sm font-bold">
                        {{ statusOptions[order.status] }}
                    </span>
                    <p class="mt-2 text-xs text-gray-500">Last updated: {{ formatDate(order.updated_at) }}</p>
                </div>

                <!-- Customer & Order Type Info -->
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="mb-3 font-semibold text-gray-900">Order Information</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Customer:</span>
                            <span class="font-medium">{{ order.customer_name }}</span>
                        </div>
                        <div v-if="order.table_number" class="flex justify-between">
                            <span class="text-gray-600">Table Number:</span>
                            <span class="font-medium">{{ order.table_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Order Type:</span>
                            <span class="font-medium capitalize">{{ order.order_type.replace('_', ' ') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Order Date:</span>
                            <span class="font-medium">{{ formatFullDate(order.created_at) }}</span>
                        </div>
                        <div v-if="order.notes" class="flex flex-col">
                            <span class="text-gray-600">Order Notes:</span>
                            <p class="mt-1 text-sm font-medium">{{ order.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Ordered Items Section -->
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="mb-4 font-semibold text-gray-900">Ordered Items</h3>
                    <div class="space-y-4">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-start space-x-4 border-b border-gray-100 pb-4 last:border-b-0 last:pb-0"
                        >
                            <img
                                :src="item.product.image || '/images/placeholder.jpg'"
                                :alt="item.product.name"
                                class="h-16 w-16 flex-shrink-0 rounded-lg bg-gray-100 object-cover"
                            />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ item.quantity }}x {{ item.product.name }}</p>
                                <PriceDisplay :amount="item.subtotal" class="text-sm text-gray-600" />
                                <p v-if="item.notes" class="mt-1 text-xs text-gray-500">Note: {{ item.notes }}</p>
                            </div>
                            <div class="flex-shrink-0 text-right">
                                <span :class="getItemStatusClass(item.is_done)" class="rounded-full px-2 py-1 text-xs font-medium whitespace-nowrap">
                                    {{ getItemStatusLabel(item.is_done) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Details & Total -->
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="mb-4 font-semibold text-gray-900">Payment Details</h3>
                    <div class="mb-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Status:</span>
                            <span class="font-medium capitalize">{{ paymentStatusOptions[order.payment_status] || order.payment_status }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Method:</span>
                            <span class="font-medium capitalize">{{ order.payment_method || 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                        <span class="text-lg font-semibold text-gray-900">Grand Total</span>
                        <PriceDisplay :amount="order.total_amount" class="text-2xl font-bold text-red-500" />
                    </div>
                </div>

                <!-- Back to Home -->
                <button
                    @click="$inertia.visit(route('user.home'))"
                    class="mt-4 w-full rounded-lg bg-red-500 px-4 py-3 font-medium text-white transition-colors hover:bg-red-600 active:scale-95"
                >
                    Back to Home
                </button>
            </div>
        </div>
    </UserLayout>
</template>

<script setup lang="ts">
import PriceDisplay from '@/components/reusable/PriceDisplay.vue';
import UserLayout from '@/layouts/UserLayout.vue';
import { useOrderHistoryStore } from '@/stores/orderHistoryStore';
import type { Order } from '@/types';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, RefreshCw } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    order: Order;
    statusOptions: Record<string, string>; // Overall order status labels
    paymentStatusOptions: Record<string, string>;
}

const props = defineProps<Props>();
const orderHistoryStore = useOrderHistoryStore();

// Dynamically use the order object from props
const currentOrder = computed<Order>(() => props.order);

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('user.home'));
    }
};

const refreshOrder = async () => {
    // Pass the current order's code to refresh only that order
    await orderHistoryStore.refreshOrderDetails(currentOrder.value.order_code);

    // After refresh, the Inertia page needs to be re-rendered with the new props
    // The simplest way to do this is to visit the same route again,
    // forcing Inertia to fetch fresh props.
    router.visit(route('user.order.show', currentOrder.value.order_code), {
        preserveScroll: true,
        preserveState: false, // Force fetch fresh props
        onFinish: () => {
            // Toast handled by the store method
        },
    });
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatFullDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getOverallStatusClass = (status: string) => {
    const classes = {
        waiting_payment: 'bg-yellow-100 text-yellow-800',
        payment_failed: 'bg-red-100 text-red-800',
        in_queue: 'bg-blue-100 text-blue-800',
        in_progress: 'bg-orange-100 text-orange-800',
        ready_to_serve: 'bg-green-100 text-green-800',
        completed: 'bg-gray-100 text-gray-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const getItemStatusClass = (isDone: boolean) => {
    return isDone ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800';
};

const getItemStatusLabel = (isDone: boolean) => {
    return isDone ? 'Done' : 'In Progress';
};
</script>
