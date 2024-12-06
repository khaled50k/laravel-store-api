<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Shippings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingsController extends BaseController
{
    /**
     * List all shipping details.
     */
    public function index()
    {
        $shippings = Shippings::with('order')->get();
        return $this->sendResponse($shippings, 'Shipping details retrieved successfully.');
    }

    /**
     * Show details of specific shipping.
     */
    public function show(Request $request)
    {
        $id = $request->query('id');
        $sid = $request->query('sid');
        $user = $request->user();

        if ($sid) {
            $shipping = Shippings::with(['order'])->where('id', $sid)->first();
        } elseif ($id) {
            $shipping = Shippings::with(['order'])
                ->whereHas('order', function ($query) use ($user,$id) {
                    $query->where('user_id', $user->id)->where('id', $id);
                })->first();
        } else {
            return $this->sendError('Either id or sid is required.');
        }

        if (is_null($shipping)) {
            return $this->sendError('Shipping details not found.');
        }

        return $this->sendResponse($shipping, 'Shipping details retrieved successfully.');
    }

    /**
     * Create new shipping details.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        // Check if a shipping record already exists for the order
        if (Shippings::where('order_id', $request->order_id)->exists()) {
            return $this->sendError('Shipping record already exists for this order.');
        }
        $shipping = Shippings::create($request->all());

        return $this->sendResponse($shipping, 'Shipping details created successfully.');
    }

    /**
     * Update existing shipping details.
     */
    public function update(Request $request)
    {
        $id = $request->query('id');
        $shipping = Shippings::find($id);

        if (is_null($shipping)) {
            return $this->sendError('Shipping details not found.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'sometimes|required|string|max:20',
            'country' => 'sometimes|required|string|max:100',
            'phone' => 'sometimes|required|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $shipping->update($request->all());

        return $this->sendResponse($shipping, 'Shipping details updated successfully.');
    }

    /**
     * Delete shipping details.
     */
    public function destroy(Request $request)
    {
        $id = $request->query('id');

        $shipping = Shippings::find($id);

        if (is_null($shipping)) {
            return $this->sendError('Shipping details not found.');
        }

        $shipping->delete();

        return $this->sendResponse([], 'Shipping details deleted successfully.');
    }
}
