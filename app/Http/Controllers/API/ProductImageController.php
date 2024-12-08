<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\ImageUploadController;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Validator;
class ProductImageController extends BaseController
{
    protected $imageUploadController;

    public function __construct()
    {
        $this->imageUploadController = new ImageUploadController();
    }

    /**
     * List all images for a specific product or color.
     */
    public function index(Request $request)
    {

        
        $validator = Validator::make($request->query(), [
            'pid' => 'required_without:cid|integer|exists:products,id',
            'cid' => 'required_without:pid|integer|exists:product_colors,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if ($request->query('pid')) {
            $query = ProductImage::where('product_id', $request->query('pid'))->with('color');
        } else {
            $query = ProductImage::where('product_color_id', $request->query('cid'))->with('color');
        }

        
        $images = $query->get();
        $formattedImages = $images->map(function ($image) {
            return [
                'id' => $image->id,
                'image_path' => $image->image_path,
                'color' => [
                    'name' => $image->color->color_name,
                    'code' => $image->color->color_code,
                ],
            ];
        });

        return $this->sendResponse($formattedImages, 'Product images retrieved successfully.');
    }

    /**
     * Show a specific product image.
     */
   

    /**
     * Store a new product image.
     */
    public function store(Request $request)
    {
        $images = $this->imageUploadController->uploadProductImage($request);
        return $this->sendResponse($images, 'Product image uploaded successfully.');
    }

    /**
     * Delete a product image.
     */
    public function destroy(Request $request)
    {
        $images = $this->imageUploadController->removeProductImages($request);
        if (isset($images['error'])) {
            return $this->sendError($images['error'], $images['details']);
        }
        if ($images) {
            return $this->sendResponse([], 'Product images deleted successfully.');
        }
        return $this->sendError('No images were deleted.');
    }
}
