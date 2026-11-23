<?php

use App\Http\Controllers\AdminAvailabilityCalendarController;
use App\Http\Controllers\AdminCateringBookingController;
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminReportingController;
use App\Http\Controllers\AdminReviewController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CateringBookingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\FoodPackageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\PublicCatalogController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserManagementController;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $bestSellers = Product::with('category')
        ->where('is_available', true)
        ->orderBy('product_id')
        ->limit(4)
        ->get();
    $approvedReviews = Review::with('user')
        ->where('status', 'Approved')
        ->latest('created_at')
        ->limit(3)
        ->get();
    $averageRating = Review::where('status', 'Approved')->avg('rating');

    return view('home', compact('bestSellers', 'approvedReviews', 'averageRating'));
})->name('home');

Route::get('/products', [PublicCatalogController::class, 'products'])->name('products.index');
Route::get('/products/{product}', [PublicCatalogController::class, 'product'])->name('products.show');
Route::get('/packages', [PublicCatalogController::class, 'packages'])->name('packages.index');
Route::get('/about', [PublicCatalogController::class, 'about'])->name('about');
Route::get('/contact', [PublicCatalogController::class, 'contact'])->name('contact');
Route::get('/reviews', [PublicCatalogController::class, 'reviews'])->name('reviews');

Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/my-reviews', [ReviewController::class, 'index'])->name('reviews.mine');
    Route::post('/orders/{order}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');

    Route::get('/catering', [CateringBookingController::class, 'index'])->name('catering.index');
    Route::get('/catering/availability-events', [CateringBookingController::class, 'events'])->name('catering.events');
    Route::post('/catering/bookings', [CateringBookingController::class, 'store'])->name('catering.bookings.store');
    Route::patch('/catering/bookings/{booking}/cancel', [CateringBookingController::class, 'cancel'])->name('catering.bookings.cancel');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/confirmation', [CustomerOrderController::class, 'confirmation'])->name('orders.confirmation');

    Route::get('/account', [CustomerAccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [CustomerAccountController::class, 'update'])->name('account.update');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminReportingController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/prep-sheet', [AdminReportingController::class, 'prepSheet'])->name('admin.prep-sheet');
    Route::get('/prep-sheet/pdf', [AdminReportingController::class, 'prepSheetPdf'])->name('admin.prep-sheet.pdf');
    Route::get('/prep-sheet/csv', [AdminReportingController::class, 'prepSheetCsv'])->name('admin.prep-sheet.csv');
    Route::get('/reports', [AdminReportingController::class, 'reports'])->name('admin.reports.index');
    Route::get('/reports/pdf', [AdminReportingController::class, 'reportsPdf'])->name('admin.reports.pdf');
    Route::get('/reports/csv', [AdminReportingController::class, 'reportsCsv'])->name('admin.reports.csv');
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('admin.reviews.index');
    Route::patch('/reviews/{review}/status', [AdminReviewController::class, 'updateStatus'])->name('admin.reviews.status');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('admin.reviews.destroy');
    Route::get('/catering-bookings', [AdminCateringBookingController::class, 'index'])->name('admin.catering-bookings.index');
    Route::get('/catering-bookings/{booking}', [AdminCateringBookingController::class, 'show'])->name('admin.catering-bookings.show');
    Route::patch('/catering-bookings/{booking}/confirm', [AdminCateringBookingController::class, 'confirm'])->name('admin.catering-bookings.confirm');
    Route::patch('/catering-bookings/{booking}/reject', [AdminCateringBookingController::class, 'reject'])->name('admin.catering-bookings.reject');
    Route::get('/availability-calendar', [AdminAvailabilityCalendarController::class, 'index'])->name('admin.availability.index');
    Route::post('/availability-calendar', [AdminAvailabilityCalendarController::class, 'store'])->name('admin.availability.store');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.status');
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('admin.notifications.index');
    Route::patch('/notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('admin.notifications.read');
    Route::resource('categories', CategoryController::class)->except(['show'])->names([
        'index' => 'admin.categories.index',
        'create' => 'admin.categories.create',
        'store' => 'admin.categories.store',
        'edit' => 'admin.categories.edit',
        'update' => 'admin.categories.update',
        'destroy' => 'admin.categories.destroy',
    ]);
    Route::resource('products', ProductController::class)->except(['show'])->names([
        'index' => 'admin.products.index',
        'create' => 'admin.products.create',
        'store' => 'admin.products.store',
        'edit' => 'admin.products.edit',
        'update' => 'admin.products.update',
        'destroy' => 'admin.products.destroy',
    ]);
    Route::patch('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
    Route::post('products/import', [ProductImportController::class, 'import'])->name('products.import');
    Route::get('products/import/template', [ProductImportController::class, 'template'])->name('products.import.template');
    Route::resource('packages', FoodPackageController::class)->except(['show'])->names([
        'index' => 'admin.packages.index',
        'create' => 'admin.packages.create',
        'store' => 'admin.packages.store',
        'edit' => 'admin.packages.edit',
        'update' => 'admin.packages.update',
        'destroy' => 'admin.packages.destroy',
    ]);
    Route::patch('packages/{package}/toggle', [FoodPackageController::class, 'toggle'])->name('packages.toggle');

    Route::middleware('owner')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('admin.users.store');
        Route::patch('/users/{user}/status', [UserManagementController::class, 'updateStatus'])->name('admin.users.status');
        Route::patch('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('admin.users.role');
    });
});
