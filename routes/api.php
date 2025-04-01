<?php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SectorController;

// Public Auth Routes (No Authentication Required)
Route::controller( AuthController::class)->group(function () {
Route::post('/register', 'register'); // User registration
Route::post('/login', 'login'); // User login
});

// Protected Routes (Require Authentication)
Route::middleware('auth:sanctum')->group(function (): void {
// API Resource Routes
Route::apiResources([
    'users' => UserController::class,
    'tags' => TagController::class,
    'reports' => ReportController::class,
    'sectors' => SectorController::class,
]);

// Add additional routes for specific actions
// Route for reports search
Route::get('/search', [ReportController::class, 'search']);
// Route for downloading reports
Route::get('/reports/{report}/download', [ReportController::class, 'download']);
// Route for updating the report validation status
Route::put('/reports/{report}/validate', [ReportController::class, 'validateReport']);



// Route for updating the user password
Route::put('/users/{user}/password', [UserController::class, 'updatePassword']);




// Authenticated User Routes
Route::controller(AuthController::class)->group(function () {
Route::post('/logout', 'logout'); // Logout
Route::get('/user', 'user'); // Get authenticated user details
});
});
