<?php

use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\IndexController;
use App\Http\Controllers\Dashboard\BookController;
use App\Http\Controllers\Dashboard\SettingController;
use App\Http\Controllers\Dashboard\AuthorController;
use App\Http\Controllers\dashboard\OrderController;
use App\Http\Controllers\dashboard\RequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::group(['as' => 'dashboard.'], function () {
    Route::put('settings/{setting}/update',[SettingController::class , 'update'])->name('settings.update');
    Route::get('settings',[SettingController::class , 'index'])->name('settings.index');

    #### category ####
    Route::get('categories/ajax',[CategoryController::class , 'getall'])->name('categories.getall');
    Route::delete('categories/delete',[CategoryController::class , 'delete'])->name('categories.delete');
    Route::resource('categories', CategoryController::class)->except('destroy','create' , 'show');
    #### /category ####

    #### author ####
    Route::get('authors/ajax',[AuthorController::class , 'getall'])->name('authors.getall');
    Route::delete('authors/delete',[AuthorController::class , 'delete'])->name('authors.delete');
    Route::resource('authors', AuthorController::class)->except('destroy','create' , 'show');
    #### /author ####

    #### book ####
    Route::get('books/ajax',[BookController::class , 'getall'])->name('books.getall');
    Route::delete('books/delete',[BookController::class , 'delete'])->name('books.delete');
    Route::resource('books', BookController::class)->except('show');
    #### /book ####

    #### request ####
    Route::get('requests/ajax',[RequestController::class , 'getall'])->name('requests.getall');
    Route::delete('requests/delete',[RequestController::class , 'delete'])->name('requests.delete');
    Route::resource('requests', RequestController::class)->except('show');
    Route::put('requests/{id}/approve',[RequestController::class , 'approve'])->name('requests.approve');
    #### /request ####
    Route::get('orders',[OrderController::class,'index'])->name('orders.index');
    Route::post('orders/cancel/{orderid}',[OrderController::class,'cancel'])->name('order.cancel');
    Route::post('orders/deliver/{orderid}',[OrderController::class,'deliver'])->name('order.deliver');
    Route::post('orders/delete/{orderid}',[OrderController::class,'delete'])->name('order.delete');
});
