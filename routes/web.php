<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('index');
Route::get('/books/ajax',[BookController::class,'search'])->name('home.books.ajax');

Route::get('/author/{author:first_name}/books',[AuthorController::class,'index']);
Route::get('/author/ajax',[AuthorController::class,'search'])->name('author.ajax');
Route::get('/author/books/ajax',[AuthorController::class,'searchbooks'])->name('author.books.ajax');

Route::get('/category/{category:name}/books',[CategoryController::class,'index']);
Route::get('/category/books/ajax',[CategoryController::class,'search'])->name('category.books.ajax');

Route::get('/cart',[CartController::class,'index'])->name('cart.index');
Route::post('/cart',[CartController::class,'store'])->name('cart.store');
Route::delete('/cart/remove/{rowId}', [CartController::class, 'remove'])->name('cart.remove');


Route::get('/logout',function(){
    return redirect()->route('index');
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
