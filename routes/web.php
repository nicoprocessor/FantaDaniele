<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Game\GameArrivalConfirmationController;
use App\Http\Controllers\Game\GameArrivalProposalController;
use App\Http\Controllers\Game\GameArrivalVoteController;
use App\Http\Controllers\Game\GameBetController;
use App\Http\Controllers\Game\GameController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::post('games', [GameController::class, 'store'])->name('games.store');
        Route::delete('games/{game}', [GameController::class, 'destroy'])->name('games.destroy');
        Route::post('games/{game}/bets', [GameBetController::class, 'store'])->name('games.bets.store');
        Route::post('games/{game}/arrival-proposals', [GameArrivalProposalController::class, 'store'])->name('games.arrivals.proposals.store');
        Route::post('games/{game}/arrival-proposals/{proposal}/votes', [GameArrivalVoteController::class, 'store'])->name('games.arrivals.proposals.votes.store');
        Route::post('games/{game}/confirm-arrival', [GameArrivalConfirmationController::class, 'store'])->name('games.arrival.confirm');
    });

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
