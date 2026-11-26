<?php

use App\Http\Controllers\SigmadReviewController;
use Illuminate\Support\Facades\Route;

// Módulo exclusivo para personal Administrador: comprueba rol en CADA operación.
Route::middleware(['auth'])->prefix('campo/revision-ia')->group(function () {
    Route::get('/', [SigmadReviewController::class, 'index'])->name('campo.review.index');
    Route::get('/{mission:uuid}/data', [SigmadReviewController::class, 'data'])->name('campo.review.data');
    Route::post('/{mission:uuid}/revisions', [SigmadReviewController::class, 'save'])->name('campo.review.save');
    Route::get('/{mission:uuid}/history', [SigmadReviewController::class, 'history'])->name('campo.review.history');
    Route::get('/{mission:uuid}/revisions/{version}', [SigmadReviewController::class, 'revision'])->whereNumber('version')->name('campo.review.revision');
});
