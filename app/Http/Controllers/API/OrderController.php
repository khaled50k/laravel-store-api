<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends BaseController
{
    /**
     * List all orders.
     */
    public function index()
    {
        $orders = Order::with(['items.product', 'items.color', 'items.size', 'payment', 'shipping'])->get();
        return $this->sendResponse($orders, 'All orders retrieved successfully.');
    }



    /**
     * Show details of a specific order.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $id = $request->query('id');

        if ($id) {
            $order = Order::with(['items.product', 'items.color', 'items.size', 'payment', 'shipping'])->where('user_id', $user->id)->find($id);
            if (is_null($order)) {
                return $this->sendError('Order not found.');
            }
        } else {
            $order = Order::with(['items.product', 'items.color', 'items.size', 'payment', 'shipping'])->where('user_id', $user->id)->get();
        }

        return $this->sendResponse($order, 'Order details retrieved successfully.');
    }

    /**
     * Create a new order.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'order_number' => 'required|string|unique:orders,order_number',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $order = Order::create($request->all());

        return $this->sendResponse($order, 'Order created successfully.');
    }

    /**
     * Update an existing order.
     */
    public function update(Request $request)
    {
        $id = $request->query('id');
        $order = Order::find($id);

        if (is_null($order)) {
            return $this->sendError('Order not found.');
        }

        $validator = Validator::make($request->all(), [
            'order_number' => 'sometimes|required|string|unique:orders,order_number,' . $id,
            'subtotal' => 'sometimes|required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'sometimes|required|numeric|min:0',
            'currency' => 'sometimes|required|string|max:3',
            'status' => 'sometimes|required|string|in:pending,processing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $order->update($request->all());

        return $this->sendResponse($order, 'Order updated successfully.');
    }

    /**
     * Delete an order.
     */
    public function destroy(Request $request)
    {  $id = $request->query('id');
        $order = Order::find($id);

        if (is_null($order)) {
            return $this->sendError('Order not found.');
        }

        $order->delete();

        return $this->sendResponse([], 'Order deleted successfully.');
    }
}
