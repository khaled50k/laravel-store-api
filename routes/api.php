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

// Authenticated Admin Routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    // Products Management
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/details', [ProductController::class, 'show']);
        Route::put('/', [ProductController::class, 'update']);
        Route::delete('/', [ProductController::class, 'destroy']);
    });

    // Users Management
    Route::prefix('user')->group(function () {
        Route::delete('/', [UserController::class, 'disableUser']);
        Route::get('/', [UserController::class, 'getAllUsers']);
    });

    // Orders Management
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/summary', [OrderController::class, 'generateOrderSummary']);
        Route::put('/status', [OrderController::class, 'updateOrderStatus']);
    });

    // Product Colors
    Route::prefix('product-colors')->group(function () {
        Route::get('/', [ProductColorController::class, 'index']);
        Route::post('/', [ProductColorController::class, 'store']);
        Route::get('/details', [ProductColorController::class, 'show']);
        Route::put('/', [ProductColorController::class, 'update']);
        Route::delete('/', [ProductColorController::class, 'destroy']);
    });

    // Product Images
    Route::prefix('product-images')->group(function () {
        Route::get('/', [ProductImageController::class, 'index']);
        Route::post('/', [ProductImageController::class, 'store']);
        Route::delete('/', [ProductImageController::class, 'destroy']);
    });

    // Product Sizes
    Route::prefix('product-sizes')->group(function () {
        Route::get('/', [ProductSizeController::class, 'index']);
        Route::post('/', [ProductSizeController::class, 'store']);
        Route::delete('/', [ProductSizeController::class, 'destroy']);
    });

    // Categories Management
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/', [CategoryController::class, 'update']);
        Route::delete('/', [CategoryController::class, 'destroy']);
        Route::post('/image', [CategoryController::class, 'uploadCategoryImage']);
        Route::delete('/image', [CategoryController::class, 'deleteCategoryImage']);
    });
});

// Versioned API
Route::prefix('/v1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/product-sizes', [ProductSizeController::class, 'index']);
    Route::get('/product-images', [ProductImageController::class, 'index']);
    Route::get('/product-colors', [ProductColorController::class, 'index']);
});

// Authenticated User Routes
Route::middleware(['auth:sanctum'])->prefix('user')->group(function () {
    Route::get('/data', [UserController::class, 'getUserData']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::post('/avatar', [UserController::class, 'uploadAvatar']);
    Route::delete('/avatar', [UserController::class, 'deleteAvatar']);
});

// Orders Management
Route::middleware(['auth:sanctum'])->prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'show']);
    Route::post('/', [OrderController::class, 'store']);
    Route::put('/', [OrderController::class, 'update']);
    Route::delete('/', [OrderController::class, 'destroy']);
});

// Order Items
Route::middleware(['auth:sanctum'])->prefix('order-items')->group(function () {
    Route::get('/', [OrderItemController::class, 'show']);
    Route::post('/', [OrderItemController::class, 'store']);
    Route::put('/', [OrderItemController::class, 'update']);
    Route::delete('/', [OrderItemController::class, 'destroy']);
});

// Shipping Management
Route::middleware(['auth:sanctum'])->prefix('shippings')->group(function () {
    Route::get('/', [ShippingsController::class, 'show']);
    Route::post('/', [ShippingsController::class, 'store']);
    Route::put('/', [ShippingsController::class, 'update']);
    Route::delete('/', [ShippingsController::class, 'destroy']);
});

// Payments
Route::middleware(['auth:sanctum'])->prefix('payments')->group(function () {
    Route::get('/', [PaymentsController::class, 'show']);
    Route::post('/', [PaymentsController::class, 'store']);
    Route::put('/', [PaymentsController::class, 'update']);
    Route::delete('/', [PaymentsController::class, 'destroy']);
});

// PayPal Payment Integration
Route::middleware(['auth:sanctum'])->prefix('paypal')->group(function () {
    Route::post('/create', [PayPalController::class, 'createPayment']);
    Route::post('/capture', [PayPalController::class, 'capturePayment']);
    Route::get('/cancel', [PayPalController::class, 'cancelPayment'])->name('paypal.cancel');
    Route::get('/success', [PayPalController::class, 'successPayment'])->name('paypal.success');
});

// Password Reset
Route::prefix('password')->group(function () {
    Route::post('/reset-link', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/reset', [PasswordResetController::class, 'resetPassword']);
});
Route::prefix('images')->group(function () {
    Route::get('/{directory}/{filename}', [ImageUploadController::class, 'serveImage'])
        ->where(['directory' => '[a-zA-Z0-9_-]+', 'filename' => '.+']);
});