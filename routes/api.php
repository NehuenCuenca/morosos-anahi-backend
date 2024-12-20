<?php

use App\Http\Controllers\DebtController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DefaulterController;
use App\Http\Controllers\MultipleDebtController;
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
Route::get('/defaulters/{defaulter}/excel-debts', [DefaulterController::class, 'get_excel_debts_by_month_year']);

Route::resource('things', ThingController::class);

Route::put('/debts/{defaulter}/multiple', [MultipleDebtController::class, 'update_multiple_debts']);
Route::delete('/debts/{defaulter}/multiple', [MultipleDebtController::class, 'destroy_multiple_debts']);
Route::resource('debts', DebtController::class);
