<!-- resources/js/pages/admin/kitchen/components/OrderColumn.vue -->
<template>
    <div class="flex h-full w-full flex-col overflow-hidden rounded-lg border-t-4 bg-white p-4 shadow-md" :class="borderClass">
        <h2 class="mb-4 border-b border-gray-200 pb-2 text-xl font-semibold text-gray-800">{{ title }} ({{ orders.length }})</h2>

        <div class="-mr-2 flex-1 space-y-4 overflow-y-auto pr-2">
            <!-- Added pr-2 -mr-2 for custom scrollbar spacing -->
            <KitchenOrderCard
                v-for="order in orders"
                :key="order.id"
                :order="order"
                :item-status-labels="itemStatusLabels"
                :overall-status-labels="overallStatusLabels"
                @toggle-item-done="(item) => $emit('toggle-item-done', item)"
                @update-order-status="(newStatus) => $emit('update-order-status', order, newStatus)"
            />
            <p v-if="orders.length === 0" class="py-8 text-center text-gray-500">No {{ title.toLowerCase() }} orders.</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { Order } from '@/types';
import { computed } from 'vue';
import KitchenOrderCard from './KitchenOrderCard.vue';

interface Props {
    title: string;
    statusKey: string; // e.g., 'in_progress'
    orders: Order[];
    borderClass: string; // Tailwind class for border color, e.g., 'border-blue-500'
    itemStatusLabels: { pending: string; done: string };
}

const props = defineProps<Props>();
defineEmits(['toggle-item-done', 'update-order-status']);

// Provide overall status labels if needed for cards
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
/* Custom scrollbar styles for better aesthetics */
div::-webkit-scrollbar {
    width: 8px;
}

div::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

div::-webkit-scrollbar-thumb {
    background: #cbd5e1; /* slate-300 */
    border-radius: 10px;
}

div::-webkit-scrollbar-thumb:hover {
    background: #a0aec0; /* slate-400 */
}
</style>
