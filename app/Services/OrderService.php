<?php
// app/Services/OrderService.php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderService
{
    /**
     * Create a new order with items
     */
    public function createOrder(StoreOrderRequest $request): Order
    {
        return DB::transaction(function () use ($request) {
            $validatedData = $request->validated();

            // Validate products and calculate total
            $orderData = $this->validateAndCalculateOrder($validatedData);

            // Create the order
            $order = Order::create([
                'customer_name' => $orderData['customer_name'],
                'customer_email' => $orderData['customer_email'],
                'customer_phone' => $orderData['customer_phone'],
                'order_type' => $orderData['order_type'],
                'table_number' => $orderData['table_number'],
                'total_amount' => $orderData['total_amount'],
                'status' => 'in_queue', // Default overall order status for anonymous orders
                'payment_method' => 'cash', // Default payment method
                'payment_status' => 'settlement', // Assume payment is successful for now
                'notes' => $orderData['notes'],
            ]);

            // Create order items and set their initial status to 'is_done = false'
            $this->createOrderItems($order, $orderData['items']);

            // Update product stock if managed
            $this->updateProductStock($orderData['items']);

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'customer_name' => $order->customer_name,
                'total_amount' => $order->total_amount
            ]);

            return $order->load('items.product');
        });
    }

    /**
     * Validate products and calculate order total
     */
    private function validateAndCalculateOrder(array $data): array
    {
        $productIds = collect($data['items'])->pluck('product_id')->unique();
        $products = Product::whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $totalAmount = 0;
        $validatedItems = [];

        foreach ($data['items'] as $item) {
            $product = $products->get($item['product_id']);

            if (!$product) {
                throw new Exception("Product with ID {$item['product_id']} not found or inactive.");
            }

            // Check stock availability
            if ($product->is_stock_managed && $product->stock < $item['quantity']) {
                throw new Exception("Insufficient stock for product: {$product->name}. Available: {$product->stock}, Requested: {$item['quantity']}");
            }

            $itemPrice = (float) $product->price;
            $itemSubtotal = $itemPrice * $item['quantity'];
            $totalAmount += $itemSubtotal;

            $validatedItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $itemPrice,
                'subtotal' => $itemSubtotal,
                'notes' => $item['notes'] ?? null,
                'is_done' => false, // Set default status for order item as false (not done)
            ];
        }

        return array_merge($data, [
            'items' => $validatedItems,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Create order items
     */
    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            $order->items()->create($item);
        }
    }

    /**
     * Update product stock
     */
    private function updateProductStock(array $items): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if ($product && $product->is_stock_managed) {
                $product->decrement('stock', $item['quantity']);

                Log::info('Product stock updated', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_sold' => $item['quantity'],
                    'remaining_stock' => $product->fresh()->stock
                ]);
            }
        }
    }

    /**
     * Get order by code with items
     */
    public function getOrderByCode(string $orderCode): ?Order
    {
        return Order::with(['items.product.category']) // Ensure 'items' are eager loaded
            ->where('order_code', $orderCode)
            ->first();
    }

    /**
     * Get overall order status options
     */
    public static function getStatusOptions(): array
    {
        return [
            'waiting_payment' => 'Waiting Payment',
            'payment_failed' => 'Payment Failed',
            'in_queue' => 'In Queue',
            'in_progress' => 'In Progress',
            'ready_to_serve' => 'Ready to Serve',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    // No specific getItemStatusOptions needed as it's just true/false handled in UI

    /**
     * Get payment status options
     */
    public static function getPaymentStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'authorize' => 'Authorized',
            'capture' => 'Captured',
            'settlement' => 'Settlement',
            'deny' => 'Denied',
            'cancel' => 'Cancelled',
            'refund' => 'Refunded',
            'partial_refund' => 'Partial Refund',
            'chargeback' => 'Chargeback',
            'partial_chargeback' => 'Partial Chargeback',
            'expire' => 'Expired',
            'failure' => 'Failed',
        ];
    }
}
