// resources/js/stores/cartStore.ts
import type { CartItem, OrderData } from '@/types';
import { router } from '@inertiajs/vue3';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useCartStore = defineStore(
    'cart',
    () => {
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
            return !isEmpty.value && customerName.value.trim() !== '' && (orderType.value === 'take_away' || tableNumber.value.trim() !== '');
        });

        // Actions
        const addItem = (product: { id: number; name: string; price: number; image?: string }) => {
            const existingItemIndex = items.value.findIndex((i) => i.id === product.id);

            if (existingItemIndex > -1) {
                items.value[existingItemIndex].quantity += 1;
            } else {
                items.value.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image,
                    quantity: 1,
                    notes: '',
                });
            }
        };

        const removeItem = (id: number) => {
            const index = items.value.findIndex((item) => item.id === id);
            if (index > -1) {
                items.value.splice(index, 1);
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
            items.value = [];
            customerName.value = '';
            customerEmail.value = '';
            customerPhone.value = '';
            tableNumber.value = '';
            orderNotes.value = '';
            closeDrawer();
        };

        const openDrawer = () => {
            isDrawerOpen.value = true;
            // Prevent body scroll when drawer is open
            document.body.style.overflow = 'hidden';
        };

        const closeDrawer = () => {
            isDrawerOpen.value = false;
            // Restore body scroll
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
                return false;
            }

            isSubmitting.value = true;
            submitError.value = '';

            try {
                const orderData: OrderData = {
                    customer_name: customerName.value,
                    customer_email: customerEmail.value || undefined,
                    customer_phone: customerPhone.value || undefined,
                    order_type: orderType.value,
                    table_number: orderType.value === 'dine_in' ? tableNumber.value : undefined,
                    total_amount: totalPrice.value,
                    notes: orderNotes.value || undefined,
                    items: items.value.map((item) => ({
                        product_id: item.id,
                        quantity: item.quantity,
                        price: item.price,
                        notes: item.notes || undefined,
                    })),
                };

                await router.post(
                    '/orders',
                    { ...orderData },
                    {
                        onSuccess: () => {
                            clearCart();
                        },
                        onError: (errors) => {
                            console.error('Order submission failed:', errors);
                            submitError.value = 'Failed to submit order. Please try again.';
                        },
                    },
                );

                return true;
            } catch (error) {
                console.error('Order submission error:', error);
                submitError.value = 'An unexpected error occurred. Please try again.';
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
            key: 'cart-store',
            paths: ['items', 'orderType', 'tableNumber', 'customerName', 'customerEmail', 'customerPhone'],
        } as any,
    },
);
