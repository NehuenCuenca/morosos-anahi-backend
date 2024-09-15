<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DefaulterController;
use App\Http\Controllers\ItemController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
 
Route::resource('defaulters', DefaulterController::class);
Route::get('/defaulters/{id}/items', [DefaulterController::class, 'get_items']);

Route::resource('items', ItemController::class);