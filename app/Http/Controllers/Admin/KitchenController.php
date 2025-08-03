<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class KitchenController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index()
    {
        $orders = $this->getKitchenOrders();

        return Inertia::render('admin/kitchen/Index', [
            'initialOrders' => $orders,
            'statusOptions' => OrderService::getStatusOptions(),
        ]);
    }

    /**
     * API endpoint to fetch kitchen orders.
     */
    public function getOrdersApi()
    {
        $orders = $this->getKitchenOrders();
        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    /**
     * Toggle the 'is_done' status of an OrderItem.
     */
    public function toggleOrderItemDone(Request $request, OrderItem $orderItem)
    {
        $orderInKitchenFlow = in_array($orderItem->order->status, ['in_queue', 'in_progress', 'ready_to_serve']);

        if (!$orderInKitchenFlow) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update item status for an order not in kitchen flow.',
            ], 403);
        }

        $orderItem->is_done = !$orderItem->is_done;
        $orderItem->save();

        $order = $orderItem->order;
        $allOrderItemsDone = $order->items->every('is_done');

        if ($allOrderItemsDone && $order->status === 'in_progress') {
            $order->status = 'ready_to_serve';
            $order->save();
        } elseif (!$allOrderItemsDone && $order->status === 'ready_to_serve') {
            $order->status = 'in_progress';
            $order->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Order item status updated successfully.',
            'order_item' => $orderItem,
            'order' => $order->load('items.product'),
        ]);
    }

    /**
     * Update the overall status of an Order.
     */
    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'string', Rule::in(['in_queue', 'in_progress', 'ready_to_serve', 'completed', 'cancelled'])],
        ]);

        $currentStatus = $order->status;
        $newStatus = $request->input('status');

        $allowedTransitions = [
            'in_queue' => ['in_progress', 'cancelled'],
            'in_progress' => ['ready_to_serve', 'cancelled'],
            // Removed 'completed' from ready_to_serve allowed transitions in KitchenController
            // As per new requirement, cashier/other role handles 'completed' state.
            // Chef only pushes to 'ready_to_serve'.
            'ready_to_serve' => ['cancelled'], // Chef can only cancel from here, not complete
            'completed' => [],
            'cancelled' => [],
        ];

        if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status transition.',
            ], 403);
        }

        if ($newStatus === 'ready_to_serve' && !$order->items->every('is_done')) {
            return response()->json([
                'success' => false,
                'message' => 'All items must be marked as done before marking order as "Ready to Serve".',
            ], 400);
        }

        // Removed specific check for 'completed' transition logic as chef won't complete

        $order->status = $newStatus;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'order' => $order->load('items.product'),
        ]);
    }

    /**
     * Helper to get orders for the kitchen dashboard.
     */
    private function getKitchenOrders()
    {
        $orders = Order::with(['items.product'])
            ->whereIn('status', ['in_queue', 'in_progress', 'ready_to_serve'])
            ->get();

        $groupedOrders = [
            'in_progress' => [],
            'in_queue' => [],
            'ready_to_serve' => [],
        ];

        foreach ($orders as $order) {
            $order->setAttribute('time_since_creation', Carbon::parse($order->created_at)->diffForHumans());
            $order->setAttribute('human_created_at', Carbon::parse($order->created_at)->format('H:i'));

            // Remove disappearing_at logic from here
            // $order->setAttribute('disappearing_at', ...);

            $groupedOrders[$order->status][] = $order;
        }

        usort($groupedOrders['in_progress'], function ($a, $b) {
            return $b->created_at <=> $a->created_at;
        });

        usort($groupedOrders['in_queue'], function ($a, $b) {
            return $a->created_at <=> $b->created_at;
        });
        usort($groupedOrders['ready_to_serve'], function ($a, $b) {
            return $a->created_at <=> $b->created_at;
        });

        return $groupedOrders;
    }
}
