<!-- resources/js/pages/admin/kitchen/components/OrderColumn.vue -->
<template>
    <div class="flex h-full w-full flex-col overflow-hidden rounded-lg border-t-4 bg-white p-4 shadow-md" :class="borderClass">
        <h2 class="mb-4 border-b border-gray-200 pb-2 text-xl font-semibold text-gray-800">{{ title }} ({{ orders.length }})</h2>

        <!-- This div is the key to vertical scrolling for cards within the column -->
        <div class="scrollbar-hide -mr-2 flex-1 space-y-4 overflow-y-auto pr-2">
            <KitchenOrderCard
                v-for="order in orders"
                :key="order.id"
                :order="order"
                :item-status-labels="itemStatusLabels"
                :overall-status-labels="overallStatusLabels"
                @toggle-item-done="(item) => $emit('toggle-item-done', item)"
                @update-order-status="(orderId, newStatus) => $emit('update-order-status', orderId, newStatus)"
            />
            <p v-if="orders.length === 0" class="py-8 text-center text-gray-500">No {{ title.toLowerCase() }} orders.</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { Order } from '@/types'; // No need for OrderItem here
import { computed } from 'vue';
import KitchenOrderCard from './KitchenOrderCard.vue';

interface Props {
    title: string;
    statusKey: string;
    orders: Order[];
    borderClass: string;
    itemStatusLabels: Record<string, string>;
    overallStatusLabels?: Record<string, string>;
}

const props = defineProps<Props>();
defineEmits(['toggle-item-done', 'update-order-status']);

const overallStatusLabels = computed(() => {
    return {
        in_queue: 'In Queue',
        in_progress: 'In Progress',
        ready_to_serve: 'Ready to Serve',
        completed: 'Completed',
        cancelled: 'Cancelled',
        waiting_payment: 'Waiting Payment',
        payment_failed: 'Payment Failed',
    };
});
</script>

<style scoped>
/* Custom scrollbar styles for the column's scrollable area */
.scrollbar-hide {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}
.scrollbar-hide::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}
</style>
