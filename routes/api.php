<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('users/shareable', [UserController::class, 'shareable']);

        Route::get('documents', [DocumentController::class, 'index']);
        Route::post('documents', [DocumentController::class, 'store']);
        Route::post('documents/import', [DocumentController::class, 'import']);
        Route::get('documents/{id}', [DocumentController::class, 'show'])->whereNumber('id');
        Route::put('documents/{id}', [DocumentController::class, 'update'])->whereNumber('id');
        Route::delete('documents/{id}', [DocumentController::class, 'destroy'])->whereNumber('id');
        Route::get('documents/{id}/share', [ShareController::class, 'index'])->whereNumber('id');
        Route::post('documents/{id}/share', [ShareController::class, 'store'])->whereNumber('id');
        Route::put('documents/{id}/share/{shareId}', [ShareController::class, 'update'])
            ->whereNumber('id')
            ->whereNumber('shareId');
    });
});
