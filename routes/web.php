<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;

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

Route::get('/', function () {
    return view('welcome');
});

// Item Resource controller
Route::resource('item', ItemController::class); 
Route::post('uploads', [ItemController::class,'uploads'])->name('uploads');
Route::get('readFiles/{id?}', [ItemController::class, 'readFiles'])->name('readFiles');
Route::post('image/delete',[ItemController::class,'fileDestroy']);
