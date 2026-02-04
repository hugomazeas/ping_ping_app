<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/players', [HomeController::class, 'setPlayer'])->name('players.store');
Route::get('/api/players', [HomeController::class, 'playersAutocomplete'])->name('players.autocomplete');

// Game
Route::get('/play', [GameController::class, 'selectOpponent'])->name('play');
Route::post('/games', [GameController::class, 'store'])->name('games.store');
Route::get('/game/{game}', [GameController::class, 'show'])->name('game.show');
Route::patch('/game/{game}', [GameController::class, 'update'])->name('game.update');
Route::post('/game/{game}/end', [GameController::class, 'end'])->name('game.end');
Route::post('/game/{game}/rematch', [GameController::class, 'rematch'])->name('game.rematch');

// Stats
Route::get('/stats', [StatsController::class, 'index'])->name('stats');
Route::get('/stats/{player}', [StatsController::class, 'player'])->name('stats.player');
Route::get('/api/leaderboard', [StatsController::class, 'leaderboard'])->name('api.leaderboard');
