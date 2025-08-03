// resources/js/services/orderStorage.ts

// Re-define StoredOrderItem here to ensure it's correct for localStorage saving
// This needs product_id and image fields for consistency with the store and frontend usage.
export interface StoredOrderItemInternal {
    id: number; // OrderItem ID
    product_id: number; // Actual Product ID
    product_name: string;
    image?: string | null; // Optional product image
    quantity: number;
    price: number;
    subtotal: number;
    notes?: string | null;
    is_done: boolean;
}

// Adjust StoredOrder to use this internal item type
export interface StoredOrderInternal {
    order_code: string;
    customer_name: string;
    total_amount: number;
    status: string; // Overall order status
    order_type: string;
    table_number?: string | null;
    created_at: string;
    items: StoredOrderItemInternal[]; // Use the internal item type
}

class OrderStorageService {
    private readonly STORAGE_KEY = 'user_orders';
    private readonly MAX_ORDERS = 10;

    saveOrder(order: any): void {
        // 'order' here is the Laravel API response object
        try {
            const totalAmount = parseFloat(order.total_amount);
            if (isNaN(totalAmount)) {
                console.error('Invalid total_amount received:', order.total_amount);
                throw new Error('Invalid total_amount for order persistence.');
            }

            const storedOrder: StoredOrderInternal = {
                // Use StoredOrderInternal
                order_code: order.order_code,
                customer_name: order.customer_name,
                total_amount: totalAmount,
                status: order.status,
                order_type: order.order_type,
                table_number: order.table_number || null,
                created_at: order.created_at,
                items: order.items.map((item: any) => ({
                    // 'item' here is Laravel OrderItem from API
                    id: item.id, // OrderItem ID
                    product_id: item.product_id, // <-- Your fix: Get product_id from Laravel's OrderItem response
                    product_name: item.product?.name || 'Unknown Product',
                    image: item.product?.image || null, // <-- Your fix: Get image from Laravel's Product relation
                    quantity: item.quantity,
                    price: parseFloat(item.price),
                    subtotal: parseFloat(item.subtotal),
                    notes: item.notes || null,
                    is_done: !!item.is_done,
                })),
            };

            const existingOrders = this.getOrders();
            const filteredOrders = existingOrders.filter((o) => o.order_code !== order.order_code);
            const updatedOrders = [storedOrder, ...filteredOrders].slice(0, this.MAX_ORDERS);

            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(updatedOrders));
        } catch (error) {
            console.error('Failed to save order to localStorage:', error);
            // Optionally clear corrupted data if save fails due to structural issue
            // localStorage.removeItem(this.STORAGE_KEY);
        }
    }

    getOrders(): StoredOrderInternal[] {
        // Use StoredOrderInternal
        try {
            const stored = localStorage.getItem(this.STORAGE_KEY);
            const parsed = stored ? JSON.parse(stored) : [];
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            console.error('Failed to get orders from localStorage:', error);
            // Clear potentially corrupted data if parsing fails
            localStorage.removeItem(this.STORAGE_KEY);
            return [];
        }
    }

    getOrderByCode(orderCode: string): StoredOrderInternal | null {
        // Use StoredOrderInternal
        const orders = this.getOrders();
        return orders.find((order) => order.order_code === orderCode) || null;
    }

    updateOrder(updatedOrder: StoredOrderInternal): void {
        // Use StoredOrderInternal
        try {
            const orders = this.getOrders();
            const orderIndex = orders.findIndex((o) => o.order_code === updatedOrder.order_code);

            if (orderIndex !== -1) {
                orders[orderIndex] = updatedOrder;
                localStorage.setItem(this.STORAGE_KEY, JSON.stringify(orders));
            } else {
                this.saveOrder(updatedOrder);
            }
        } catch (error) {
            console.error('Failed to update order in localStorage:', error);
        }
    }

    clearOrders(): void {
        try {
            localStorage.removeItem(this.STORAGE_KEY);
        } catch (error) {
            console.error('Failed to clear orders:', error);
        }
    }

    getRecentOrders(): StoredOrderInternal[] {
        // Use StoredOrderInternal
        return this.getOrders().slice(0, 5);
    }
}

export const orderStorage = new OrderStorageService();
