<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageUploadController extends Controller
{
    /**
     * Serve uploaded images.
     */
    public function serveImage(Request $request, $directory, $filename)
    {
        $path = "uploads/{$directory}/{$filename}";

        // Check if the file exists in the storage
        if (Storage::disk('public')->exists($path)) {
            return response()->file(storage_path("app/public/{$path}"));
        }

        return response()->json(['error' => 'Image not found'], 404);
    }

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
     * Remove images.
     */
    public function removeProductImages(Request $request)
    {
        return $this->deleteImages($request, 'products');
    }

    public function removeCategoryImage(Request $request)
    {
        return $this->removeImage($request, 'categories');
    }

    public function removeUserAvatar(Request $request)
    {
        return $this->removeImage($request, 'avatars');
    }

    private function uploadImages(Request $request, $directory)
    {
        $validator = Validator::make($request->all(), [
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'color_id' => 'required|exists:product_colors,id',
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return ['error' => 'Validation Error.', 'details' => $validator->errors()];
        }

        $uploadedImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $newFileName = 'ibdaatec-' . $request->product_id . '-' . $request->color_id . '-' . time() . '-' . Str::random(8) . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs("uploads/{$directory}", $newFileName, 'public');
                ProductImage::create([
                    'product_id' => $request->product_id,
                    'product_color_id' => $request->color_id,
                    'image_path' =>  $directory . '/' . $newFileName,
                ]);

                $uploadedImages[] = Storage::url($path);
            }
            return $uploadedImages;
        }

        return ['error' => 'No image files found.'];
    }

    private function deleteImages(Request $request, $directory)
    {
        $validator = Validator::make($request->all(), [
            'image_ids' => 'required|array|min:1',
            'image_ids.*' => 'distinct|exists:product_images,id',
        ]);

        if ($validator->fails()) {
            return [
                'error' => 'Validation Error.',
                'details' => $validator->errors(),
            ];
        }

        $deletedImages = [];
        $failedDeletions = [];

        foreach ($request->image_ids as $imageId) {
            $image = ProductImage::find($imageId);

            if ($image) {
                // Correct file path by removing "/images/"
                $path = str_replace('/images/', '', $image->image_path);
                $storagePath = "uploads/{$path}";

                // Check if the file exists in storage
                if (Storage::disk('public')->exists($storagePath)) {
                    // Delete the file from storage
                    if (Storage::disk('public')->delete($storagePath)) {
                        // Delete the database record
                        $image->delete();

                        $deletedImages[] = [
                            'id' => $imageId,
                            'file_path' => url("storage/{$storagePath}"),
                        ];
                    } else {
                        $failedDeletions[] = [
                            'error' => 'Failed to delete image from storage.',
                        ];
                    }
                } else {
                    $failedDeletions[] = [
                        'error' => 'Image not found in storage.',
                    ];
                }
            } else {
                $failedDeletions[] = [
                    'id' => $imageId,
                    'error' => 'Image not found in database.',
                ];
            }
        }

        // Final response format
        if (!empty($deletedImages)) {
            if (!empty($failedDeletions)) {
                return [
                    'deleted' => $deletedImages,
                    'failed' => $failedDeletions,
                ];
            } else {
                return $deletedImages;
            }
        } else {
            return [
                'error' => 'No images were deleted.',
                'details' => $failedDeletions,
            ];
        }
    }


    private function uploadImage(Request $request, $directory)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return ['error' => 'Validation Error.', 'details' => $validator->errors()];
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $newFileName = 'ibdaatec-' . Str::random(5) . '-' . Str::random(5) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs("uploads/{$directory}", $newFileName, 'public');
            return [
                'file_path' =>$directory . '/' . $newFileName,
            ];
        }

        return ['error' => 'No image file found.'];
    }

    private function removeImage(Request $request, $directory)
    {
        $validator = Validator::make($request->all(), [
            'file_path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ['error' => 'Validation Error.', 'details' => $validator->errors()];
        }

  
        $path = str_replace('/images/', '', $request->file_path);
        $storagePath = "uploads/{$path}";

        if (Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->delete($storagePath);
            return [];
        }

        return ['error' => 'File not found.'];
    }
}
