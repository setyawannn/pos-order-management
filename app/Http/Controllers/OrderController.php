<?php
// app/Http/Controllers/OrderController.php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Store a new order
     */
    public function store(StoreOrderRequest $request)
    {
        try {

            $order = $this->orderService->createOrder($request);
            // dd($order->toArray());

            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $params = array(
                'transaction_details' => array(
                    'order_id' => $order->id,
                    'gross_amount' => $order->total_amount,
                ),
                'customer_details' => array(
                    'first_name' => $order->customer_name,
                    'last_name' => '',
                    'email' => $order->customer_email,
                    'phone' => $order->customer_phone,
                ),
            );

            // $snapToken = \Midtrans\Snap::getSnapToken($params);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'data' => [
                    'order' => $order->load('items.product.category'),
                    'redirect_url' => route('user.order.success', $order->order_code)
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => ['order' => $e->getMessage()]
            ], 422);
        }
    }

    /**
     * Show order success page
     */
    public function success(string $orderCode)
    {
        $order = $this->orderService->getOrderByCode($orderCode);

        if (!$order) {
            return redirect()
                ->route('user.home')
                ->withErrors(['order' => 'Order not found.']);
        }

        return Inertia::render('user/order/Success', [
            'order' => $order,
            'statusOptions' => OrderService::getStatusOptions(), // Overall order status
            // Removed itemStatusOptions here
        ]);
    }

    /**
     * Show order details (for tracking page)
     */
    public function show(string $orderCode)
    {
        $order = $this->orderService->getOrderByCode($orderCode);

        if (!$order) {
            return redirect()
                ->route('user.home')
                ->withErrors(['order' => 'Order not found.']);
        }

        return Inertia::render('user/order/Show', [
            'order' => $order,
            'statusOptions' => OrderService::getStatusOptions(),
            // Removed itemStatusOptions here
            'paymentStatusOptions' => OrderService::getPaymentStatusOptions(),
        ]);
    }

    /**
     * Get order status by code (API endpoint)
     */
    public function getOrderStatus(string $orderCode)
    {
        $order = $this->orderService->getOrderByCode($orderCode);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $order,
                'overall_status_label' => OrderService::getStatusOptions()[$order->status] ?? $order->status,
                // Removed item_status_labels here
            ]
        ]);
    }
}
