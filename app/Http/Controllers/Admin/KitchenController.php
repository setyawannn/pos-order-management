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
        // Initial fetch of orders for the kitchen dashboard
        $orders = $this->getKitchenOrders();

        return Inertia::render('admin/kitchen/Index', [
            'initialOrders' => $orders,
            'statusOptions' => OrderService::getStatusOptions(), // Still useful for general labels
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
        // Basic authorization: ensure this item belongs to an order currently in kitchen flow
        $orderInKitchenFlow = in_array($orderItem->order->status, ['in_queue', 'in_progress', 'ready_to_serve']);

        if (!$orderInKitchenFlow) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update item status for an order not in kitchen flow.',
            ], 403);
        }

        $orderItem->is_done = !$orderItem->is_done;
        $orderItem->save();

        // After updating an item, check if all items in the order are done
        $order = $orderItem->order;
        $allOrderItemsDone = $order->items->every('is_done');

        // If all items are done and the order is in progress,
        // automatically move the order to 'ready_to_serve'
        if ($allOrderItemsDone && $order->status === 'in_progress') {
            $order->status = 'ready_to_serve';
            $order->save();
        } elseif (!$allOrderItemsDone && $order->status === 'ready_to_serve') {
            // If an item is unchecked and order was ready to serve,
            // push it back to in_progress or in_queue based on previous state (complex, simplified here)
            // For simplicity, let's move it back to 'in_progress'
            $order->status = 'in_progress';
            $order->save();
        }


        return response()->json([
            'success' => true,
            'message' => 'Order item status updated successfully.',
            // Return updated order with relations for frontend refresh
            'order' => $order->load('items.product'),
        ]);
    }

    /**
     * Update the overall status of an Order.
     */
    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'string', Rule::in([
                'in_queue',
                'in_progress',
                'ready_to_serve',
                'completed',
                'cancelled'
            ])],
        ]);

        // Authorization/Validation logic
        $currentStatus = $order->status;
        $newStatus = $request->input('status');

        $allowedTransitions = [
            'in_queue' => ['in_progress', 'cancelled'],
            'in_progress' => ['ready_to_serve', 'cancelled'],
            'ready_to_serve' => ['completed', 'cancelled'],
            'completed' => [], // No transitions from completed in kitchen
            'cancelled' => [], // No transitions from cancelled in kitchen
        ];

        if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status transition.',
            ], 403);
        }

        // Additional check for 'ready_to_serve': all items must be done
        if ($newStatus === 'ready_to_serve' && !$order->items->every('is_done')) {
            return response()->json([
                'success' => false,
                'message' => 'All items must be marked as done before marking order as "Ready to Serve".',
            ], 400);
        }

        // When setting to completed, ensure it passes through ready_to_serve
        if ($newStatus === 'completed' && $currentStatus !== 'ready_to_serve') {
            return response()->json([
                'success' => false,
                'message' => 'Order must be "Ready to Serve" before "Completed".',
            ], 400);
        }

        $order->status = $newStatus;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'order' => $order->load('items.product'), // Reload with relations
        ]);
    }

    /**
     * Helper to get orders for the kitchen dashboard.
     */
    private function getKitchenOrders()
    {
        $orders = Order::with(['items.product'])
            ->whereIn('status', ['in_queue', 'in_progress', 'ready_to_serve'])
            ->get(); // Get all relevant orders first

        // Manually process and group to add dynamic properties
        $groupedOrders = [
            'in_progress' => [],
            'in_queue' => [],
            'ready_to_serve' => [],
        ];

        foreach ($orders as $order) {
            $order->setAttribute('time_since_creation', Carbon::parse($order->created_at)->diffForHumans());
            $order->setAttribute('human_created_at', Carbon::parse($order->created_at)->format('H:i'));

            // Check if all items are done for 'ready_to_serve' logic
            $allItemsDone = $order->items->every('is_done');
            if ($order->status === 'in_progress' && $allItemsDone) {
                // If all items are done, conceptually move it to 'ready_to_serve' for display,
                // but keep its actual status as 'in_progress' until chef clicks button.
                // Or, if you want automatic transition, the toggleOrderItemDone handles it.
                // For display purposes, we can add a flag
                // $order->setAttribute('can_be_served', true);
            }

            // If the order is 'ready_to_serve', add a disappearing_at timestamp
            if ($order->status === 'ready_to_serve') {
                // Determine when it should disappear (e.g., 10 seconds after it became ready_to_serve)
                // For demonstration, let's just make it disappear 10s after fetching if not yet cleared.
                // In a real app, this would be based on when status actually changed to ready_to_serve.
                $order->setAttribute('disappearing_at', Carbon::now()->addSeconds(10)->toIso8601String());
            }

            // Add to appropriate group
            $groupedOrders[$order->status][] = $order;
        }

        // Apply sorting rules after grouping and adding custom attributes
        // 1. in_progress orders first, newest first (right of newest = oldest)
        usort($groupedOrders['in_progress'], function ($a, $b) {
            return $b->created_at <=> $a->created_at; // Newest first (oldest on the right)
        });

        // 2. in_queue orders by oldest first
        usort($groupedOrders['in_queue'], function ($a, $b) {
            return $a->created_at <=> $b->created_at; // Oldest first
        });

        // 3. ready_to_serve orders by oldest first
        usort($groupedOrders['ready_to_serve'], function ($a, $b) {
            return $a->created_at <=> $b->created_at; // Oldest first
        });

        return $groupedOrders;
    }
}
