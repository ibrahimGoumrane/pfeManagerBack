<?php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SectorController;

// Public Auth Routes (No Authentication Required)
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register'); // User registration
    Route::post('/login', 'login'); // User login
});

// Resource controllers with mixed public/protected routes
Route::controller(UserController::class)->group(function () {
    Route::get('/users', 'index');
    Route::get('/users/{user}', 'show');
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/users', 'store');
        Route::put('/users/{user}', 'update');
        Route::get('/users/{user}/reports', 'userReports'); // Get reports by user
        Route::delete('/users/{user}', 'destroy');
        Route::put('/users/{user}/password', 'updatePassword'); // Update user password
    });
});

Route::controller(TagController::class)->group(function () {
    Route::get('/tags', 'index');
    Route::get('/tags/{tag}', 'show');
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/tags', 'store');
        Route::put('/tags/{tag}', 'update');
        Route::delete('/tags/{tag}', 'destroy');
    });
});

Route::controller(ReportController::class)->group(function () {
    Route::get('/reports', 'index');
    Route::get('/reports/{report}', 'show');
    Route::get('/search', 'search');
    Route::get('/reports/{report}/download', 'download');
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/reports', 'store');
        Route::put('/reports/{report}', 'update');
        Route::delete('/reports/{report}', 'destroy');
        Route::put('/reports/{report}/validate', 'validateReport'); // Update validation status
    });
});

Route::controller(SectorController::class)->group(function () {
    Route::get('/sectors', 'index');
    Route::get('/sectors/{sector}', 'show');
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/sectors', 'store');
        Route::put('/sectors/{sector}', 'update');
        Route::delete('/sectors/{sector}', 'destroy');
    });
});

// Authenticated User Routes
Route::middleware('auth:sanctum')->controller(AuthController::class)->group(function () {
    Route::post('/logout', 'logout'); // Logout
    Route::get('/user', 'user'); // Get authenticated user details
});
