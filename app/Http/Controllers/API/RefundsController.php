<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Refunds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RefundsController extends BaseController
{
    /**
     * List all refunds.
     */
    public function index()
    {
        $refunds = Refunds::with(['order', 'payment'])->get();
        return $this->sendResponse($refunds, 'Refunds retrieved successfully.');
    }

    /**
     * Show details of a specific refund.
     */
    public function show($id)
    {
        $refund = Refunds::with(['order', 'payment'])->find($id);

        if (is_null($refund)) {
            return $this->sendError('Refund not found.');
        }

        return $this->sendResponse($refund, 'Refund details retrieved successfully.');
    }

    /**
     * Create a new refund.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'payment_id' => 'required|exists:payments,id',
            'amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $refund = Refunds::create($request->all());

        return $this->sendResponse($refund, 'Refund created successfully.');
    }

    /**
     * Update an existing refund.
     */
    public function update(Request $request, $id)
    {
        $refund = Refunds::find($id);

        if (is_null($refund)) {
            return $this->sendError('Refund not found.');
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $refund->update($request->all());

        return $this->sendResponse($refund, 'Refund updated successfully.');
    }

    /**
     * Delete a refund.
     */
    public function destroy($id)
    {
        $refund = Refunds::find($id);

        if (is_null($refund)) {
            return $this->sendError('Refund not found.');
        }

        $refund->delete();

        return $this->sendResponse([], 'Refund deleted successfully.');
    }
}
