<!-- resources/js/pages/admin/orders/Show.vue -->
<template>
    <DashboardLayout>
        <div class="flex h-full flex-col bg-gray-50 p-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Order: {{ order.order_code }}</h1>
                <Link
                    :href="route('admin.orders.index')"
                    class="flex items-center space-x-2 rounded-lg bg-gray-200 px-5 py-2.5 text-gray-800 transition-colors hover:bg-gray-300"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span>Back to List</span>
                </Link>
            </div>

            <form
                @submit.prevent="submitForm"
                class="scrollbar-hide flex flex-1 flex-col space-y-8 overflow-y-auto rounded-xl border border-gray-200 bg-white p-8 shadow-md"
            >
                <!-- General Order & Customer Details Section -->
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div>
                        <h2 class="mb-5 flex items-center space-x-2 text-xl font-semibold text-gray-800">
                            <UserRound class="h-5 w-5 text-red-500" />
                            <span>Customer Information</span>
                        </h2>
                        <div class="space-y-5">
                            <div>
                                <label for="customer_name" class="mb-1 block text-sm font-medium text-gray-700"
                                    >Name <span class="text-red-500">*</span></label
                                >
                                <input
                                    id="customer_name"
                                    v-model="form.customer_name"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 transition-colors focus:border-red-500 focus:ring-red-500"
                                />
                                <div v-if="form.errors.customer_name" class="mt-1 text-xs text-red-500">{{ form.errors.customer_name }}</div>
                            </div>
                            <div>
                                <label for="customer_email" class="mb-1 block text-sm font-medium text-gray-700"
                                    >Email <span class="text-red-500">*</span></label
                                >
                                <input
                                    id="customer_email"
                                    v-model="form.customer_email"
                                    type="email"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 transition-colors focus:border-red-500 focus:ring-red-500"
                                />
                                <div v-if="form.errors.customer_email" class="mt-1 text-xs text-red-500">{{ form.errors.customer_email }}</div>
                            </div>
                            <div>
                                <label for="customer_phone" class="mb-1 block text-sm font-medium text-gray-700"
                                    >Phone <span class="text-red-500">*</span></label
                                >
                                <input
                                    id="customer_phone"
                                    v-model="form.customer_phone"
                                    type="tel"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 transition-colors focus:border-red-500 focus:ring-red-500"
                                />
                                <div v-if="form.errors.customer_phone" class="mt-1 text-xs text-red-500">{{ form.errors.customer_phone }}</div>
                            </div>
                            <div v-if="order.order_type === 'dine_in'">
                                <label for="table_number" class="mb-1 block text-sm font-medium text-gray-700">Table Number</label>
                                <input
                                    id="table_number"
                                    v-model="form.table_number"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 transition-colors focus:border-red-500 focus:ring-red-500"
                                />
                                <div v-if="form.errors.table_number" class="mt-1 text-xs text-red-500">{{ form.errors.table_number }}</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="mb-5 flex items-center space-x-2 text-xl font-semibold text-gray-800">
                            <ClipboardList class="h-5 w-5 text-red-500" />
                            <span>Order Details</span>
                        </h2>
                        <div class="space-y-5">
                            <div>
                                <label for="order_status" class="mb-1 block text-sm font-medium text-gray-700">Order Status</label>
                                <select
                                    id="order_status"
                                    v-model="form.status"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 transition-colors focus:border-red-500 focus:ring-red-500"
                                >
                                    <option v-for="(label, key) in statusOptions" :key="key" :value="key">{{ label }}</option>
                                </select>
                                <div v-if="form.errors.status" class="mt-1 text-xs text-red-500">{{ form.errors.status }}</div>
                            </div>
                            <div>
                                <label for="payment_status" class="mb-1 block text-sm font-medium text-gray-700">Payment Status</label>
                                <select
                                    id="payment_status"
                                    v-model="form.payment_status"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 transition-colors focus:border-red-500 focus:ring-red-500"
                                >
                                    <option v-for="(label, key) in paymentStatusOptions" :key="key" :value="key">{{ label }}</option>
                                </select>
                                <div v-if="form.errors.payment_status" class="mt-1 text-xs text-red-500">{{ form.errors.payment_status }}</div>
                            </div>
                            <div>
                                <label for="payment_method" class="mb-1 block text-sm font-medium text-gray-700">Payment Method</label>
                                <input
                                    id="payment_method"
                                    v-model="form.payment_method"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 transition-colors focus:border-red-500 focus:ring-red-500"
                                />
                                <div v-if="form.errors.payment_method" class="mt-1 text-xs text-red-500">{{ form.errors.payment_method }}</div>
                            </div>
                            <div>
                                <label for="transaction_id" class="mb-1 block text-sm font-medium text-gray-700">Transaction ID</label>
                                <input
                                    id="transaction_id"
                                    v-model="form.transaction_id"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 transition-colors focus:border-red-500 focus:ring-red-500"
                                />
                                <div v-if="form.errors.transaction_id" class="mt-1 text-xs text-red-500">{{ form.errors.transaction_id }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-6 border-gray-200" />

                <!-- Ordered Items Section -->
                <div>
                    <h2 class="mb-4 flex items-center space-x-2 text-xl font-semibold text-gray-800">
                        <ShoppingCart class="h-5 w-5 text-red-500" />
                        <span>Items Ordered</span>
                    </h2>
                    <div class="scrollbar-hide -mr-2 max-h-80 space-y-4 overflow-y-auto rounded-xl border border-gray-200 p-6 pr-2">
                        <div
                            v-for="item in form.items"
                            :key="item.id"
                            class="flex items-start space-x-4 border-b border-gray-100 pb-4 last:border-b-0 last:pb-0"
                        >
                            <img
                                :src="item.product.image || '/images/placeholder.jpg'"
                                :alt="item.product.name"
                                class="h-20 w-20 flex-shrink-0 rounded-md object-cover"
                            />
                            <div class="flex-1">
                                <p class="mb-1 text-base font-semibold text-gray-900">{{ item.quantity }}x {{ item.product.name }}</p>
                                <p class="text-sm text-gray-600">Price: <PriceDisplay :amount="item.price" /></p>
                                <p class="mb-2 text-sm text-gray-600">Subtotal: <PriceDisplay :amount="item.subtotal" class="font-semibold" /></p>
                                <p v-if="item.notes" class="text-xs text-gray-500 italic">User Notes: {{ item.notes }}</p>
                                <div class="mt-2">
                                    <label :for="`item_notes_${item.id}`" class="mb-1 block text-xs font-medium text-gray-700"
                                        >Cashier Notes for Item:</label
                                    >
                                    <textarea
                                        :id="`item_notes_${item.id}`"
                                        v-model="item.notes"
                                        rows="1"
                                        class="w-full resize-none rounded-md border border-gray-300 px-2.5 py-1.5 text-xs transition-colors focus:border-red-500 focus:ring-red-500"
                                    ></textarea>
                                </div>
                            </div>
                            <div class="flex-shrink-0 text-right">
                                <span :class="getItemStatusClass(item.is_done)" class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize">
                                    {{ itemStatusLabels[item.is_done.toString()] }}
                                </span>
                                <!-- Mark as Done/Undone button - only show if order is ready_to_serve -->
                                <button
                                    type="button"
                                    v-if="order.status === 'ready_to_serve'"
                                    @click="toggleItemDone(item.id, item.is_done)"
                                    class="mt-2 rounded-md bg-red-100 px-3 py-1 text-xs text-red-600 transition-colors hover:bg-red-200"
                                >
                                    {{ item.is_done ? 'Mark Undone' : 'Mark Done' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-6 border-gray-200" />

                <!-- General Order Notes -->
                <div>
                    <h2 class="mb-4 flex items-center space-x-2 text-xl font-semibold text-gray-800">
                        <FileText class="h-5 w-5 text-red-500" />
                        <span>General Order Notes</span>
                    </h2>
                    <textarea
                        v-model="form.notes"
                        rows="4"
                        class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2.5 transition-colors focus:border-red-500 focus:ring-red-500"
                    ></textarea>
                    <div v-if="form.errors.notes" class="mt-1 text-xs text-red-500">{{ form.errors.notes }}</div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8 flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center space-x-2 rounded-lg bg-red-500 px-8 py-3 font-semibold text-white shadow-lg transition-colors hover:bg-red-600 disabled:opacity-50"
                    >
                        <Loader2 v-if="form.processing" class="h-5 w-5 animate-spin" />
                        <SaveAll v-else class="h-5 w-5" />
                        <span>{{ form.processing ? 'Updating...' : 'Save Changes' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup lang="ts">
import PriceDisplay from '@/components/reusable/PriceDisplay.vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ClipboardList, FileText, Loader2, SaveAll, ShoppingCart, UserRound } from 'lucide-vue-next'; // Removed UtensilsCrossed, added CheckCircle, CircleX etc.
import { useToast } from 'vue-toastification';

import type { Order, OrderItem, Product } from '@/types';

// Define FormOrderItem to perfectly match the nested items structure within useForm
interface FormOrderItem extends Omit<OrderItem, 'notes' | 'product'> {
    notes: string; // Override notes to be always string for form binding
    product: Product; // Keep Product data for display
    // All other original properties of OrderItem must also be present
    quantity: number;
    price: number;
    subtotal: number;
}

// Define the full structure for the useForm helper's data
// This now includes an index signature to satisfy FormDataType constraints
interface OrderFormState {
    [key: string]: any; // Index signature allows arbitrary string keys
    _method: string;
    customer_name: string;
    customer_email: string;
    customer_phone: string;
    table_number: string;
    status: string;
    payment_status: string;
    payment_method: string;
    transaction_id: string;
    notes: string;
    items: FormOrderItem[]; // Use the defined FormOrderItem
}

// Define the component's props interface
interface Props {
    order: Order; // The original Order object passed from Laravel
    statusOptions: Record<string, string>;
    paymentStatusOptions: Record<string, string>;
    itemStatusLabels: Record<string, string>; // From backend as "false"/"true"
}

const props = defineProps<Props>();
const toast = useToast();

// Initialize useForm with explicit type and safe defaults
const form = useForm<OrderFormState>({
    _method: 'patch',
    customer_name: props.order.customer_name ?? '',
    customer_email: props.order.customer_email ?? '',
    customer_phone: props.order.customer_phone ?? '',
    table_number: props.order.table_number ?? '',
    status: props.order.status,
    payment_status: props.order.payment_status,
    payment_method: props.order.payment_method ?? '',
    transaction_id: props.order.transaction_id ?? '',
    notes: props.order.notes ?? '',
    items: props.order.items.map((item) => ({
        id: item.id,
        is_done: item.is_done,
        notes: item.notes ?? '', // Ensure item notes are defaulted to string
        product: item.product,
        quantity: item.quantity, // Ensure these properties from OrderItem are explicitly mapped
        price: item.price,
        subtotal: item.subtotal,
    })),
});

const submitForm = () => {
    // `useForm` automatically handles sending the form's reactive data.
    // We don't need to manually create a `dataToSubmit` object if we just
    // want to send the entire `form` state, which `useForm` is designed for.
    // The `items` array is part of `form` and will be serialized correctly.

    form.post(route('admin.orders.update', props.order.id), {
        onSuccess: () => {
            toast.success('Order updated successfully!');
        },
        onError: (errors) => {
            console.error('Order update failed:', errors);
            toast.error('Failed to update order. Please check the form for errors.');
        },
        preserveScroll: true,
    });
};

const formatDateTime = (dateTime: string) => {
    return new Date(dateTime).toLocaleString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
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

const toggleItemDone = (itemId: number, currentIsDone: boolean) => {
    if (props.order.status === 'ready_to_serve') {
        const itemIndex = form.items.findIndex((item) => item.id === itemId);
        if (itemIndex !== -1) {
            form.items[itemIndex].is_done = !currentIsDone;
        }
    } else {
        toast.info('Item status can only be toggled when the order is "Ready to Serve".');
    }
};
</script>

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
