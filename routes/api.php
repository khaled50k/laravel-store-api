<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{
    CategoryController,
    ImageUploadController,
    OrderController,
    OrderItemController,
    PasswordResetController,
    PaymentsController,
    PayPalController,
    ProductColorController,
    RegisterController,
    ProductController,
    ProductImageController,
    ProductSizeController,
    ShippingsController,
    UserController
};

// Test Route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});

// Authentication Routes
Route::controller(RegisterController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
});

// Authenticated Routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Admin Routes
    Route::prefix('admin')->group(function () {
        // Product Routes
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::post('/', [ProductController::class, 'store']);
            Route::get('/details', [ProductController::class, 'show']); // Uses ?id= for product details
            Route::put('/', [ProductController::class, 'update']); // Uses ?id= for updates
            Route::delete('/', [ProductController::class, 'destroy']); // Uses ?id= for deletion
        });
        Route::prefix('user')->group(function () {

            Route::delete('/', [UserController::class, 'disableUser']); // Get user data
            Route::get('/', [UserController::class, 'getAllUsers']); // Get user data

        });

        Route::middleware(['auth:sanctum'])->prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']); // Show order details
            Route::get('/summary', [OrderController::class, 'generateOrderSummary']); // Show order details
            Route::put('/status', [OrderController::class, 'updateOrderStatus']); // Show order details
        });

        // Product Colors Routes
        Route::prefix('product-colors')->group(function () {
            Route::get('/', [ProductColorController::class, 'index']); // Uses ?product_id= for color list
            Route::post('/', [ProductColorController::class, 'store']);
            Route::get('/details', [ProductColorController::class, 'show']); // Uses ?id= for color details
            Route::put('/', [ProductColorController::class, 'update']); // Uses ?id= for color updates
            Route::delete('/', [ProductColorController::class, 'destroy']); // Uses ?id= for color deletion
        });

        // Product Images Routes
        Route::prefix('product-images')->group(function () {
            Route::get('/', [ProductImageController::class, 'index']); // Uses ?product_id= for image list
            Route::post('/', [ProductImageController::class, 'store']);
            Route::delete('/', [ProductImageController::class, 'destroy']); // Uses ?id= for image deletion
        });

        // Product Sizes Routes
        Route::prefix('product-sizes')->group(function () {
            Route::get('/', [ProductSizeController::class, 'index']); // Uses ?product_id= for size list
            Route::post('/', [ProductSizeController::class, 'store']);
            Route::delete('/', [ProductSizeController::class, 'destroy']); // Uses ?id= for size deletion
        });

        // Category Routes
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::post('/', [CategoryController::class, 'store']);
            Route::get('/details', [CategoryController::class, 'show']); // Uses ?id= for category details
            Route::put('/', [CategoryController::class, 'update']); // Uses ?id= for updates
            Route::delete('/', [CategoryController::class, 'destroy']); // Uses ?id= for deletion
        });

        // Image Upload Routes
        Route::prefix('uploads')->group(function () {
            Route::post('/products', [ImageUploadController::class, 'uploadProductImage']);
            Route::post('/categories', [ImageUploadController::class, 'uploadCategoryImage']);
            Route::delete('/categories', [ImageUploadController::class, 'removeCategoryImage']);
            Route::post('/users/avatar', [ImageUploadController::class, 'uploadUserAvatar']);
        });
    });
});
Route::prefix('/v1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/product-sizes', [ProductSizeController::class, 'index']); // Uses ?product_id= for size list
    Route::get('/product-images', [ProductImageController::class, 'index']); // Uses ?product_id= for image list
    Route::get('/product-colors', [ProductColorController::class, 'index']); // Uses ?product_id= for color list

});
// Add a closing statement to properly end the routes file
Route::middleware(['auth:sanctum'])->prefix('user')->group(function () {
    Route::get('/data', [UserController::class, 'getUserData']); // Get user data
    Route::put('/profile', [UserController::class, 'updateProfile']); // Update user profile
    Route::post('/avatar', [UserController::class, 'uploadAvatar']); // Upload avatar
    Route::delete('/avatar', [UserController::class, 'deleteAvatar']); // Delete avatar



});
Route::middleware(['auth:sanctum'])->prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'show']); // Show order details
    Route::post('/', [OrderController::class, 'store']); // Create a new order
    Route::put('/', [OrderController::class, 'update']); // Update an order
    Route::delete('/', [OrderController::class, 'destroy']); // Delete an order
});
Route::middleware(['auth:sanctum'])->prefix('order-items')->group(function () {
    Route::get('/', [OrderItemController::class, 'show']); // Show details of a specific order item
    Route::post('/', [OrderItemController::class, 'store']); // Add an item to an order
    Route::put('/', [OrderItemController::class, 'update']); // Update an order item
    Route::delete('/', [OrderItemController::class, 'destroy']); // Delete an order item
});
Route::middleware(['auth:sanctum'])->prefix('shippings')->group(function () {
    Route::get('/', [ShippingsController::class, 'show']); // Show specific shipping details
    Route::post('/', [ShippingsController::class, 'store']); // Create new shipping details
    Route::put('/', [ShippingsController::class, 'update']); // Update shipping details
    Route::delete('/', [ShippingsController::class, 'destroy']); // Delete shipping details
});
Route::middleware(['auth:sanctum'])->prefix('payments')->group(function () {
    Route::get('/', [PaymentsController::class, 'show']); // Show details of a specific payment
    Route::post('/', [PaymentsController::class, 'store']); // Create a new payment
    Route::put('/', [PaymentsController::class, 'update']); // Update a payment
    Route::delete('/', [PaymentsController::class, 'destroy']); // Delete a payment
   
});
Route::middleware(['auth:sanctum'])->prefix('paypal')->group(function () {
    Route::post('/create', [PayPalController::class, 'createPayment']);
    Route::post('/capture', [PayPalController::class, 'capturePayment']);
    Route::get('/cancel', [PayPalController::class, 'cancelPayment'])->name('paypal.cancel');
    Route::get('/success', [PayPalController::class, 'successPayment'])->name('paypal.success');

});


Route::prefix('password')->group(function () {
    Route::post('/reset-link', [PasswordResetController::class, 'sendResetLink']); // Send reset link
    Route::post('/reset', [PasswordResetController::class, 'resetPassword']); // Reset password
});