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

export interface Order {
    id: number;
    order_code: string;
    table_number: string | null; // Allow null for takeaway
    customer_name: string;
    customer_email: string | null; // Allow null
    customer_phone: string | null; // Allow null
    order_type: 'dine_in' | 'take_away';
    total_amount: number;
    status: 'waiting_payment' | 'payment_failed' | 'in_queue' | 'in_progress' | 'ready_to_serve' | 'completed' | 'cancelled';
    payment_method: string | null;
    payment_status: string; // Assuming for now
    transaction_id: string | null;
    payment_payload: any | null;
    notes: string | null;
    sequence: number | null;
    created_at: string;
    updated_at: string;
    items: OrderItem[];
    // Dynamic properties added by backend for kitchen dashboard
    time_since_creation?: string; // Optional because it's added dynamically
    human_created_at?: string; // Optional
    disappearing_at?: string; // Optional, for ready_to_serve orders
}

// OrderItem model structure received from backend
export interface OrderItem {
    id: number;
    order_id: number;
    product_id: number;
    quantity: number;
    price: number;
    subtotal: number;
    notes: string | null; // Allow null
    is_done: boolean;
    created_at: string;
    updated_at: string;
    product: Product;
}

export interface StoredOrderItem {
    id: number; // This is the OrderItem ID
    product_id: number; // The actual Product ID
    product_name: string;
    image?: string | null; // Image URL for displaying in cart/history
    quantity: number;
    price: number;
    subtotal: number;
    notes?: string | null;
    is_done: boolean;
}

// Corrected StoredOrder for localStorage
export interface StoredOrder {
    order_code: string;
    customer_name: string;
    total_amount: number;
    status: string;
    order_type: string;
    table_number?: string | null;
    created_at: string;
    items: StoredOrderItem[];
}
