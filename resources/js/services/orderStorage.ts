// resources/js/services/orderStorage.ts

export interface StoredOrder {
    order_code: string;
    customer_name: string;
    total_amount: number;
    status: string; // Overall order status
    order_type: string;
    table_number?: string;
    created_at: string;
    items: Array<{
        id: number; // OrderItem ID
        product_name: string;
        quantity: number;
        price: number; // Stored price
        subtotal: number;
        notes?: string;
        is_done: boolean; // Use boolean 'is_done'
    }>;
}

class OrderStorageService {
    private readonly STORAGE_KEY = 'user_orders';
    private readonly MAX_ORDERS = 10;

    saveOrder(order: any): void {
        try {
            const storedOrder: StoredOrder = {
                order_code: order.order_code,
                customer_name: order.customer_name,
                total_amount: parseFloat(order.total_amount),
                status: order.status,
                order_type: order.order_type,
                table_number: order.table_number,
                created_at: order.created_at,
                items: order.items.map((item: any) => ({
                    id: item.id,
                    product_name: item.product.name,
                    quantity: item.quantity,
                    price: parseFloat(item.price),
                    subtotal: parseFloat(item.subtotal),
                    notes: item.notes,
                    is_done: item.is_done, // Use 'is_done' from backend
                })),
            };

            const existingOrders = this.getOrders();
            const filteredOrders = existingOrders.filter((o) => o.order_code !== order.order_code);
            const updatedOrders = [storedOrder, ...filteredOrders].slice(0, this.MAX_ORDERS);

            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(updatedOrders));
        } catch (error) {
            console.error('Failed to save order to localStorage:', error);
        }
    }

    getOrders(): StoredOrder[] {
        try {
            const stored = localStorage.getItem(this.STORAGE_KEY);
            return stored ? JSON.parse(stored) : [];
        } catch (error) {
            console.error('Failed to get orders from localStorage:', error);
            return [];
        }
    }

    getOrderByCode(orderCode: string): StoredOrder | null {
        const orders = this.getOrders();
        return orders.find((order) => order.order_code === orderCode) || null;
    }

    updateOrder(updatedOrder: StoredOrder): void {
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

    getRecentOrders(): StoredOrder[] {
        return this.getOrders().slice(0, 5);
    }
}

export const orderStorage = new OrderStorageService();
