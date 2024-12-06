<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Order;
use App\Models\Payments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentsController extends BaseController
{
    /**
     * List all payments.
     */
    public function index()
    {
        $payments = Payments::with('order')->get();
        return $this->sendResponse($payments, 'Payments retrieved successfully.');
    }

    /**
     * Show a specific payment.
     */
    public function show(Request $request)
    {
        $id = $request->query('id');
        $order_id = $request->query('oid');
        $user = $request->user();

        if ($id) {
            // Retrieve a specific order item by ID
            $payment = Payments::with(['order'])
                ->whereHas('order', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->where('id', $id)
                ->first();


        } elseif ($order_id) {
            $payment = Payments::with(['order'])
                ->whereHas('order', function ($query) use ($user, $order_id) {
                    $query->where('user_id', $user->id)->where('id', $order_id);
                })
                ->first();
        } else {
            return $this->sendError('Either id or order_id is required.');
        }

        if (is_null($payment)) {
            return $this->sendError('Payment not found.');
        }

        return $this->sendResponse($payment, 'Payment details retrieved successfully.');
    }

    /**
     * Create a new payment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'payment_gateway' => 'required|string|max:50',
            'transaction_id' => 'required|string|unique:payments,transaction_id',
            'status' => 'required|string|in:pending,completed,failed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $order = Order::find($request->order_id);
        $amount = $order->total;

        $payment = Payments::create(array_merge($request->all(), ['amount' => $amount]));

        return $this->sendResponse($payment, 'Payment created successfully.');
    }

    /**
     * Update an existing payment.
     */
    public function update(Request $request)
    {        $id = $request->query('id');
        $payment = Payments::find($id);

        if (is_null($payment)) {
            return $this->sendError('Payment not found.');
        }

        $validator = Validator::make($request->all(), [
            'payment_gateway' => 'sometimes|required|string|max:50',
            'transaction_id' => 'sometimes|required|string|unique:payments,transaction_id,' . $id,
            'status' => 'sometimes|required|string|in:pending,completed,failed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $payment->update($request->all());

        return $this->sendResponse($payment, 'Payment updated successfully.');
    }

    /**
     * Delete a payment.
     */
    public function destroy(Request $request)
    {        $id = $request->query('id');
        $payment = Payments::find($id);

        if (is_null($payment)) {
            return $this->sendError('Payment not found.');
        }

        $payment->delete();

        return $this->sendResponse([], 'Payment deleted successfully.');
    }
}
