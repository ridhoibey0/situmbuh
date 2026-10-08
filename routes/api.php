<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\ChatController;

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

Route::get('/provinces', [LocationController::class, 'provinces']);
Route::get('/regencies/{province_id}', [LocationController::class, 'regencies']);
Route::get('/districts/{regency_id}', [LocationController::class, 'districts']);
Route::get('/villages/{district_id}', [LocationController::class, 'villages']);
Route::get('/search-villages', [LocationController::class, 'searchVillages']);

