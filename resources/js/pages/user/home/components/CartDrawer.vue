<!-- resources/js/pages/user/home/components/CartDrawer.vue -->
<template>
    <!-- Overlay -->
    <Teleport to="body">
        <div v-if="cartStore.isDrawerOpen" class="bg-opacity-50 fixed inset-0 z-50 bg-black transition-opacity" @click="cartStore.closeDrawer" />

        <!-- Drawer -->
        <div
            :class="[
                'fixed right-0 bottom-0 left-0 z-50 flex transform flex-col rounded-t-2xl bg-white transition-transform duration-300',
                cartStore.isDrawerOpen ? 'translate-y-0' : 'translate-y-full',
            ]"
            style="max-height: 85vh"
        >
            <!-- Handle -->
            <div class="flex flex-shrink-0 justify-center py-3">
                <div class="h-1 w-12 rounded-full bg-gray-300"></div>
            </div>

            <!-- Header -->
            <div class="flex-shrink-0 border-b border-gray-200 px-4 pb-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Your Order</h2>
                    <button @click="cartStore.closeDrawer" class="rounded-lg p-2 transition-colors hover:bg-gray-100">
                        <X class="h-5 w-5 text-gray-500" />
                    </button>
                </div>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto">
                <div class="space-y-6 px-4 py-4">
                    <!-- Customer Information Form -->
                    <div class="space-y-4">
                        <h3 class="font-semibold text-gray-900">Customer Information</h3>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700"> Name <span class="text-red-500">*</span> </label>
                            <input
                                v-model="cartStore.customerName"
                                type="text"
                                placeholder="Enter your name"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-red-500 focus:outline-none"
                                required
                            />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700"> Phone Number <span class="text-red-500">*</span> </label>
                            <input
                                v-model="cartStore.customerPhone"
                                type="tel"
                                placeholder="Enter your phone number"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-red-500 focus:outline-none"
                                required
                            />
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700"> Email <span class="text-red-500">*</span> </label>
                            <input
                                v-model="cartStore.customerEmail"
                                type="email"
                                placeholder="Enter your email"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-red-500 focus:outline-none"
                                required
                            />
                        </div>

                        <!-- Table Number for Dine In -->
                        <div v-if="cartStore.orderType === 'dine_in'">
                            <label class="mb-1 block text-sm font-medium text-gray-700"> Table Number <span class="text-red-500">*</span> </label>
                            <input
                                v-model="cartStore.tableNumber"
                                type="text"
                                placeholder="Enter table number"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-red-500 focus:outline-none"
                                required
                            />
                        </div>
                    </div>

                    <!-- Cart Items -->
                    <div>
                        <h3 class="mb-4 font-semibold text-gray-900">Order Items</h3>

                        <div v-if="cartStore.items.length === 0" class="py-8 text-center">
                            <ShoppingCart class="mx-auto mb-3 h-12 w-12 text-gray-300" />
                            <p class="text-gray-500">Your cart is empty</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="item in cartStore.items" :key="item.id" class="flex items-start space-x-3 rounded-lg bg-gray-50 p-3">
                                <img
                                    :src="item.image || '/images/placeholder.jpg'"
                                    :alt="item.name"
                                    class="h-12 w-12 flex-shrink-0 rounded-lg bg-gray-100 object-cover"
                                />

                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-medium text-gray-900">{{ item.name }}</h4>
                                    <PriceDisplay :amount="item.price" class="text-sm font-semibold text-red-500" />

                                    <!-- Item Notes -->
                                    <textarea
                                        :value="item.notes"
                                        @input="cartStore.updateItemNotes(item.id, ($event.target as HTMLTextAreaElement).value)"
                                        placeholder="Special notes for this item..."
                                        rows="2"
                                        class="mt-2 w-full resize-none rounded border border-gray-200 px-2 py-1 text-xs focus:ring-1 focus:ring-red-500 focus:outline-none"
                                    />
                                </div>

                                <!-- Quantity Controls -->
                                <div class="flex flex-shrink-0 flex-col items-center space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            @click="cartStore.updateQuantity(item.id, item.quantity - 1)"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white transition-all hover:bg-gray-50 active:scale-95"
                                        >
                                            <Minus class="h-4 w-4 text-gray-600" />
                                        </button>

                                        <span class="w-8 text-center text-sm font-medium">{{ item.quantity }}</span>

                                        <button
                                            @click="cartStore.updateQuantity(item.id, item.quantity + 1)"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-500 text-white transition-all hover:bg-red-600 active:scale-95"
                                        >
                                            <Plus class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <!-- Remove Button -->
                                    <button @click="cartStore.removeItem(item.id)" class="rounded p-1 text-red-500 transition-colors hover:bg-red-50">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div v-if="cartStore.items.length > 0">
                        <label class="mb-1 block text-sm font-medium text-gray-700"> Order Notes </label>
                        <textarea
                            v-model="cartStore.orderNotes"
                            placeholder="Any special instructions for your order..."
                            rows="3"
                            class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-red-500 focus:outline-none"
                        />
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div v-if="cartStore.items.length > 0" class="flex-shrink-0 border-t border-gray-200 bg-white px-4 py-4">
                <div class="space-y-3">
                    <!-- Error Message -->
                    <div v-if="cartStore.submitError" class="text-center text-sm text-red-600">
                        {{ cartStore.submitError }}
                    </div>

                    <!-- Subtotal -->
                    <div class="flex justify-between text-lg font-semibold">
                        <span class="text-gray-900">Total</span>
                        <PriceDisplay :amount="cartStore.totalPrice" class="text-red-500" />
                    </div>

                    <!-- Submit Button -->
                    <button
                        @click="cartStore.submitOrder"
                        :disabled="!cartStore.canSubmitOrder || cartStore.isSubmitting"
                        :class="[
                            'flex w-full items-center justify-center space-x-2 rounded-lg py-3 font-medium transition-all',
                            cartStore.canSubmitOrder && !cartStore.isSubmitting
                                ? 'bg-red-500 text-white hover:bg-red-600 active:scale-95'
                                : 'cursor-not-allowed bg-gray-300 text-gray-500',
                        ]"
                    >
                        <Loader2 v-if="cartStore.isSubmitting" class="h-5 w-5 animate-spin" />
                        <CreditCard v-else class="h-5 w-5" />
                        <span>
                            {{ cartStore.isSubmitting ? 'Processing...' : 'Place Order' }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import PriceDisplay from '@/components/reusable/PriceDisplay.vue';
import { useCartStore } from '@/stores/cartStore';
import { CreditCard, Loader2, Minus, Plus, ShoppingCart, Trash2, X } from 'lucide-vue-next';

const cartStore = useCartStore();
</script>
