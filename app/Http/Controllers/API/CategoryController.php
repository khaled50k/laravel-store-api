<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\ImageUploadController;

class CategoryController extends BaseController
{
    protected $imageUploadController;

    public function __construct()
    {
        $this->imageUploadController = new ImageUploadController();
    }

    /**
     * List all categories.
     */
    public function index(Request $request)
    {
        $id = $request->query('id');
        $name = $request->query('name');

        if ($id) {
            $category = Category::with('products')->find($id);
        } elseif ($name) {
            $category = Category::with('products')->where('name', $name)->first();
        } else {
            $categories = Category::with('products')->get();
            return $this->sendResponse(CategoryResource::collection($categories), 'Categories retrieved successfully.');
        }

        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        return $this->sendResponse($category, 'Category retrieved successfully.');
    }

    /**
     * Show a specific category.
     */
    public function show(Request $request)
    {
        $id = $request->query('id');
        $category = Category::with('products')->find($id);

        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        return $this->sendResponse($category, 'Category retrieved successfully.');
    }

    /**
     * Create a new category.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $category = Category::create($request->only(['name', 'description', 'image']));
        return $this->sendResponse($category, 'Category created successfully.');
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $category->update($request->only(['name', 'description', 'image']));
        return $this->sendResponse($category, 'Category updated successfully.');
    }

    /**
     * Delete a category.
     */
    public function destroy($id)
    {
        $category = Category::find($id);

        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        // Detach related products before deleting
        $category->products()->detach();
        $category->delete();

        return $this->sendResponse([], 'Category deleted successfully.');
    }

    public function uploadCategoryImage(Request $request)
    {
        $result = $this->imageUploadController->uploadCategoryImage($request);

        if (array_key_exists('error', $result)) {
            return $this->sendError($result['error']);
        }

        return $this->sendResponse($result, 'Category image uploaded successfully.');
    }

    public function deleteCategoryImage(Request $request)
    {
        $category = Category::where('image', $request->file_path)->first();

        if (is_null($category)) {
            return $this->sendError('Category not found.');
        }

        $result = $this->imageUploadController->removeCategoryImage($request);

        if (array_key_exists('error', $result)) {
            return $this->sendError($result['error']);
        }

        $category->update(['image' => null]);

        return $this->sendResponse([], 'Category image deleted successfully.');
    }
}
