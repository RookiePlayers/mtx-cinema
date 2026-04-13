<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\MovieManagerController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/movies/create', [MovieManagerController::class, 'index'])->name('movies.create');
    Route::post('/movies', [MovieManagerController::class, 'createMovie'])->name('movies.store');
    Route::put('/movies/{id}', [MovieManagerController::class, 'updateMovie'])->name('movies.update');
    Route::delete('/movies/{id}', [MovieManagerController::class, 'deleteMovie'])->name('movies.delete');
    Route::post('/movies/{id}/save', [MovieController::class, 'save'])->name('movie.save');
    Route::delete('/movies/{id}/save', [MovieController::class, 'unsave'])->name('movie.unsave');
});

Route::get('/movies/{id}', [MovieController::class, 'show'])->name('movie');

Route::middleware('cache.ttl:60')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/autocomplete-search', [HomeController::class, 'autocompleteSearch'])->name('autocomplete-search');
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::get('/movie-search', [SearchController::class, 'search'])->name('movie-search');
});
