<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CategoryController;
use Illuminate\Support\Facades\Route;

// Public read endpoints — powers the public magazine site.
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('articles', ArticleController::class)->only(['index', 'show']);

// Mutating endpoints — editorial/admin only.
//
// NOTE: this uses the default session guard (['web', 'auth']) rather than
// Sanctum tokens, since Sanctum isn't installed yet. That's fine for an
// admin panel served from the same Laravel app, but a decoupled mobile
// app or public API consumer (both on the roadmap) will need token auth —
// install Laravel Sanctum in the upcoming Auth milestone and swap this
// middleware to `auth:sanctum` at that point.
Route::middleware(['web', 'auth'])->group(function () {
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    Route::apiResource('articles', ArticleController::class)->except(['index', 'show']);
});
