<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContractorExportController;
use App\Http\Controllers\ContractorShowController;
use App\Http\Controllers\InfoPageController;
use App\Http\Controllers\PublicFormController;
use App\Http\Controllers\PublicReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\AuthModalController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/login', [AuthModalController::class, 'redirectToLogin'])->name('login');
Route::post('/login', [AuthModalController::class, 'login'])->name('login.attempt');
Route::get('/register', [AuthModalController::class, 'redirectToRegister'])->name('register');
Route::post('/register', [AuthModalController::class, 'register'])->name('register.store');
Route::post('/request', [PublicFormController::class, 'storeRequest'])->name('request.store');
Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard/contractors/export', ContractorExportController::class)->name('contractors.export');
    Route::post('/reviews/service', [PublicReviewController::class, 'storeService'])->name('reviews.service.store');
    Route::post('/reviews/contractor/{contractor}', [PublicReviewController::class, 'storeContractor'])->name('reviews.contractor.store');
});
Route::get('/search', SearchController::class)->name('search.index');
Route::get('/info/{slug}', InfoPageController::class)->name('info.show');
Route::get('/agent/{slug}', ContractorShowController::class)->name('agent.show');
Route::get('/agents/{slug}', ContractorShowController::class)->name('agents.show');
