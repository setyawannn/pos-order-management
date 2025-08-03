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
            <!-- Scrollable container for items within a card -->
            <div class="scrollbar-hide -mr-2 max-h-48 space-y-3 overflow-y-auto pr-2">
                <div v-for="item in order.items" :key="item.id" class="flex items-start justify-between">
                    <div class="flex flex-1 items-center pr-2">
                        <!-- Checklist Icon next to item name, always visible if done -->
                        <CheckCircle v-if="item.is_done" class="mr-1.5 h-4 w-4 text-green-500" />
                        <p class="text-sm leading-tight font-medium text-gray-900">{{ item.quantity }}x {{ item.product.name }}</p>
                    </div>
                    <div class="ml-2 flex-shrink-0">
                        <!-- Toggle Button: Only clickable/visible as a button if order.status is 'in_progress' -->
                        <button
                            v-if="order.status === 'in_progress'"
                            @click="$emit('toggle-item-done', item)"
                            :class="[
                                'flex items-center justify-center rounded-md px-2.5 py-1.5 text-xs font-semibold whitespace-nowrap transition-colors',
                                item.is_done ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200',
                            ]"
                        >
                            <!-- Icon inside button depends on is_done status -->
                            <component :is="item.is_done ? CheckCircle : CircleX" class="mr-1 h-3.5 w-3.5" />
                            <span>{{ itemStatusLabels[item.is_done.toString()] }}</span>
                        </button>
                        <!-- Non-clickable Status Display: For other order statuses (in_queue, ready_to_serve, etc.) -->
                        <span
                            v-else
                            :class="[
                                'flex items-center justify-center rounded-md px-2.5 py-1.5 text-xs font-semibold whitespace-nowrap',
                                item.is_done ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600',
                            ]"
                        >
                            <!-- Icon inside span depends on is_done status -->
                            <component :is="item.is_done ? CheckCircle : CircleX" class="mr-1 h-3.5 w-3.5" />
                            <span>{{ itemStatusLabels[item.is_done.toString()] }}</span>
                        </span>
                    </div>
                </div>
            </div>
            <p v-if="order.notes" class="mt-3 border-t border-gray-100 pt-3 text-xs text-gray-600 italic">Order Notes: {{ order.notes }}</p>
        </div>

        <!-- Card Footer - Status Update Buttons -->
        <div class="flex flex-col space-y-2 p-4">
            <!-- Start Cooking Button for in_queue orders -->
            <template v-if="order.status === 'in_queue'">
                <button
                    @click="$emit('update-order-status', order.id, 'in_progress')"
                    class="w-full rounded-lg bg-blue-500 px-3 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-600 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Start Cooking
                </button>
            </template>

            <!-- Ready to Serve Button for in_progress orders -->
            <template v-else-if="order.status === 'in_progress'">
                <button
                    @click="$emit('update-order-status', order.id, 'ready_to_serve')"
                    :disabled="!allOrderItemsDone"
                    class="w-full rounded-lg bg-green-500 px-3 py-2.5 text-sm font-medium text-white transition-colors hover:bg-green-600 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Ready to Serve ({{ doneItemsCount }}/{{ totalItemsCount }})
                </button>
            </template>

            <!-- Ready to Serve - NO BUTTONS HERE, just display it is ready -->
            <!-- We deliberately DO NOT include a template for order.status === 'ready_to_serve' here
                 if we don't want any buttons for that state. The space will collapse. -->

            <!-- Cancel Order Button (visible for all except completed/cancelled/ready_to_serve) -->
            <button
                v-if="order.status !== 'completed' && order.status !== 'cancelled' && order.status !== 'ready_to_serve'"
                @click="$emit('update-order-status', order.id, 'cancelled')"
                class="w-full rounded-lg bg-red-50 px-3 py-2.5 text-sm font-medium text-red-500 transition-colors hover:bg-red-100"
            >
                Cancel Order
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { Order } from '@/types';
import { CheckCircle, CircleX } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    order: Order;
    itemStatusLabels: Record<string, string>;
    overallStatusLabels: Record<string, string>;
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

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
