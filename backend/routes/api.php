<?php

use App\Http\Controllers\Api\BooksController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/libros', [BooksController::class, 'index'])->name('api.books.index');
Route::post('/libros', [BooksController::class, 'store'])->name('api.books.store');
Route::get('/libros/{id}', [BooksController::class, 'show'])->name('api.books.show');
Route::put('/libros/{id}', [BooksController::class, 'update'])->name('api.books.update');
Route::patch('/libros/{id}', [BooksController::class, 'patch'])->name('api.books.update-partial');
Route::delete('/libros/{id}', [BooksController::class, 'destroy'])->name('api.books.destroy');

Route::get('/health', function (): JsonResponse {
    try {
        DB::connection('mongodb')->command(['ping' => 1]);

        return response()->json([
            'status' => 'ok',
            'services' => ['mongodb' => 'ok'],
        ]);
    } catch (Throwable $exception) {
        report($exception);

        return response()->json([
            'status' => 'degraded',
            'services' => ['mongodb' => 'unavailable'],
        ], 503);
    }
})->name('api.health');
