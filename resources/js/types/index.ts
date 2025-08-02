// resources/js/types/index.ts
export interface Category {
    id: number;
    name: string;
    description?: string;
    created_at: string;
    updated_at: string;
}

export interface Product {
    id: number;
    category_id: number;
    name: string;
    description?: string;
    price: number;
    image?: string;
    stock: number;
    is_stock_managed: boolean;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    category?: Category;
}

export interface CartItem {
    id: number; // This is the Product ID
    name: string;
    price: number;
    quantity: number;
    image?: string;
    notes?: string;
}

// Order data to be sent from frontend
export interface OrderData {
    customer_name: string;
    customer_email?: string;
    customer_phone?: string;
    order_type: 'dine_in' | 'take_away';
    table_number?: string;
    items: {
        product_id: number;
        quantity: number;
        notes?: string;
        // No 'is_done' field here, it's set on backend
    }[];
    notes?: string;
}

// Order model structure received from backend
export interface Order {
    id: number;
    order_code: string;
    customer_name: string;
    customer_email?: string;
    customer_phone?: string;
    order_type: 'dine_in' | 'take_away';
    table_number?: string;
    total_amount: number;
    status: 'waiting_payment' | 'payment_failed' | 'in_queue' | 'in_progress' | 'ready_to_serve' | 'completed' | 'cancelled';
    payment_method?: string;
    payment_status: string; // Assuming for now
    transaction_id?: string;
    payment_payload?: any;
    notes?: string;
    sequence?: number;
    created_at: string;
    updated_at: string;
    items: OrderItem[]; // Now explicitly include OrderItem
}

// OrderItem model structure received from backend
export interface OrderItem {
    id: number;
    order_id: number;
    product_id: number;
    quantity: number;
    price: number;
    subtotal: number;
    notes?: string;
    is_done: boolean; // Changed to boolean 'is_done'
    created_at: string;
    updated_at: string;
    product: Product; // Eager loaded product details
}
