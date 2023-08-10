<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\VoucherController;
use App\Http\Middleware\AddJsonHeaderMiddleware;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix("v1")->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post("register", [ApiAuthController::class, 'register']);
        Route::post("logout", [ApiAuthController::class, 'logout']);
        Route::post("logout-all", [ApiAuthController::class, 'logoutAll']);
        Route::get("devices", [ApiAuthController::class, 'devices']);
        // inventory
        Route::apiResource("brands", BrandController::class);
        Route::apiResource("products", ProductController::class);
        Route::apiResource("stocks", StockController::class);
        // sale
        Route::apiResource("vouchers", VoucherController::class);
        Route::post("checkout", [VoucherController::class, "checkout"]);
    });
    Route::post("login", [ApiAuthController::class, 'login']);
});
