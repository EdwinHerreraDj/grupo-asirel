<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Drive\FolderController;
use App\Http\Controllers\Api\Drive\FileController;

Route::middleware('auth:sanctum')->group(function () {

    // Usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ===== DRIVE =====

    Route::prefix('folders')->group(function () {
        Route::get('{id}/content', [FolderController::class, 'getContent']);
        Route::post('/', [FolderController::class, 'store']);
    });

    Route::prefix('files')->group(function () {
        Route::post('/', [FileController::class, 'store']);
    });

});
