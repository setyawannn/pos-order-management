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
    id: number;
    name: string;
    price: number;
    quantity: number;
    image?: string;
    notes?: string;
}

export interface OrderData {
    customer_name: string;
    customer_email?: string;
    customer_phone?: string;
    order_type: 'dine_in' | 'take_away';
    table_number?: string;
    items: {
        product_id: number;
        quantity: number;
        price: number;
        notes?: string;
    }[];
    total_amount: number;
    notes?: string;
}
