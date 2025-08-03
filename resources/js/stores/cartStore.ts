// resources/js/stores/cartStore.ts
import { orderStorage } from '@/services/orderStorage';
import type { CartItem } from '@/types';
import { router } from '@inertiajs/vue3';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { useToast } from 'vue-toastification';
import { useOrderHistoryStore } from './orderHistoryStore'; // Import order history store

// Declare a global interface for window.Laravel if it's not already defined
declare global {
    interface Window {
        Laravel: {
            csrfToken: string;
        };
    }
}

export const useCartStore = defineStore(
    'cart',
    () => {
        const toast = useToast();

        // State
        const items = ref<CartItem[]>([]);
        const isDrawerOpen = ref(false);
        const orderType = ref<'dine_in' | 'take_away'>('dine_in');
        const tableNumber = ref<string>('');
        const customerName = ref<string>('');
        const customerEmail = ref<string>('');
        const customerPhone = ref<string>('');
        const orderNotes = ref<string>('');
        const isSubmitting = ref(false);
        const submitError = ref<string>('');

        // Getters
        const totalItems = computed(() => items.value.reduce((sum, item) => sum + item.quantity, 0));

        const totalPrice = computed(() => items.value.reduce((sum, item) => sum + item.price * item.quantity, 0));

        const isEmpty = computed(() => items.value.length === 0);

        const canSubmitOrder = computed(() => {
            const hasItems = !isEmpty.value;
            const hasCustomerName = customerName.value.trim() !== '';

            const hasTableNumberForDineIn = orderType.value === 'dine_in' ? tableNumber.value.trim() !== '' : true;

            return hasItems && hasCustomerName && hasTableNumberForDineIn;
        });

        // Actions
        const addItem = (product: { id: number; name: string; price: number; image?: string }) => {
            const existingItemIndex = items.value.findIndex((i) => i.id === product.id);

            if (existingItemIndex > -1) {
                items.value[existingItemIndex].quantity += 1;
                // toast.success(`Added another ${product.name} to cart`);
            } else {
                items.value.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image,
                    quantity: 1,
                    notes: '',
                });
                // toast.success(`${product.name} added to cart`);
            }
        };

        const removeItem = (id: number) => {
            const item = items.value.find((i) => i.id === id);
            const index = items.value.findIndex((item) => item.id === id);

            if (index > -1) {
                items.value.splice(index, 1);
                if (item) {
                    toast.info(`${item.name} removed from cart`);
                }
            }
        };

        const updateQuantity = (id: number, quantity: number) => {
            if (quantity <= 0) {
                removeItem(id);
                return;
            }

            const item = items.value.find((i) => i.id === id);
            if (item) {
                item.quantity = quantity;
            }
        };

        const updateItemNotes = (id: number, notes: string) => {
            const item = items.value.find((i) => i.id === id);
            if (item) {
                item.notes = notes;
            }
        };

        const clearCart = () => {
            const itemCount = items.value.length;
            items.value = [];
            customerName.value = '';
            customerEmail.value = '';
            customerPhone.value = '';
            tableNumber.value = '';
            orderNotes.value = '';
            submitError.value = '';
            closeDrawer();

            if (itemCount > 0) {
                // No toast here as success/error toasts are handled by submitOrder
            }
        };

        const openDrawer = () => {
            isDrawerOpen.value = true;
            document.body.style.overflow = 'hidden';
        };

        const closeDrawer = () => {
            isDrawerOpen.value = false;
            document.body.style.overflow = '';
        };

        const setOrderType = (type: 'dine_in' | 'take_away') => {
            orderType.value = type;
            if (type === 'take_away') {
                tableNumber.value = '';
            }
        };

        const submitOrder = async () => {
            if (!canSubmitOrder.value) {
                submitError.value = 'Please fill in all required fields';
                toast.error('Please fill in all required fields');
                return false;
            }

            isSubmitting.value = true;
            submitError.value = '';

            try {
                const formData = {
                    _token: window.Laravel.csrfToken,
                    customer_name: customerName.value.trim(),
                    customer_email: customerEmail.value.trim() || null,
                    customer_phone: customerPhone.value.trim() || null,
                    order_type: orderType.value,
                    table_number: orderType.value === 'dine_in' ? tableNumber.value.trim() || null : null,
                    notes: orderNotes.value.trim() || null,
                    items: items.value.map((item) => ({
                        product_id: item.id,
                        quantity: item.quantity,
                        notes: item.notes?.trim() || null,
                    })),
                };

                const response = await fetch('/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(formData),
                });

                const result = await response.json();

                if (response.ok) {
                    orderStorage.saveOrder(result.data.order);

                    // --- NEW: Trigger order history update ---
                    const orderHistoryStore = useOrderHistoryStore();
                    orderHistoryStore.loadOrders(); // Explicitly tell the history store to reload its list
                    // --- END NEW ---

                    clearCart(); // Clear cart after successfully saving the order and updating history
                    toast.success(result.message);

                    setTimeout(() => {
                        router.visit(result.data.redirect_url);
                    }, 1000);

                    return true;
                } else {
                    let errorMessage = result.message || 'An error occurred during order submission.';
                    if (result.errors) {
                        const firstErrorKey = Object.keys(result.errors)[0];
                        errorMessage = result.errors[firstErrorKey][0];
                    }
                    throw new Error(errorMessage);
                }
            } catch (error: any) {
                console.error('Order submission error:', error);
                let displayMessage = error.message || 'An unexpected error occurred. Please try again.';

                submitError.value = displayMessage;
                toast.error(displayMessage);
                return false;
            } finally {
                isSubmitting.value = false;
            }
        };

        return {
            // State
            items,
            isDrawerOpen,
            orderType,
            tableNumber,
            customerName,
            customerEmail,
            customerPhone,
            orderNotes,
            isSubmitting,
            submitError,

            // Getters
            totalItems,
            totalPrice,
            isEmpty,
            canSubmitOrder,

            // Actions
            addItem,
            removeItem,
            updateQuantity,
            updateItemNotes,
            clearCart,
            openDrawer,
            closeDrawer,
            setOrderType,
            submitOrder,
        };
    },
    {
        persist: {
            key: 'pos-cart-store',
            storage: localStorage,
        },
    },
);
