<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\JourneyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubcategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CategoryController::class, 'index'])->name('home');
Route::get('/guide', GuideController::class)->name('guide');
Route::get('/search', SearchController::class)->name('search');

Route::get('/journeys', [JourneyController::class, 'index'])->name('journeys.index');
Route::get('/journeys/{journey}', [JourneyController::class, 'show'])->name('journeys.show');
Route::get('/journeys/{journey}/step/{step}', [JourneyController::class, 'step'])
    ->whereNumber('step')
    ->name('journeys.step');

Route::post('/prompts/{prompt}/copy', [PromptController::class, 'copy'])->name('prompts.copy');

Route::scopeBindings()->group(function () {
    Route::get('/c/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/c/{category}/{subcategory}', [SubcategoryController::class, 'show'])->name('subcategories.show');
    Route::get('/c/{category}/{subcategory}/{prompt}', [PromptController::class, 'show'])->name('prompts.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard');

    Route::get('/prompts/create', [PromptController::class, 'create'])->name('prompts.create');
    Route::post('/prompts', [PromptController::class, 'store'])->name('prompts.store');

    Route::scopeBindings()->group(function () {
        Route::get('/c/{category}/{subcategory}/{prompt}/edit', [PromptController::class, 'edit'])->name('prompts.edit');
        Route::put('/c/{category}/{subcategory}/{prompt}', [PromptController::class, 'update'])->name('prompts.update');
        Route::delete('/c/{category}/{subcategory}/{prompt}', [PromptController::class, 'destroy'])->name('prompts.destroy');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
