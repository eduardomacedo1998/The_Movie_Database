<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovieController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Rota inicial - redireciona para login
Route::get('/', function () {
    return redirect('/login');
});

// Rotas de Autenticação
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rotas protegidas por autenticação
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [MovieController::class, 'index'])->name('home');
    Route::get('/search', [MovieController::class, 'search'])->name('movies.search');
    Route::get('/favorites', [MovieController::class, 'favorites'])->name('movies.favorites');
    Route::post('/favorites/{tmdbId}', [MovieController::class, 'addFavorite'])->name('movies.favorite');
    Route::delete('/favorites/{id}', [MovieController::class, 'removeFavorite'])->name('movies.unfavorite');
});
