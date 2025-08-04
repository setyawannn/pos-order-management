<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr; // Import Arr

class AdminOrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    /**
     * Display a listing of orders (for cashier).
     */
    public function index(Request $request)
    {
        $filters = $request->only('search', 'status', 'order_type');
        $perPage = $request->input('per_page', 10);

        $query = Order::with('items.product');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_code', 'like', '%' . $request->search . '%')
                    ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                    ->orWhere('table_number', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('order_type') && $request->order_type !== 'all') {
            $query->where('order_type', $request->order_type);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        return Inertia::render('admin/orders/Index', [
            'orders' => $orders,
            'filters' => $filters,
            'statusOptions' => OrderService::getStatusOptions(),
            'orderTypeOptions' => [
                'dine_in' => 'Dine In',
                'take_away' => 'Take Away',
            ],
            'perPageOptions' => [10, 25, 50, 100],
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load('items.product');

        // Ensure itemStatusLabels are passed for correct rendering
        return Inertia::render('admin/orders/Show', [
            'order' => $order,
            'statusOptions' => OrderService::getStatusOptions(),
            'paymentStatusOptions' => OrderService::getPaymentStatusOptions(),
            'itemStatusLabels' => [
                'false' => 'In Progress',
                'true' => 'Done',
            ],
        ]);
    }

    /**
     * Update the specified order in storage.
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'table_number' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(array_keys(OrderService::getStatusOptions()))],
            'payment_status' => ['required', 'string', Rule::in(array_keys(OrderService::getPaymentStatusOptions()))],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'transaction_id' => ['nullable', 'string', 'max:255', Rule::unique('orders')->ignore($order->id)],
            'notes' => ['nullable', 'string', 'max:1000'],
            // Validation for the items array being sent
            'items' => ['array'],
            'items.*.id' => ['required', 'exists:order_items,id'], // OrderItem ID
            'items.*.is_done' => ['boolean'], // Allow toggling item done status
            'items.*.notes' => ['nullable', 'string', 'max:1000'], // Allow changing item notes
        ]);

        // Update overall order details
        // Use Arr::except to prevent 'items' from being passed directly to Order::update()
        $orderData = Arr::except($validated, ['items']);
        $order->update($orderData);

        // Handle individual order item updates
        if (isset($validated['items'])) {
            foreach ($validated['items'] as $itemData) {
                $orderItem = $order->items()->find($itemData['id']);
                if ($orderItem) {
                    $updateFields = [];
                    // Only update 'is_done' and 'notes' fields that are sent for items
                    if (array_key_exists('is_done', $itemData)) { // Check if key exists (even if false)
                        $updateFields['is_done'] = $itemData['is_done'];
                    }
                    if (array_key_exists('notes', $itemData)) { // Check if key exists (even if null string)
                        $updateFields['notes'] = $itemData['notes'];
                    }

                    if (!empty($updateFields)) {
                        $orderItem->update($updateFields);
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Order updated successfully!');
    }

    /**
     * Remove the specified order from storage (Cancel Order).
     */
    public function destroy(Order $order)
    {
        if ($order->status === 'completed' || $order->status === 'ready_to_serve') {
            return redirect()->back()->withErrors(['order' => 'Cannot cancel a completed or ready order.']);
        }

        $order->delete(); // This also deletes order_items due to cascade delete

        return redirect()->route('admin.orders.index')->with('success', 'Order cancelled successfully!');
    }
}
