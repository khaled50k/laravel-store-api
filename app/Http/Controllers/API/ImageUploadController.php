<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageUploadController extends BaseController
{
    /**
     * Upload an image for a product.
     */
    public function uploadProductImage(Request $request)
    {
        return $this->uploadImages($request, 'products');
    }

    /**
     * Upload an image for a category.
     */
    public function uploadCategoryImage(Request $request)
    {
        return $this->uploadImage($request, 'categories');
    }

    /**
     * Upload an image for a user avatar.
     */
    public function uploadUserAvatar(Request $request)
    {
        return $this->uploadImage($request, 'avatars');
    }

    /**
     * Remove an image for a product.
     */
    public function removeProductImages(Request $request)
    {
        return $this->deleteImages($request, 'products');
    }

    /**
     * Remove an image for a category.
     */
    public function removeCategoryImage(Request $request)
    {
        return $this->removeImage($request, 'categories');
    }

    /**
     * Remove an image for a user avatar.
     */
    public function removeUserAvatar(Request $request)
    {
        return $this->removeImage($request, 'avatars');
    }

    private function uploadImages(Request $request, $directory)
    {
        $validator = Validator::make($request->all(), [
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max size: 2MB per image
            'color_id' => 'required|exists:product_colors,id', // Ensure the color ID exists
            'product_id' => 'required|exists:products,id', // Ensure the product ID exists
        ]);

        if ($validator->fails()) {
            return ['error' => 'Validation Error.', 'details' => $validator->errors()];
        }
        $uploadedImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Generate a unique file name: product_id-color_id-timestamp.extension
                $newFileName = $request->product_id . '-' . $request->color_id . '-' . time() . '-' . Str::random(8) . '.' . $image->getClientOriginalExtension();

                // Save the file to the directory
                $path = $image->storeAs("uploads/{$directory}", $newFileName, 'public');

                // Save the image details to the database (optional)
                ProductImage::create([
                    'product_id' => $request->product_id,
                    'product_color_id' => $request->color_id,
                    'image_path' => $path,
                ]);

                // Add the uploaded image's public URL to the response array
                $uploadedImages[] = Storage::url($path);
            }
            return $uploadedImages;
        }

        return ['error' => 'No image files found.'];
    }

    private function deleteImages(Request $request, $directory)
    {
        $validator = Validator::make($request->all(), [
            'image_ids' => 'required|array|min:1', // Ensure image_ids is an array with at least one element
            'image_ids.*' => 'distinct|exists:product_images,id', // Validate that each ID is distinct and exists in the product_images table
        ]);

        if ($validator->fails()) {
            return ['error' => 'Validation Error.', 'details' => $validator->errors()];
        }

        $deletedImages = [];

        foreach ($request->image_ids as $imageId) {
            $image = ProductImage::find($imageId);

            if ($image) {
                $fullPath = "public/{$image->image_path}";

                // Delete the file from storage
                if (Storage::exists($fullPath)) {
                    Storage::delete($fullPath);
                }

                // Remove the record from the database
                $image->delete();

                // Add deleted image information to the response
                $deletedImages[] = [
                    'id' => $imageId,
                    'file_path' => Storage::url($image->image_path),
                ];
            }
        }

        if (empty($deletedImages)) {
            return ['error' => 'No images were deleted.'];
        }

        return $deletedImages;
    }

    /**
     * Generic image upload handler.
     */
    private function uploadImage(Request $request, $directory)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif', // Max size: 2MB per image
        ]);

        if ($validator->fails()) {
            return ['error' => 'Validation Error.', 'details' => $validator->errors()];
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $path = $image->store("uploads/{$directory}", 'public'); // Save to storage/public/uploads/{directory}

            return [
                'file_path' => Storage::url($path), // Public URL for the uploaded file
            ];
        }

        return ['error' => 'No image file found.'];
    }

    /**
     * Generic image remove handler.
     */
    private function removeImage(Request $request, $directory)
    {
        $validator = Validator::make($request->all(), [
            'file_path' => 'required|string', // The file path of the image to be removed
        ]);

        if ($validator->fails()) {
            return ['error' => 'Validation Error.', 'details' => $validator->errors()];
        }

        $filePath = $request->file_path; // Get the file path from the request
        $filePath = str_replace('/storage/', '', $filePath); // Remove the '/storage/' prefix
        $fullPath = "public/{$filePath}"; // Construct the full path for the storage system
        if (Storage::exists($fullPath)) {
            Storage::delete($fullPath); // Delete the file from storage
            return [];
        }

        return ['error' => 'File not found.'];
    }
}
