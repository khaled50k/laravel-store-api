<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\ProductColor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class ProductColorController extends BaseController
{
    /**
     * List all colors for a specific product.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'pid' => 'required|integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $colors = ProductColor::where('product_id', $request->query('pid'))
            ->with('images')
            ->get();

        return $this->sendResponse($colors, 'Product colors retrieved successfully.');
    }

    /**
     * Create a new product color.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'color_name' => 'required|string|max:50',
            'color_code' => 'sometimes|required|string|max:7',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $color = ProductColor::create($request->only(['product_id', 'color_name', 'color_code']));

        return $this->sendResponse($color, 'Product color created successfully.');
    }

    /**
     * Update an existing product color.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:product_colors,id',
            'color_name' => 'sometimes|required|string|max:50',
            'color_code' => 'sometimes|required|string|max:7',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $color = ProductColor::find($request->input('id'));

        if (!$color) {
            return $this->sendError('Product color not found.');
        }

        $color->update($request->only(['color_name', 'color_code']));

        return $this->sendResponse($color, 'Product color updated successfully.');
    }

    /**
     * Delete a product color and its related images.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'id' => 'required|integer|exists:product_colors,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $color = ProductColor::find($request->query('id'));

        if (!$color) {
            return $this->sendError('Product color not found.');
        }

        // Delete related images
        $color->images->each(function ($image) {
            $imagePath = "public/{$image->image_path}";
            if (Storage::exists($imagePath)) {
                Storage::delete($imagePath);
            }
            $image->delete();
        });

        $color->delete();

        return $this->sendResponse([], 'Product color and its images deleted successfully.');
    }
}
