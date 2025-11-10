<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\RapoarteController;
use App\Http\Controllers\PaymentsController;

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

// Article routes (public)
Route::prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index']);
    Route::get('/search', [ArticleController::class, 'search']);
    Route::get('/{id}', [ArticleController::class, 'show']);
    Route::put('/{id}', [ArticleController::class, 'update']);
    Route::delete('/{id}', [ArticleController::class, 'delete']);
});

// Customer routes (public)
Route::prefix('customers')->group(function () {
    Route::get('/{id}', [\App\Http\Controllers\CustomerController::class, 'show']);
});

// Rapoarte routes (public)
Route::prefix('rapoarte')->group(function () {
    Route::post('/generate-x', [RapoarteController::class, 'generateX']);
    Route::post('/generate-z', [RapoarteController::class, 'generateZ']);
});

// Payments routes (public)
Route::prefix('payments')->group(function () {
    Route::post('/subtotal', [PaymentsController::class, 'subTotal']);
    Route::post('/payment', [PaymentsController::class, 'payment']);
});

// Protected API routes that require secret
Route::middleware('api.secret')->group(function () {
    
});
