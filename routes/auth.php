<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Auth;

Route::middleware('guest')->group(function () {
    //he lroute ma ela 3aze l2no lregister w login 3nde yehn 3anafs lroute.
    //Route::get('register', [RegisteredUserController::class, 'create'])
    //            ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])->name('register');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.requestt');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
                ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});

Auth::routes();

Route::middleware('guest')->group(function () {

});

Route::middleware('auth')->group(function () {
    Route::post('comment/create',[CommentController::class,'create'
    ])->name('comment.create');

    Route::get('/profile/{user:username}',[ProfileController::class,'index'
    ])->name('user.profile');
    Route::get('/orders/{user:username}',[ProfileController::class,'orders'
    ])->name('user.orders');
    Route::post('/orders/{user:username}/{orderid}/cancel',[ProfileController::class,'cancel'
    ])->name('user.order.cancel');
    Route::post('/orders/{user:username}/{orderid}/delete',[ProfileController::class,'delete'
    ])->name('user.order.delete');

    Route::put('/profile/{user}/update',[ProfileController::class , 'update'])->name('user.profile.update');

    Route::post('/profile/{user}/request',[ProfileController::class,'request'])->name('user.request');
    Route::get('/payment',[StripeController::class,'index'])->name('payment.index');
    Route::post('/payment/checkout',[StripeController::class,'checkout'])->name('payment.checkout');
    Route::get('/Checkout/success',[StripeController::class,'success'])->name('payment.success');
});
