<!-- resources/js/pages/admin/kitchen/Index.vue -->
<template>
    <DashboardLayout>
        <div class="flex h-full flex-col p-6">
            <h1 class="mb-6 text-2xl font-bold text-gray-900">Kitchen Dashboard</h1>

            <!-- Toolbar / Refresh Button -->
            <div class="mb-6 flex justify-end">
                <button
                    @click="fetchOrders"
                    :disabled="isFetchingOrders"
                    class="inline-flex items-center rounded-md border border-transparent bg-blue-500 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none active:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <RefreshCw :class="['mr-2 h-4 w-4', isFetchingOrders ? 'animate-spin' : '']" />
                    Refresh Orders
                </button>
            </div>

            <div class="flex-1 overflow-x-auto overflow-y-hidden pb-4">
                <div
                    class="grid auto-cols-max gap-4"
                    :style="{ gridTemplateColumns: `repeat(${Object.keys(groupedOrders).length}, minmax(320px, 1fr))` }"
                >
                    <!-- In Progress Column (Always first) -->
                    <OrderColumn
                        title="In Progress"
                        status-key="in_progress"
                        :orders="groupedOrders.in_progress"
                        border-class="border-orange-500"
                        :item-status-labels="itemStatusLabels"
                        @toggle-item-done="toggleItemDone"
                        @update-order-status="updateOrderStatus"
                    />

                    <!-- In Queue Column -->
                    <OrderColumn
                        title="In Queue"
                        status-key="in_queue"
                        :orders="groupedOrders.in_queue"
                        border-class="border-blue-500"
                        :item-status-labels="itemStatusLabels"
                        @toggle-item-done="toggleItemDone"
                        @update-order-status="updateOrderStatus"
                    />

                    <!-- Ready to Serve Column -->
                    <OrderColumn
                        title="Ready to Serve"
                        status-key="ready_to_serve"
                        :orders="groupedOrders.ready_to_serve"
                        border-class="border-green-500"
                        :item-status-labels="itemStatusLabels"
                        @toggle-item-done="toggleItemDone"
                        @update-order-status="updateOrderStatus"
                    />
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import type { Order, OrderItem } from '@/types'; // Ensure Order and OrderItem are imported
import { RefreshCw } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import { useToast } from 'vue-toastification';
import OrderColumn from './components/OrderColumn.vue';

// Define types for grouped orders for better type safety
interface GroupedOrders {
    in_progress: Order[];
    in_queue: Order[];
    ready_to_serve: Order[];
}

interface Props {
    initialOrders: GroupedOrders;
    statusOptions: Record<string, string>; // Overall order status labels (not directly used here but useful)
}

const props = defineProps<Props>();
const toast = useToast();

const groupedOrders = ref<GroupedOrders>(props.initialOrders);
const isFetchingOrders = ref(false);

const itemStatusLabels = {
    pending: 'In Progress', // is_done = false
    done: 'Done', // is_done = true
};

let refreshInterval: ReturnType<typeof setInterval> | null = null;
const POLLING_INTERVAL = 15000; // Poll every 15 seconds
const READY_TO_SERVE_DISPLAY_DURATION = 10000; // 10 seconds for "Ready to Serve" orders to disappear

const fetchOrders = async () => {
    isFetchingOrders.value = true;
    try {
        const response = await fetch(route('api.kitchen.orders'));
        const result = await response.json();

        if (response.ok && result.success) {
            const newGroupedOrders: GroupedOrders = {
                in_progress: [],
                in_queue: [],
                ready_to_serve: [],
            };

            // Process incoming orders to add disappearing_at if not present
            Object.keys(result.orders).forEach((statusKey) => {
                (result.orders[statusKey as keyof GroupedOrders] as Order[]).forEach((order) => {
                    if (statusKey === 'ready_to_serve' && !order.disappearing_at) {
                        // If backend didn't set disappearing_at, set it now for client-side timing
                        // This is a fallback, ideally backend would manage this consistently
                        order.disappearing_at = new Date(new Date().getTime() + READY_TO_SERVE_DISPLAY_DURATION).toISOString();
                    }
                    newGroupedOrders[statusKey as keyof GroupedOrders].push(order);
                });
            });

            // Client-side filtering for orders that should disappear
            Object.keys(newGroupedOrders).forEach((statusKey) => {
                if (statusKey === 'ready_to_serve') {
                    newGroupedOrders.ready_to_serve = newGroupedOrders.ready_to_serve.filter((order) => {
                        return order.disappearing_at && new Date() < new Date(order.disappearing_at);
                    });
                }
            });

            groupedOrders.value = newGroupedOrders;
        } else {
            toast.error(result.message || 'Failed to fetch orders.');
        }
    } catch (error) {
        console.error('Error fetching kitchen orders:', error);
        toast.error('Network error while fetching orders.');
    } finally {
        isFetchingOrders.value = false;
    }
};

const toggleItemDone = async (orderItem: OrderItem) => {
    try {
        const response = await fetch(route('api.kitchen.order-items.toggle-done', { orderItem: orderItem.id }), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': (window as any).Laravel.csrfToken, // Ensure CSRF token is sent
            },
        });
        const result = await response.json();

        if (response.ok && result.success) {
            toast.success(result.message);
            await fetchOrders();
        } else {
            toast.error(result.message || 'Failed to update item status.');
        }
    } catch (error) {
        console.error('Error toggling item status:', error);
        toast.error('Network error while updating item status.');
    }
};

const updateOrderStatus = async (order: Order, newStatus: string) => {
    // This function signature is from your working code
    try {
        const response = await fetch(route('api.kitchen.orders.update-status', { order: order.id }), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': (window as any).Laravel.csrfToken, // Ensure CSRF token is sent
            },
            body: JSON.stringify({ status: newStatus }),
        });
        const result = await response.json();

        if (response.ok && result.success) {
            toast.success(result.message);
            // If order becomes 'ready_to_serve', apply disappearing logic on frontend
            if (newStatus === 'ready_to_serve') {
                result.order.disappearing_at = new Date(new Date().getTime() + READY_TO_SERVE_DISPLAY_DURATION).toISOString();
            }
            // Re-fetch all orders to ensure correct sorting and removal
            await fetchOrders();
        } else {
            toast.error(result.message || 'Failed to update order status.');
        }
    } catch (error) {
        console.error('Error updating order status:', error);
        toast.error('Network error while updating order status.');
    }
};

onMounted(() => {
    fetchOrders();
    refreshInterval = setInterval(fetchOrders, POLLING_INTERVAL);
});

onUnmounted(() => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<style scoped>
/* Responsive grid for columns */
.auto-cols-max {
    grid-auto-columns: max-content;
}
@media (min-width: 768px) {
    /* iPad / Tablet breakpoint */
    .grid-cols-auto-fill-minmax-320 {
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    }
}
</style>
