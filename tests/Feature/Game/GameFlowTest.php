<?php

use App\Actions\Game\CloseExpiredGames;
use App\Actions\Game\CloseGame;
use App\Actions\Game\ConfirmGameArrival;
use App\Actions\Game\CreateGame;
use App\Actions\Game\PlaceGameBet;
use App\Models\Game;
use App\Models\GameArrivalProposal;
use App\Models\GameArrivalVote;
use App\Models\User;
use App\Notifications\DailyGamePropertyGranted;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;

test('bets consume balance, reserve exact arrival slots, and start game at two players', function () {
    $creator = User::factory()->create(['balance' => 10]);
    $secondPlayer = User::factory()->create(['balance' => 10]);
    $game = app(CreateGame::class)->handle($creator);

    app(PlaceGameBet::class)->handle($game, $creator, 4, 600);
    app(PlaceGameBet::class)->handle($game, $secondPlayer, 6, 601);

    expect($game->fresh()->status)->toBe('started')
        ->and($creator->fresh()->balance)->toBe(6)
        ->and($secondPlayer->fresh()->balance)->toBe(4);

    expect(fn () => app(PlaceGameBet::class)->handle($game, $creator, 1, 602))
        ->toThrow(ValidationException::class);
    expect(fn () => app(PlaceGameBet::class)->handle($game, User::factory()->create(['balance' => 2]), 1, 600))
        ->toThrow(ValidationException::class);
});

test('confirmed exact arrival pays pooled stake to winner after majority of cast votes', function () {
    $firstPlayer = User::factory()->create(['balance' => 10, 'is_game_admin' => true]);
    $secondPlayer = User::factory()->create(['balance' => 10]);
    $game = app(CreateGame::class)->handle($firstPlayer);

    app(PlaceGameBet::class)->handle($game, $firstPlayer, 4, 600);
    app(PlaceGameBet::class)->handle($game, $secondPlayer, 6, 601);
    $proposal = GameArrivalProposal::factory()->create(['game_id' => $game->id, 'proposed_by' => $firstPlayer->id, 'arrival_minute' => 600]);
    GameArrivalVote::factory()->create(['game_arrival_proposal_id' => $proposal->id, 'user_id' => $firstPlayer->id, 'approved' => true]);

    app(ConfirmGameArrival::class)->handle($game, $proposal, $firstPlayer);

    $resolvedGame = $game->fresh();

    expect($resolvedGame->winner_type)->toBe('exact')
        ->and($resolvedGame->winner_user_id)->toBe($firstPlayer->id)
        ->and($firstPlayer->fresh()->balance)->toBe(16)
        ->and($secondPlayer->fresh()->balance)->toBe(4);
});

test('sport resolution has no winner or payout when no bet matches confirmed arrival', function () {
    $firstPlayer = User::factory()->create(['balance' => 10]);
    $secondPlayer = User::factory()->create(['balance' => 10]);
    $game = app(CreateGame::class)->handle($firstPlayer);

    app(PlaceGameBet::class)->handle($game, $firstPlayer, 4, 600);
    app(PlaceGameBet::class)->handle($game, $secondPlayer, 6, 601);
    $proposal = GameArrivalProposal::factory()->create(['game_id' => $game->id, 'proposed_by' => $firstPlayer->id, 'arrival_minute' => 602]);
    GameArrivalVote::factory()->create(['game_arrival_proposal_id' => $proposal->id, 'user_id' => $firstPlayer->id, 'approved' => true]);

    app(ConfirmGameArrival::class)->handle($game, $proposal, $firstPlayer);

    expect($game->fresh()->winner_type)->toBe('sport')
        ->and($game->winner_user_id)->toBeNull()
        ->and($firstPlayer->fresh()->balance)->toBe(6)
        ->and($secondPlayer->fresh()->balance)->toBe(4);
});

test('daily property grant credits only zero balance users once and notifies them', function () {
    Notification::fake();
    $emptyUser = User::factory()->create(['balance' => 0]);
    $fundedUser = User::factory()->create(['balance' => 2]);

    $this->artisan('game:grant-daily-properties')->assertSuccessful();
    $this->artisan('game:grant-daily-properties')->assertSuccessful();

    expect($emptyUser->fresh()->balance)->toBe(1)
        ->and($fundedUser->fresh()->balance)->toBe(2);
    Notification::assertSentTo($emptyUser, DailyGamePropertyGranted::class, 1);
});

test('midnight closure closes unfinished games from prior days without winner', function () {
    $game = Game::factory()->started()->create(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()]);

    app(CloseExpiredGames::class)->handle();

    expect($game->fresh()->status)->toBe('closed')
        ->and($game->winner_user_id)->toBeNull();
});

test('administrator may close an unfinished game without a winner', function () {
    $administrator = User::factory()->create(['is_game_admin' => true]);
    $game = app(CreateGame::class)->handle($administrator);

    app(CloseGame::class)->handle($game);

    expect($game->fresh()->status)->toBe('closed')
        ->and($game->winner_user_id)->toBeNull();
});

test('dashboard exposes safe FantaDaniele DTOs through every current-team URL', function () {
    $user = User::factory()->create(['balance' => 3]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('game', null)
            ->where('myBet', null)
            ->where('balance.available', 3)
            ->where('balance.totalWon', 0)
            ->where('balance.totalPlayed', 0)
            ->where('isAdmin', false)
            ->where('arrivalProposal', null)
            ->has('votes', 0)
            ->has('leaderboard', 0)
            ->has('slots', 0)
            ->has('history', 0),
        );
});
