<!-- resources/js/pages/user/home/components/OrderHistoryDrawer.vue -->
<template>
    <Teleport to="body">
        <!-- Overlay -->
        <div v-if="open" class="bg-opacity-50 fixed inset-0 z-50 bg-black transition-opacity" @click="closeDrawer" />

        <!-- Drawer -->
        <div
            :class="[
                'fixed right-0 bottom-0 left-0 z-50 flex transform flex-col rounded-t-2xl bg-white transition-transform duration-300',
                open ? 'translate-y-0' : 'translate-y-full',
            ]"
            style="max-height: 80vh"
        >
            <!-- Handle -->
            <div class="flex flex-shrink-0 justify-center py-3">
                <div class="h-1 w-12 rounded-full bg-gray-300"></div>
            </div>

            <!-- Header -->
            <div class="flex-shrink-0 border-b border-gray-200 px-4 pb-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Order History</h2>
                    <button @click="closeDrawer" class="rounded-lg p-2 transition-colors hover:bg-gray-100">
                        <X class="h-5 w-5 text-gray-500" />
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto">
                <div class="px-4 py-4">
                    <!-- Empty State -->
                    <div v-if="!orderHistoryStore.hasOrders" class="py-8 text-center">
                        <ShoppingBag class="mx-auto mb-3 h-12 w-12 text-gray-300" />
                        <p class="mb-2 text-gray-500">No orders yet</p>
                        <p class="text-sm text-gray-400">Your order history will appear here</p>
                    </div>

                    <!-- Orders List -->
                    <div v-else class="space-y-4">
                        <div
                            v-for="order in orderHistoryStore.orders"
                            :key="order.order_code"
                            class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                        >
                            <!-- Order Header -->
                            <div class="mb-3 flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">{{ order.order_code }}</h3>
                                    <p class="text-xs text-gray-500">{{ formatDate(order.created_at) }}</p>
                                </div>
                                <div class="text-right">
                                    <PriceDisplay :amount="order.total_amount" class="text-sm font-semibold text-red-500" />
                                    <p class="text-xs text-gray-500 capitalize">{{ order.order_type.replace('_', ' ') }}</p>
                                </div>
                            </div>

                            <!-- Overall Status -->
                            <div class="mb-3 flex items-center justify-between">
                                <span :class="getOverallStatusClass(order.status)" class="rounded-full px-2 py-1 text-xs font-medium">
                                    {{ getOverallStatusLabel(order.status) }}
                                </span>
                                <button
                                    @click="refreshOrderDetails(order.order_code)"
                                    :disabled="orderHistoryStore.isOrderLoading(order.order_code)"
                                    class="text-xs text-blue-600 hover:text-blue-800 disabled:opacity-50"
                                >
                                    <RefreshCw
                                        :class="['mr-1 inline h-3 w-3', orderHistoryStore.isOrderLoading(order.order_code) ? 'animate-spin' : '']"
                                    />
                                    Refresh
                                </button>
                            </div>

                            <!-- Items Summary -->
                            <div class="mb-3">
                                <p class="mb-1 text-xs text-gray-600">Items:</p>
                                <div class="space-y-1">
                                    <div v-for="item in order.items.slice(0, 2)" :key="item.id" class="flex justify-between text-xs">
                                        <span class="text-gray-700"
                                            >{{ item.quantity }}x {{ item.product_name }}
                                            <span :class="getItemStatusClass(item.is_done)" class="ml-1 rounded-full px-1 py-0.5 text-xs font-medium">
                                                {{ getItemStatusLabel(item.is_done) }}
                                            </span>
                                        </span>
                                        <PriceDisplay :amount="item.subtotal" class="text-gray-600" />
                                    </div>
                                    <p v-if="order.items.length > 2" class="text-xs text-gray-500">+{{ order.items.length - 2 }} more items</p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <button
                                    @click="viewOrderDetails(order.order_code)"
                                    class="flex-1 rounded-lg bg-red-500 px-3 py-2 text-xs font-medium text-white transition-colors hover:bg-red-600 active:scale-95"
                                >
                                    View Details
                                </button>
                                <button
                                    v-if="canReorder(order.status)"
                                    @click="reorderItems(order)"
                                    class="flex-1 rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 transition-colors hover:bg-gray-200 active:scale-95"
                                >
                                    Reorder
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div v-if="orderHistoryStore.hasOrders" class="flex-shrink-0 border-t border-gray-200 px-4 py-3">
                <button
                    @click="showClearAllModal = true"
                    class="w-full rounded-lg py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 active:scale-95"
                >
                    Clear All Orders
                </button>
            </div>
        </div>

        <ConfirmationModal
            :show="showClearAllModal"
            title="Clear Order History?"
            message="Are you sure you want to clear all your past orders? This action cannot be undone."
            confirmText="Clear History"
            cancelText="Cancel"
            confirmVariant="destructive"
            icon="AlertTriangle"
            iconClass="text-orange-500"
            confirmIcon="History"
            @confirm="confirmClearAllOrders"
            @cancel="showClearAllModal = false"
        />
    </Teleport>
</template>

<script setup lang="ts">
import ConfirmationModal from '@/components/reusable/ConfirmationModal.vue'; // Import your modal
import PriceDisplay from '@/components/reusable/PriceDisplay.vue';
import type { StoredOrderInternal } from '@/services/orderStorage';
import { useCartStore } from '@/stores/cartStore';
import { useOrderHistoryStore } from '@/stores/orderHistoryStore';
import { router } from '@inertiajs/vue3';
import { RefreshCw, ShoppingBag, X } from 'lucide-vue-next'; // Import History icon
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

interface Props {
    open: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const toast = useToast();
const orderHistoryStore = useOrderHistoryStore();
const cartStore = useCartStore();

const showClearAllModal = ref(false); // Reactive state for modal visibility

const closeDrawer = () => {
    emit('update:open', false);
    document.body.style.overflow = '';
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getOverallStatusLabel = (status: string) => {
    const labels = {
        waiting_payment: 'Waiting Payment',
        payment_failed: 'Payment Failed',
        in_queue: 'In Queue',
        in_progress: 'In Progress',
        ready_to_serve: 'Ready to Serve',
        completed: 'Completed',
        cancelled: 'Cancelled',
    };
    return labels[status as keyof typeof labels] || status;
};

const getItemStatusLabel = (isDone: boolean) => {
    return isDone ? 'Done' : 'In Progress';
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
    return isDone ? 'bg-green-50 text-green-700' : 'bg-blue-50 text-blue-700';
};

const canReorder = (status: string) => {
    return ['completed', 'cancelled'].includes(status);
};

const refreshOrderDetails = async (orderCode: string) => {
    await orderHistoryStore.refreshOrderDetails(orderCode);
};

const viewOrderDetails = (orderCode: string) => {
    router.visit(route('user.order.show', orderCode));
    closeDrawer();
};

const reorderItems = (order: StoredOrderInternal) => {
    cartStore.clearCart();
    order.items.forEach((item) => {
        cartStore.addItem({
            id: item.id,
            name: item.product_name,
            image: item.image || '',
            price: item.price,
        });
    });

    toast.success(`${order.items.length} items from order ${order.order_code} added to cart`);
    setTimeout(() => {
        closeDrawer();
        cartStore.openDrawer();
    }, 500);
};

const confirmClearAllOrders = () => {
    showClearAllModal.value = false;
    orderHistoryStore.clearAllOrders();
};
</script>
