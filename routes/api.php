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

// Protected Routes (Require Authentication)
Route::middleware('auth:sanctum')->group(function () {
// API Resource Routes
Route::apiResources([
    'users' => UserController::class,
    'tags' => TagController::class,
    'reports' => ReportController::class,
    'sectors' => SectorController::class,
]);

// Add additional routes for specific actions
// Route for reports search
Route::get('/reports/search', [ReportController::class, 'search']);


// Authenticated User Routes
Route::controller(AuthController::class)->group(function () {
Route::post('/logout', 'logout'); // Logout
Route::get('/user', 'user'); // Get authenticated user details
});
});
