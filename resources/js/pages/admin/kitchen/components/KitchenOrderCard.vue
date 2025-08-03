<!-- resources/js/pages/admin/kitchen/components/KitchenOrderCard.vue -->
<template>
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <!-- Card Header -->
        <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
            <div class="mb-1 flex items-center justify-between text-sm font-medium text-gray-700">
                <span
                    >Order Code: <span class="font-semibold text-gray-900">{{ order.order_code }}</span></span
                >
                <span
                    >Table: <span class="font-semibold text-gray-900">{{ order.table_number || 'Take Away' }}</span></span
                >
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>{{ order.time_since_creation }}</span>
                <span>{{ order.human_created_at }}</span>
            </div>
        </div>

        <!-- Card Body - Order Items (ToDo List) -->
        <div class="border-b border-gray-200 p-4">
            <h4 class="mb-3 text-sm font-semibold text-gray-800">Items:</h4>
            <div class="space-y-3">
                <div v-for="item in order.items" :key="item.id" class="flex items-start justify-between">
                    <div class="flex-1 pr-2">
                        <p class="text-sm font-medium text-gray-900">{{ item.quantity }}x {{ item.product.name }}</p>
                        <p v-if="item.notes" class="text-xs text-gray-600 italic">Note: {{ item.notes }}</p>
                    </div>
                    <div v-if="order.status === 'in_progress' || order.status === 'in_queue'" class="ml-2 flex-shrink-0">
                        <button
                            @click="$emit('toggle-item-done', item)"
                            :class="[
                                'rounded-md px-2 py-1 text-xs font-semibold transition-colors',
                                item.is_done ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-blue-100 text-blue-800 hover:bg-blue-200',
                            ]"
                        >
                            {{ item.is_done ? itemStatusLabels.done : itemStatusLabels.pending }}
                        </button>
                    </div>
                    <div v-else class="ml-2 flex-shrink-0">
                        <span
                            :class="[
                                'rounded-md px-2 py-1 text-xs font-semibold',
                                item.is_done ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600', // Show status but not clickable
                            ]"
                        >
                            {{ item.is_done ? itemStatusLabels.done : itemStatusLabels.pending }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Footer - Status Update Buttons -->
        <div class="flex flex-col space-y-2 p-4">
            <!-- Only show for in_queue and in_progress -->
            <template v-if="order.status === 'in_queue'">
                <button
                    @click="$emit('update-order-status', 'in_progress')"
                    class="w-full rounded-lg bg-blue-500 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-600 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Start Cooking
                </button>
            </template>

            <template v-else-if="order.status === 'in_progress'">
                <button
                    @click="$emit('update-order-status', 'ready_to_serve')"
                    :disabled="!allOrderItemsDone"
                    class="w-full rounded-lg bg-green-500 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-green-600 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Ready to Serve ({{ doneItemsCount }}/{{ totalItemsCount }})
                </button>
            </template>

            <!-- Show 'Mark as Served' for ready_to_serve orders -->
            <template v-else-if="order.status === 'ready_to_serve'">
                <button
                    @click="$emit('update-order-status', 'completed')"
                    class="w-full rounded-lg bg-gray-500 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-gray-600"
                >
                    Mark as Served
                </button>
            </template>

            <button
                v-if="order.status !== 'completed' && order.status !== 'cancelled'"
                @click="$emit('update-order-status', 'cancelled')"
                class="w-full rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-500 transition-colors hover:bg-red-100"
            >
                Cancel Order
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { Order } from '@/types'; // Import OrderItem type
import { computed } from 'vue';

interface Props {
    order: Order;
    itemStatusLabels: { pending: string; done: string };
    overallStatusLabels: Record<string, string>; // Overall status labels for consistency
}

const props = defineProps<Props>();
defineEmits(['toggle-item-done', 'update-order-status']);

const doneItemsCount = computed(() => {
    return props.order.items.filter((item) => item.is_done).length;
});

const totalItemsCount = computed(() => {
    return props.order.items.length;
});

const allOrderItemsDone = computed(() => {
    return doneItemsCount.value === totalItemsCount.value;
});
</script>
