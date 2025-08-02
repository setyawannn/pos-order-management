// resources/js/stores/orderHistoryStore.ts
import { orderStorage, type StoredOrder } from '@/services/orderStorage';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { useToast } from 'vue-toastification';

export const useOrderHistoryStore = defineStore('orderHistory', () => {
    const toast = useToast();
    const orders = ref<StoredOrder[]>([]);
    const isLoading = ref(false);

    // Getters
    const recentOrders = computed(() => orders.value.slice(0, 5));
    const hasOrders = computed(() => orders.value.length > 0);

    // Actions
    const loadOrders = () => {
        orders.value = orderStorage.getOrders();
    };

    const addOrder = (order: any) => {
        orderStorage.saveOrder(order);
        loadOrders(); // Refresh the list
    };

    const getOrderByCode = (orderCode: string): StoredOrder | null => {
        return orderStorage.getOrderByCode(orderCode);
    };

    const clearAllOrders = () => {
        orderStorage.clearOrders();
        orders.value = [];
    };

    // Fetch latest status and full details from server
    const refreshOrderDetails = async (orderCode: string) => {
        isLoading.value = true;
        try {
            // Use the API route for getting order status/details
            const response = await fetch(route('api.orders.status', { orderCode: orderCode }));
            const result = await response.json();

            if (response.ok && result.success) {
                // Update the stored order with the full new data
                // Ensure the data structure matches StoredOrder interface
                const updatedOrderData = {
                    order_code: result.data.order.order_code,
                    customer_name: result.data.order.customer_name,
                    total_amount: parseFloat(result.data.order.total_amount),
                    status: result.data.order.status,
                    order_type: result.data.order.order_type,
                    table_number: result.data.order.table_number,
                    created_at: result.data.order.created_at,
                    items: result.data.order.items.map((item: any) => ({
                        id: item.id, // OrderItem ID
                        product_name: item.product.name,
                        quantity: item.quantity,
                        price: parseFloat(item.price),
                        subtotal: parseFloat(item.subtotal),
                        notes: item.notes,
                        is_done: item.is_done, // Ensure is_done is captured
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
            isLoading.value = false;
        }
        return null;
    };

    // Initialize
    loadOrders(); // Load existing orders from localStorage when the store is created

    return {
        // State
        orders,
        isLoading,

        // Getters
        recentOrders,
        hasOrders,

        // Actions
        loadOrders,
        addOrder,
        getOrderByCode,
        clearAllOrders,
        refreshOrderDetails,
    };
});
