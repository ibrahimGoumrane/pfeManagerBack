<?php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SectorController;


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResources([
        // User Routes
        'users' => UserController::class,
        // Tag Routes
        'tags' => TagController::class,
        // Report Routes
        'reports' => ReportController::class,
        // Sector Routes
        'sectors' => SectorController::class,
    ]);
    // Auth Routes
    Route::Controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/user', 'user');
    });
});


// Auth Routes
Route::Controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});
