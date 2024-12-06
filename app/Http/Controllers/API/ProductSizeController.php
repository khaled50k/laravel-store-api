<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\ProductSize;
use Illuminate\Support\Facades\Validator;

class ProductSizeController extends BaseController
{
    /**
     * List all sizes for a specific product.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'pid' => 'required_without:sid|integer|exists:products,id',
            'sid' => 'required_without:pid|integer|exists:product_sizes,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if ($request->query('pid')) {
            $sizes = ProductSize::where('product_id', $request->query('pid'))->get();
        } else {
            $sizes = ProductSize::where('id', $request->query('sid'))->get();
        }

        return $this->sendResponse($sizes, 'Product sizes retrieved successfully.');
    }

    /**
     * Store a new product size.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'sizes' => 'required|array',
            'sizes.*' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $sizes = collect($request->input('sizes'))->map(function ($size) use ($request) {
            return ProductSize::create([
                'product_id' => $request->input('product_id'),
                'size' => $size,
            ]);
        });

        return $this->sendResponse($sizes, 'Product sizes created successfully.');
    }



    /**
     * Delete a product size.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'pid' => 'required_without:sid|integer|exists:products,id',
            'sid' => 'required_without:pid|integer|exists:product_sizes,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if ($request->query('pid')) {
            $sizes = ProductSize::where('product_id', $request->query('pid'))->get();
        } else {
            $sizes = ProductSize::where('id', $request->query('sid'))->get();
        }

        if ($sizes->isEmpty()) {
            return $this->sendError('Product size not found.');
        }

        $sizes->each->delete();

        return $this->sendResponse([], 'Product size(s) deleted successfully.');
    }
}
