<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\NominationController;
use App\Http\Controllers\ConciergeController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\EventPackageController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public payment callback routes (called from Stripe/browser without auth)
Route::post('/payment/success/{id}', [PaymentController::class, 'paymentSuccess']);
Route::post('/payment/failed/{id}', [PaymentController::class, 'paymentFailed']);
Route::get('/payment/mock-checkout/{id}', [PaymentController::class, 'mockCheckout']);

// Stripe webhook (must be public, no auth)
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handleWebhook']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Search
    Route::get('/search', [SearchController::class, 'search']);
    Route::get('/search/suggestions', [SearchController::class, 'suggestions']);
    Route::get('/search/test', [SearchController::class, 'test']);
    
    // Image Upload
    Route::post('/upload/image', [ImageUploadController::class, 'upload']);
    Route::post('/upload/pdf', [ImageUploadController::class, 'uploadPDF']);

    // Posts (News Feed)
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{id}', [PostController::class, 'show']);

    // Marketplace
    Route::get('/offers', [OfferController::class, 'index']);
    Route::get('/offers/{id}', [OfferController::class, 'show']);

    // Awards
    Route::get('/awards', [AwardController::class, 'index']);
    Route::get('/awards/{id}', [AwardController::class, 'show']);

    // Nominations
    Route::post('/nominations', [NominationController::class, 'store']);
    Route::get('/nominations', [NominationController::class, 'index']);

    // Magazine Issues
    Route::get('/issues', [IssueController::class, 'index']);
    Route::get('/issues/{id}', [IssueController::class, 'show']);

    // Concierge
    Route::post('/concierge', [ConciergeController::class, 'store']);
    Route::get('/concierge', [ConciergeController::class, 'index']);
    Route::get('/concierge/{id}', [ConciergeController::class, 'show']);

    // Event Packages & Payments
    Route::get('/awards/{id}/packages', [PaymentController::class, 'getPackages']);
    Route::post('/packages/apply', [PaymentController::class, 'createApplication']);
    Route::get('/my-applications', [PaymentController::class, 'myApplications']);
    Route::get('/packages/applications/{id}', [PaymentController::class, 'getApplication']);

    // Supplier routes
    Route::middleware('role:supplier,admin')->group(function () {
        Route::post('/offers', [OfferController::class, 'store']);
        Route::put('/offers/{id}', [OfferController::class, 'update']);
        Route::delete('/offers/{id}', [OfferController::class, 'destroy']);
    });

    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/stats/dashboard', [StatsController::class, 'dashboard']);
        
        // User management
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        
        // Application management
        Route::get('/admin/applications', [PaymentController::class, 'getAllApplications']);
        
        Route::post('/posts', [PostController::class, 'store']);
        Route::put('/posts/{id}', [PostController::class, 'update']);
        Route::delete('/posts/{id}', [PostController::class, 'destroy']);

        Route::post('/awards', [AwardController::class, 'store']);
        Route::put('/awards/{id}', [AwardController::class, 'update']);
        Route::delete('/awards/{id}', [AwardController::class, 'destroy']);

        Route::post('/issues', [IssueController::class, 'store']);
        Route::put('/issues/{id}', [IssueController::class, 'update']);
        Route::delete('/issues/{id}', [IssueController::class, 'destroy']);

        Route::put('/concierge/{id}', [ConciergeController::class, 'update']);
        
        // Event Package management
        Route::get('/packages', [EventPackageController::class, 'index']);
        Route::get('/packages/{id}', [EventPackageController::class, 'show']);
        Route::post('/packages', [EventPackageController::class, 'store']);
        Route::put('/packages/{id}', [EventPackageController::class, 'update']);
        Route::delete('/packages/{id}', [EventPackageController::class, 'destroy']);
    });
});

// Supplier Dashboard Routes
Route::middleware('auth:api')->prefix('supplier')->group(function () {
    Route::get('/stats', [App\Http\Controllers\SupplierDashboardController::class, 'stats']);
    Route::get('/offers', [App\Http\Controllers\SupplierDashboardController::class, 'myOffers']);
    Route::get('/offers/{id}/analytics', [App\Http\Controllers\SupplierDashboardController::class, 'offerAnalytics']);
    Route::post('/offers', [App\Http\Controllers\SupplierDashboardController::class, 'createOffer']);
    Route::put('/offers/{id}', [App\Http\Controllers\SupplierDashboardController::class, 'updateOffer']);
    Route::delete('/offers/{id}', [App\Http\Controllers\SupplierDashboardController::class, 'deleteOffer']);
});
