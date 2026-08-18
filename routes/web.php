<?php

use App\Http\Controllers\AvatarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Game\GameArrivalConfirmationController;
use App\Http\Controllers\Game\GameArrivalProposalController;
use App\Http\Controllers\Game\GameArrivalVoteController;
use App\Http\Controllers\Game\GameBetController;
use App\Http\Controllers\Game\GameController;
use App\Http\Controllers\GameIndexController;
use App\Http\Controllers\GameShowController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('avatars/{user}.svg', [AvatarController::class, 'show'])->name('avatars.show');
Route::get('avatars/previews/{seed}.svg', [AvatarController::class, 'preview'])
    ->middleware('auth')
    ->name('avatars.previews.show');

Route::middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::get('partite', GameIndexController::class)->name('games.index');
        Route::get('partite/{game}', GameShowController::class)->name('games.show');
        Route::get('classifica', LeaderboardController::class)->name('leaderboard.index');
        Route::get('statistiche', StatisticsController::class)->name('statistics.index');
        Route::post('games', [GameController::class, 'store'])->name('games.store');
        Route::delete('games/{game}', [GameController::class, 'destroy'])->name('games.destroy');
        Route::post('games/{game}/bets', [GameBetController::class, 'store'])->name('games.bets.store');
        Route::post('games/{game}/arrival-proposals', [GameArrivalProposalController::class, 'store'])->name('games.arrivals.proposals.store');
        Route::post('games/{game}/arrival-proposals/{proposal}/votes', [GameArrivalVoteController::class, 'store'])->name('games.arrivals.proposals.votes.store');
        Route::post('games/{game}/confirm-arrival', [GameArrivalConfirmationController::class, 'store'])->name('games.arrival.confirm');
    });

Route::redirect('{current_team}/dashboard', '/dashboard')->where('current_team', '[a-z0-9-]+');

require __DIR__.'/settings.php';
