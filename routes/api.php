<?php

use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\PermissionController;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/files/{file}/restore', [FileController::class, 'restore'])->name('files.restore');
    Route::apiResource('files', FileController::class);
    Route::get('/folders/search', [FolderController::class, 'search'])->name('folders.search');
    Route::apiResource('folders',FolderController::class);
    Route::post('/permissions',[PermissionController::class,'store'])->name('permissions.store');
    Route::delete('/permissions',[PermissionController::class,'destroy'])->name('permissions.destroy');
});

Route::fallback(function(){
    return response()->json([
        'message'=>'this route does not exist'
    ],404);
});
