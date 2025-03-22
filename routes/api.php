<?php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;





// Auth Routes
Route::Controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout');
    Route::get('/user', 'user')->middleware('auth:sanctum');
});
