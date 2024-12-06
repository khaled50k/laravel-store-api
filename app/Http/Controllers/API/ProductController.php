<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Product;
use Illuminate\Support\Facades\Validator; 
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Product::with('categories', 'colors.images');
    
        // Apply filters
        if ($request->has('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('id', $request->query('category_id'));
            });
        }
    
        if ($request->has('min_price')) {
            $query->where('price', '>=', (float)$request->query('min_price'));
        }
    
        if ($request->has('max_price')) {
            $query->where('price', '<=', (float)$request->query('max_price'));
        }
    
        if ($request->has('search')) {
            $searchTerm = $request->query('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }
    
        if ($request->has('min_inventory')) {
            $query->where('inventory', '>=', $request->query('min_inventory'));
        }
    
        if ($request->has('max_inventory')) {
            $query->where('inventory', '<=', $request->query('max_inventory'));
        }
    
        if ($request->has('sku')) {
            $query->where('sku', $request->query('sku'));
        }
    
        // Apply sorting
        if ($request->has('sort_by')) {
            $query->orderBy(
                $request->query('sort_by'),
                $request->query('sort_order', 'asc')
            );
        }
    
        if ($request->has('sort_by_price')) {
            $query->orderBy('price', $request->query('sort_order_price', 'asc'));
        }
    
        if ($request->has('sort_by_inventory')) {
            $query->orderBy('inventory', $request->query('sort_order_inventory', 'asc'));
        }
    
        // Paginate the results
        $products = $query->paginate($request->query('per_page', 10));
    
        $products->transform(function ($product) {
            $product->price = (float)$product->price;
            return $product;
        });
    
        return $this->sendResponse($products, 'Products retrieved successfully.');
    }

    public function show(Request $request)
    {
        // Validate ID
        $validator = Validator::make($request->query(), [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $product = Product::with('categories', 'colors.images')->find($request->query('id'));

        if (!$product) {
            return $this->sendError('Product not found.');
        }

        return $this->sendResponse(new ProductResource($product), 'Product retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'inventory' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products|max:100',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
    
        // Create product
        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'inventory' => $request->inventory,
            'sku' => $request->sku,
        ]);
    
        // Attach categories if provided
        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }
    
        // Load related data and return response
        $product->load('categories');
    
        return $this->sendResponse(new ProductResource($product), 'Product created successfully.');
    }
    
    public function update(Request $request)
    {
        // Validate ID
        $validator = Validator::make($request->query(), [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $product = Product::find($request->query('id'));

        if (!$product) {
            return $this->sendError('Product not found.');
        }
    
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'sku' => "sometimes|required|string|unique:products,sku,{$request->query('id')}|max:100",
            'categories' => 'nullable|array', // Array of category IDs
            'categories.*' => 'exists:categories,id',
            'colors' => 'nullable|array', // Array of color objects
            'colors.*.id' => 'nullable|exists:product_colors,id',
            'colors.*.color_name' => 'required_with:colors|string|max:50',
            'colors.*.color_code' => 'required_with:colors|string|max:7',
            'colors.*.images' => 'nullable|array',
            'colors.*.images.*' => 'string', // Assuming image paths are strings
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
    
        // Update product details
        $product->update($request->only(['name', 'price', 'sku']));
    
        // Sync categories if provided
        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }
    
        // Handle colors and their images
        if ($request->has('colors')) {
            foreach ($request->colors as $colorData) {
                // Update existing color or create a new one
                $color = $product->colors()->updateOrCreate(
                    ['id' => $colorData['id'] ?? null],
                    ['color_name' => $colorData['color_name'], 'color_code' => $colorData['color_code']]
                );
    
                // Update or recreate images for the color
                if (isset($colorData['images'])) {
                    $color->images()->delete(); // Remove existing images
                    foreach ($colorData['images'] as $image) {
                        $color->images()->create(['image_path' => $image]);
                    }
                }
            }
        }
    
        return $this->sendResponse($product->load('categories', 'colors.images'), 'Product updated successfully.');
    }
    
    public function destroy(Request $request)
    {
        // Validate ID
        $validator = Validator::make($request->query(), [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $product = Product::find($request->query('id'));

        if (!$product) {
            return $this->sendError('Product not found.');
        }

        // Detach related categories
        $product->categories()->detach();

        // Delete related colors and their images
        foreach ($product->colors as $color) {
            $color->images()->delete(); // Delete images for the color
            $color->delete(); // Delete the color itself
        }

        // Delete the product
        $product->delete();

        return $this->sendResponse([], 'Product deleted successfully.');
    }
}