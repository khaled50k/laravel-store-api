<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderItemController extends BaseController
{
    /**
     * List all order items for a specific order.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $orderItems = OrderItem::where('order_id', $request->query('order_id'))->with('product', 'size', 'color')->get();
        return $this->sendResponse($orderItems, 'Order items retrieved successfully.');
    }

    /**
     * Show a specific order item.
     */
    public function show(Request $request)
    {
        $id = $request->query('id');
        $oid = $request->query('oid');
        $user = $request->user();

        if ($id) {
            // Retrieve a specific order item by ID
            $orderItem = OrderItem::with(['product', 'color', 'size'])
                ->whereHas('order', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->where('id', $id)
                ->first();

            if (is_null($orderItem)) {
                return $this->sendError('Order item not found.');
            }

            return $this->sendResponse($orderItem, 'Order item retrieved successfully.');
        } elseif ($oid) {
            // Retrieve all order items for a specific order
            $orderItems = OrderItem::with(['product', 'color', 'size'])
                ->whereHas('order', function ($query) use ($user, $oid) {
                    $query->where('user_id', $user->id)->where('id', $oid);
                })
                ->get();

            if ($orderItems->isEmpty()) {
                return $this->sendError('Order items not found.');
            }

            return $this->sendResponse($orderItems, 'Order items retrieved successfully.');
        } else {
            // Retrieve all order items for the authenticated user
            $orderItems = OrderItem::with(['product', 'color', 'size'])
                ->whereHas('order', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();

            if ($orderItems->isEmpty()) {
                return $this->sendError('No order items found for the authenticated user.');
            }

            return $this->sendResponse($orderItems, 'Order items retrieved successfully.');
        }
    }


    /**
     * Add an item to an order.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'color_id' => 'required|exists:product_colors,id',
            'size_id' => 'required|exists:product_sizes,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Fetch the product to get its price
        $product = Product::find($request->product_id);
        if (!$product) {
            return $this->sendError('Product not found.');
        }

        // Validate that the color belongs to the product
        $color = $product->colors()->where('id', $request->color_id)->first();
        if (!$color) {
            return $this->sendError('Invalid color for the selected product.');
        }

        // Validate that the size belongs to the product
        $size = $product->sizes()->where('id', $request->size_id)->first();
        if (!$size) {
            return $this->sendError('Invalid size for the selected product.');
        }

        // Calculate price and total
        $price = $product->price;
        $quantity = $request->quantity;
        $total = $price * $quantity;

        // Create the order item
        $orderItem = OrderItem::create([
            'order_id' => $request->order_id,
            'product_id' => $request->product_id,
            'color_id' => $request->color_id,
            'size_id' => $request->size_id,
            'quantity' => $quantity,
            'price' => $price,
            'total' => $total,
        ]);

        return $this->sendResponse($orderItem, 'Order item added successfully.');
    }

    /**
     * Update an order item.
     */
    public function update(Request $request)
    {
        $id = $request->query('id');
        $orderItem = OrderItem::find($id);

        if (is_null($orderItem)) {
            return $this->sendError('Order item not found.');
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|required|integer|min:1',
            'color_id' => 'sometimes|required|exists:product_colors,id',
            'size_id' => 'sometimes|required|exists:product_sizes,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Update the order item's attributes
        if ($request->has('quantity')) {
            $orderItem->quantity = $request->quantity;
        }

        if ($request->has('color_id')) {
            // Validate the color belongs to the product
            $color = $orderItem->product->colors()->where('id', $request->color_id)->first();
            if (!$color) {
                return $this->sendError('Invalid color for the selected product.');
            }
            $orderItem->color_id = $request->color_id;
        }

        if ($request->has('size_id')) {
            // Validate the size belongs to the product
            $size = $orderItem->product->sizes()->where('id', $request->size_id)->first();
            if (!$size) {
                return $this->sendError('Invalid size for the selected product.');
            }
            $orderItem->size_id = $request->size_id;
        }

        // Recalculate the total based on the updated quantity
        $orderItem->total = $orderItem->price * $orderItem->quantity;

        // Save the updates
        $orderItem->save();

        // Reload with relationships
        $orderItem->load(['product', 'color', 'size']);

        return $this->sendResponse($orderItem, 'Order item updated successfully.');
    }

    /**
     * Remove an order item.
     */
    public function destroy(Request $request)
    {
        $id = $request->query('id');
        $orderItem = OrderItem::find($id);

        if (is_null($orderItem)) {
            return $this->sendError('Order item not found.');
        }

        $orderItem->delete();

        return $this->sendResponse([], 'Order item deleted successfully.');
    }
}
