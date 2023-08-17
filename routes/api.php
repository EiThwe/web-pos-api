<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;
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
        Route::middleware("isActiveUser")->group(function () {
            Route::post("register", [ApiAuthController::class, 'register']);
            Route::post("logout", [ApiAuthController::class, 'logout']);
            Route::post("logout-all", [ApiAuthController::class, 'logoutAll']);
            Route::get("devices", [ApiAuthController::class, 'devices']);
            //profile
            Route::get("profile", [ApiAuthController::class, 'profile']);
            Route::put("profile", [ApiAuthController::class, 'profileUpdate']);
            Route::patch("profile", [ApiAuthController::class, 'profileUpdate']);

            Route::put("change-password", [ApiAuthController::class, 'changePassword']);
            //users
            Route::get("users", [UserController::class, 'users']);
            Route::get("users/{id}", [UserController::class, 'user']);
            Route::put("users/{id}", [UserController::class, 'userUpdate']);
            Route::patch("users/{id}", [UserController::class, 'userUpdate']);


            Route::delete("users/{id}", [UserController::class, 'userDelete']);


            // inventory
            Route::apiResource("brands", BrandController::class);
            Route::apiResource("products", ProductController::class);
            Route::apiResource("stocks", StockController::class);
            // sale
            Route::apiResource("vouchers", VoucherController::class);
            //photo
            Route::get("media", [PhotoController::class, "index"]);
            Route::post("media", [PhotoController::class, "upload"]);
            Route::delete("media/{id}", [PhotoController::class, "destroy"]);
            Route::post("checkout", [VoucherController::class, "checkout"]);
        });
    });
    Route::post("login", [ApiAuthController::class, 'login']);
});
