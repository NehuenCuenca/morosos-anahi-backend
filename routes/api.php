<?php

use App\Http\Controllers\DebtController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DefaulterController;
use App\Http\Controllers\ThingController;

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
Route::get('/defaulters/{defaulter}/debts', [DefaulterController::class, 'get_debts']);

Route::resource('things', ThingController::class);
Route::resource('debts', DebtController::class);