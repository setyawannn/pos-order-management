<!-- resources/js/pages/admin/orders/Index.vue -->
<template>
    <DashboardLayout>
        <div class="flex h-full flex-col bg-gray-50 p-6">
            <h1 class="mb-6 text-3xl font-bold text-gray-900">Order Management</h1>

            <!-- Filters & Search Bar -->
            <div
                class="mb-6 flex flex-col items-center justify-between space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-md md:flex-row md:space-y-0 md:space-x-4"
            >
                <!-- Search Input -->
                <div class="relative w-full flex-1 md:w-auto">
                    <Search class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                    <input
                        type="text"
                        v-model="form.search"
                        placeholder="Search by code, customer, table..."
                        class="w-full rounded-lg border border-gray-300 py-2.5 pr-4 pl-10 text-base transition-colors focus:border-red-500 focus:ring-2 focus:ring-red-500 focus:outline-none"
                        @input="debounceApplyFilters"
                    />
                </div>

                <div class="flex w-full flex-col items-center space-y-3 sm:flex-row sm:space-y-0 sm:space-x-3 md:w-auto">
                    <!-- Status Filter -->
                    <select
                        v-model="form.status"
                        @change="applyFilters"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-base transition-colors focus:border-red-500 focus:ring-2 focus:ring-red-500 focus:outline-none sm:w-auto"
                    >
                        <option value="all">All Statuses</option>
                        <option v-for="(label, key) in statusOptions" :key="key" :value="key">{{ label }}</option>
                    </select>

                    <!-- Order Type Filter -->
                    <select
                        v-model="form.order_type"
                        @change="applyFilters"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-base transition-colors focus:border-red-500 focus:ring-2 focus:ring-red-500 focus:outline-none sm:w-auto"
                    >
                        <option value="all">All Types</option>
                        <option v-for="(label, key) in orderTypeOptions" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
            </div>

            <!-- Order Cards Grid -->
            <div v-if="orders.data.length > 0" class="scrollbar-hide -mr-2 flex-1 overflow-y-auto pr-2">
                <div class="grid grid-cols-1 gap-6 pb-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <div
                        v-for="order in orders.data"
                        :key="order.id"
                        class="flex transform cursor-pointer flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-md transition-transform duration-200 hover:scale-[1.01]"
                        @click="viewOrderDetails(order.id)"
                    >
                        <div class="flex flex-1 flex-col p-5">
                            <div class="mb-3 flex items-start justify-between">
                                <h3 class="text-xl leading-tight font-bold text-gray-900">{{ order.order_code }}</h3>
                                <span
                                    :class="getOverallStatusClass(order.status)"
                                    class="ml-2 flex-shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold capitalize"
                                >
                                    {{ statusOptions[order.status] }}
                                </span>
                            </div>

                            <div class="mb-1 flex items-center text-sm text-gray-700">
                                <UserRound class="mr-2 h-4 w-4 text-red-500" />
                                <strong class="text-gray-800">{{ order.customer_name }}</strong>
                            </div>
                            <div class="mb-1 flex items-center text-sm text-gray-700">
                                <ClipboardList class="mr-2 h-4 w-4 text-red-500" />
                                <span class="capitalize">{{ order.order_type.replace('_', ' ') }}</span>
                                <span v-if="order.table_number" class="ml-3 flex items-center">
                                    <Tablet class="mr-1.5 h-4 w-4 text-red-500" /> {{ order.table_number }}
                                </span>
                            </div>
                            <div class="mb-3 flex items-center text-sm text-gray-700">
                                <Clock class="mr-2 h-4 w-4 text-red-500" />
                                <span>{{ formatDateTime(order.created_at) }}</span>
                            </div>

                            <div class="mt-auto flex items-center justify-between border-t border-gray-100 pt-3">
                                <span class="text-base font-semibold text-gray-800">Total:</span>
                                <PriceDisplay :amount="order.total_amount" class="text-xl font-bold text-red-500" />
                            </div>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50 p-4">
                            <Link
                                :href="route('admin.orders.show', order.id)"
                                class="flex items-center space-x-2 rounded-lg bg-red-500 px-5 py-2.5 text-sm font-medium text-white shadow-md transition-colors hover:bg-red-600"
                            >
                                <Eye class="h-4 w-4" />
                                <span>View Details</span>
                            </Link>
                            <button
                                @click.stop="confirmCancelOrder(order.id)"
                                :disabled="order.status === 'completed' || order.status === 'cancelled'"
                                :class="[
                                    'flex items-center space-x-2 rounded-lg px-4 py-2.5 text-sm font-medium shadow-md transition-colors',
                                    order.status === 'completed' || order.status === 'cancelled'
                                        ? 'cursor-not-allowed bg-gray-300 text-gray-600'
                                        : 'border border-red-500 bg-white text-red-600 hover:bg-red-50',
                                ]"
                            >
                                <Ban class="h-4 w-4" />
                                <span>Cancel</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="rounded-xl border border-gray-200 bg-white py-16 text-center shadow-md">
                <PackageX class="mx-auto mb-6 h-20 w-20 text-gray-300" />
                <p class="mb-2 text-2xl font-medium text-gray-700">No orders found.</p>
                <p class="text-md text-gray-500">Try adjusting your filters or search terms.</p>
            </div>

            <!-- Pagination -->
            <div
                v-if="orders.total > 0"
                class="mt-6 flex flex-col items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-md sm:flex-row"
            >
                <div class="mb-3 text-sm text-gray-600 sm:mb-0">
                    Showing {{ orders.from || 0 }} to {{ orders.to || 0 }} of {{ orders.total || 0 }} results
                </div>
                <div class="flex flex-wrap justify-center space-x-1">
                    <Link
                        v-for="(link, key) in orders.links"
                        :key="key"
                        :href="link.url || '#'"
                        v-html="link.label"
                        :class="{
                            'rounded-lg border px-3 py-1.5 text-sm font-medium transition-colors': true,
                            'border-red-500 bg-red-500 text-white': link.active,
                            'border-gray-300 bg-white text-gray-700 hover:bg-gray-100': !link.active && link.url,
                            'cursor-not-allowed border-gray-200 text-gray-400': !link.url,
                        }"
                        @click.prevent="link.url ? router.get(link.url, {}, { preserveState: true, replace: true }) : null"
                    />
                </div>
                <select
                    v-model="form.per_page"
                    @change="applyFilters"
                    class="mt-3 w-full rounded-md border border-gray-300 px-3 py-1.5 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500 focus:outline-none sm:mt-0 sm:ml-4 sm:w-auto"
                >
                    <option v-for="option in perPageOptions" :key="option" :value="option">{{ option }} / page</option>
                </select>
            </div>
        </div>

        <!-- Confirmation Modal for Order Cancellation -->
        <ConfirmationModal
            :show="showCancelModal"
            title="Confirm Order Cancellation"
            message="Are you sure you want to cancel this order? This action cannot be undone. You may not be able to revert stock changes."
            confirmText="Cancel Order"
            confirmVariant="destructive"
            icon="AlertTriangle"
            iconClass="text-red-600"
            confirmIcon="Ban"
            @confirm="deleteOrder"
            @cancel="showCancelModal = false"
        />
    </DashboardLayout>
