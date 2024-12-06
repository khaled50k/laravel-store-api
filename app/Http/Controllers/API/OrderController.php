<?php

namespace App\Http\Controllers\API;

use App\Events\NewOrderPlaced;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends BaseController
{
    /**
     * List all orders.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $orders = Order::with(['items.product', 'items.color', 'items.size', 'payment', 'shipping'])
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->paginate($perPage);
        return $this->sendResponse($orders, 'All orders retrieved successfully.');
    }



    /**
     * Show details of a specific order.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $id = $request->query('id');
        $perPage = $request->query('per_page', 10);

        if ($id) {
            $order = Order::with(['items.product', 'items.color', 'items.size', 'payment', 'shipping'])->where('user_id', $user->id)->find($id);
            if (is_null($order)) {
                return $this->sendError('Order not found.');
            }
        } else {
            $orders = Order::with(['items.product', 'items.color', 'items.size', 'payment', 'shipping'])->where('user_id', $user->id)
                ->paginate($perPage);
        }

        return $this->sendResponse($id ? $order : $orders, 'Order details retrieved successfully.');
    }

    /**
     * Create a new order.
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency' => 'required|string|max:3',
            'status' => 'required|string|in:pending,processing,completed,cancelled',
            'order_items' => 'required|array',
            'order_items.*.product_id' => 'required|exists:products,id',
            'order_items.*.quantity' => 'required|integer|min:1',
            'order_items.*.size_id' => 'required|exists:product_sizes,id',
            'order_items.*.color_id' => 'required|exists:product_colors,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = $request->user();
        $orderNumber = 'ORD-' . strtoupper(uniqid(6)) . '-' . strtoupper(uniqid(6));
        $subtotal = 0;

        // Start transaction
        DB::beginTransaction();

        try {
            // Validate and calculate subtotal from order items
            foreach ($request->order_items as $item) {
                $product = Product::find($item['product_id']);

                if ($product->inventory < $item['quantity']) {
                    return $this->sendError("Insufficient inventory for product: {$product->name}");
                }

                $subtotal += $product->price * $item['quantity'];
            }

            // Create the order with user relationship
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'tax' => $request->get('tax', 0),
                'discount' => $request->get('discount', 0),
                'total' => $subtotal + $request->get('tax', 0) - $request->get('discount', 0),
                'currency' => $request->currency,
                'status' => $request->status,
            ])->load('user');

            // Save order items and update inventory
            foreach ($request->order_items as $item) {
                $product = Product::find($item['product_id']);

                // Update product inventory
                $product->inventory -= $item['quantity'];
                $product->save();

                // Create order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'size_id' => $item['size_id'],
                    'color_id' => $item['color_id'],
                    'price' => $product->price,
                    'total' => $product->price * $item['quantity'],
                ]);
            }

            // Commit transaction
            DB::commit();
            // Format the order payload
            $formattedOrder = [
                'order_id' => (string) $order->id, // Ensure it's serializable
                'order_number' => (string) $order->order_number, // Ensure it's serializable
                'customer' => $order->user->first_name . ' ' . $order->user->last_name,
                'email' => $order->user->email,
                'total' => number_format($order->total, 2, '.', ''),
                'status' => ucfirst($order->status),
                'currency' => strtoupper($order->currency),
                'message' => 'A new order has been placed!',
            ];

            // Broadcast the event using a correct array structure
            broadcast(new NewOrderPlaced($formattedOrder)); // Pass the array directly
            return $this->sendResponse($order->load('items.product'), 'Order created successfully.');
        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();
            return $this->sendError('Order creation failed.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update an existing order.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:orders,id',
            'status' => 'sometimes|required|string|in:pending,processing,completed,cancelled',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $order = Order::find($request->id);

        if (!$order) {
            return $this->sendError('Order not found.');
        }

        // Update fields
        $order->status = $request->get('status', $order->status);
        $order->tax = $request->get('tax', $order->tax);
        $order->discount = $request->get('discount', $order->discount);
        $order->total = $order->subtotal + $order->tax - $order->discount;
        $order->save();

        return $this->sendResponse($order, 'Order updated successfully.');
    }
    public function updateOrderStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:orders,id',
            'status' => 'required|string|in:pending,paid,shipped,cancelled,refunded',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $order = Order::find($request->id);
        $order->status = $request->status;
        $order->save();

        return $this->sendResponse($order, 'Order status updated successfully.');
    }
    public function generateOrderSummary()
    {
        $summary = [
            'total_orders' => Order::count(),
            'total_revenue' => Order::sum('total'),
        ];
        $statuses = Order::distinct()->pluck('status');
        foreach ($statuses as $status) {
            $summary[$status . '_orders'] = Order::where('status', $status)->count();
        }

        return $this->sendResponse($summary, 'Order summary retrieved successfully.');
    }
    /**
     * Delete an order.
     */
    public function destroy(Request $request)
    {
        $id = $request->query('id');
        $user = auth()->user();

        $order = Order::with(['items.product', 'payment', 'shipping'])->find($id);

        if (is_null($order)) {
            return $this->sendError('Order not found.');
        }

        // Check if the authenticated user is authorized to delete the order
        if ($order->user_id !== $user->id) {
            return $this->sendError('Unauthorized to delete this order.', [], 403);
        }

        try {
            DB::beginTransaction();

            // Restore inventory for each order item
            foreach ($order->items as $item) {
                $product = $item->product;

                if ($product) {
                    $product->inventory += $item->quantity; // Restore inventory
                    $product->save();
                }

                $item->delete(); // Deletes each order item
            }

            // Delete related payment and shipping data
            if ($order->payment) {
                $order->payment->delete();
            }

            if ($order->shipping) {
                $order->shipping->delete();
            }

            // Delete the order itself
            $order->delete();

            DB::commit();

            return $this->sendResponse([], 'Order and its related data deleted successfully, and inventory restored.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to delete the order.', ['error' => $e->getMessage()]);
        }
    }

}
