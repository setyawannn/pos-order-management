// resources/js/stores/orderHistoryStore.ts
import { orderStorage, type StoredOrder } from '@/services/orderStorage';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { useToast } from 'vue-toastification';

export const useOrderHistoryStore = defineStore('orderHistory', () => {
    const toast = useToast();
    const orders = ref<StoredOrder[]>([]);
    // Changed isLoading from boolean to a Set to track multiple loading order codes
    const loadingOrders = ref<Set<string>>(new Set());

    // Getters
    const recentOrders = computed(() => orders.value.slice(0, 5));
    const hasOrders = computed(() => orders.value.length > 0);

    // New getter to check if a specific order is loading
    const isOrderLoading = computed(() => (orderCode: string) => loadingOrders.value.has(orderCode));

    // Actions
    const loadOrders = () => {
        orders.value = orderStorage.getOrders();
    };

    const getOrderByCode = (orderCode: string): StoredOrder | null => {
        return orderStorage.getOrderByCode(orderCode);
    };

    const clearAllOrders = () => {
        orderStorage.clearOrders();
        orders.value = [];
        toast.info('Order history cleared');
    };

    // Fetch latest status and full details from server
    const refreshOrderDetails = async (orderCode: string) => {
        // Add the orderCode to the loading set
        loadingOrders.value.add(orderCode);

        try {
            const response = await fetch(route('api.orders.status', { orderCode: orderCode }));
            const result = await response.json();

            if (response.ok && result.success) {
                const updatedOrderData = {
                    order_code: result.data.order.order_code,
                    customer_name: result.data.order.customer_name,
                    total_amount: parseFloat(result.data.order.total_amount),
                    status: result.data.order.status,
                    order_type: result.data.order.order_type,
                    table_number: result.data.order.table_number,
                    created_at: result.data.order.created_at,
                    items: result.data.order.items.map((item: any) => ({
                        id: item.id,
                        product_name: item.product?.name || 'Unknown Product',
                        quantity: item.quantity,
                        price: parseFloat(item.price),
                        subtotal: parseFloat(item.subtotal),
                        notes: item.notes,
                        is_done: !!item.is_done,
                    })),
                };

                orderStorage.updateOrder(updatedOrderData);
                loadOrders(); // Reload orders into the store to reflect changes
                toast.success(`Order ${orderCode} status refreshed!`);
                return updatedOrderData;
            } else {
                const errorMessage = result.message || 'Failed to fetch order details.';
                toast.error(errorMessage);
                console.error('Failed to refresh order details:', result);
            }
        } catch (error) {
            toast.error('Network error while refreshing order status.');
            console.error('Network error while refreshing order status:', error);
        } finally {
            // Remove the orderCode from the loading set when done (success or error)
            loadingOrders.value.delete(orderCode);
        }
        return null;
    };

    // Initialize: Load orders when the store is first created
    loadOrders();

    return {
        // State
        orders,
        // isLoading is now managed via loadingOrders Set

        // Getters
        recentOrders,
        hasOrders,
        isOrderLoading, // Expose the new getter

        // Actions
        loadOrders,
        getOrderByCode,
        clearAllOrders,
        refreshOrderDetails,
    };
});