</template>

<script setup lang="ts">
import ConfirmationModal from '@/components/reusable/ConfirmationModal.vue';
import PriceDisplay from '@/components/reusable/PriceDisplay.vue';
import DashboardLayout from '@/layouts/DashboardLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3'; // Ensure router is imported
import { onUnmounted, ref, watch } from 'vue';
import { useToast } from 'vue-toastification';
// Import all Lucide icons needed for this component
import { Ban, ClipboardList, Clock, Eye, PackageX, Search, Tablet, UserRound } from 'lucide-vue-next';

import type { Order } from '@/types';

interface OrderPagination {
    data: Order[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    from: number | null;
    to: number | null;
    total: number;
    current_page: number;
    last_page: number;
    per_page: number;
}

interface Props {
    orders: OrderPagination;
    filters: { search: string | null; status: string | null; order_type: string | null };
    statusOptions: Record<string, string>;
    orderTypeOptions: Record<string, string>;
    perPageOptions: number[];
}

const props = defineProps<Props>();
const toast = useToast();

const form = useForm({
    search: props.filters.search ?? '', // Use ?? for nullish coalescing
    status: props.filters.status ?? 'all',
    order_type: props.filters.order_type ?? 'all',
    per_page: props.orders.per_page,
});

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
        searchTimeout = null;
    }
    router.get(route('admin.orders.index'), form.data(), {
        // Corrected: form.data()
        preserveState: true,
        replace: true,
    });
};

const debounceApplyFilters = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

// Watch for changes in status and order_type selects to apply filters immediately
watch([() => form.status, () => form.order_type], () => {
    applyFilters();
});

// Watch for per_page changes to apply filters
watch(
    () => form.per_page,
    () => {
        applyFilters();
    },
);

onUnmounted(() => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
});

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

const showCancelModal = ref(false);
const orderToCancelId = ref<number | null>(null);

const confirmCancelOrder = (orderId: number) => {
    orderToCancelId.value = orderId;
    showCancelModal.value = true;
};

const deleteOrder = () => {
    if (orderToCancelId.value) {
        router.delete(route('admin.orders.destroy', orderToCancelId.value), {
            onSuccess: () => {
                showCancelModal.value = false;
                orderToCancelId.value = null;
                toast.success('Order cancelled successfully!');
            },
            onError: (errors) => {
                console.error('Order cancellation failed:', errors);
                showCancelModal.value = false;
                orderToCancelId.value = null;
                const errorMessage =
                    Object.values(errors)[0] && typeof Object.values(errors)[0] === 'string' ? Object.values(errors)[0] : 'Failed to cancel order.';
                toast.error(String(errorMessage));
            },
        });
    }
};

const viewOrderDetails = (orderId: number) => {
    router.visit(route('admin.orders.show', orderId));
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
