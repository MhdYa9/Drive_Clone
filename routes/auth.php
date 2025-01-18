<?php


use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

//------------------------------------------------------


Route::middleware('guest:sanctum')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth:sanctum');
