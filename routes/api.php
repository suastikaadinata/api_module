<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PenjualanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::group(['prefix' => 'inventory'], function () {
        Route::post('/', [InventoryController::class, 'create']);
        Route::get('/', [InventoryController::class, 'list']);
        Route::delete('/{id}', [InventoryController::class, 'delete']);
        Route::post('/{id}', [InventoryController::class, 'update']);
    });

    Route::group(['prefix' => 'penjualan'], function () {
        Route::post('/', [PenjualanController::class, 'create']);
        Route::get('/', [PenjualanController::class, 'list']);
        Route::delete('/{id}', [PenjualanController::class, 'delete']);
        Route::patch('/{id}', [PenjualanController::class, 'update']);
    });
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
