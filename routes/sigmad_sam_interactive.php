<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SigmadSamInteractiveController;
Route::middleware(['auth'])->prefix('campo/revision-ia')->group(function () {
    Route::post('/{mission:uuid}/sam-interactive', [SigmadSamInteractiveController::class,'predict'])
        ->name('campo.review.sam.predict');
});
