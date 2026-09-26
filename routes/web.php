<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book:slug}', [BookController::class, 'show'])->name('books.show');

Route::get('/authors', [AuthorController::class, 'index'])->name('authors.index');
Route::get('/authors/{author:slug}', [AuthorController::class, 'show'])->name('authors.show');

Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->middleware('throttle:cart')->name('cart.store');
Route::delete('/cart/{book}', [CartController::class, 'destroy'])->whereNumber('book')->name('cart.destroy');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Payments
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
});

// CSRF-exempt (see bootstrap/app.php); authenticity comes from the Stripe signature.
Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');

/*
|--------------------------------------------------------------------------
| Account
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::get('/library/{book:slug}/download', [LibraryController::class, 'download'])
        ->withTrashed()
        ->middleware('throttle:downloads')
        ->name('library.download');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/books/{book:slug}/reviews', [ReviewController::class, 'store'])->middleware('throttle:reviews')->name('reviews.store');
    Route::patch('/reviews/{review}', [ReviewController::class, 'update'])->middleware('throttle:reviews')->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
