<?php

use App\Actions\Game\CloseExpiredGames;
use App\Actions\Game\ConfirmGameArrival;
use App\Actions\Game\CreateGame;
use App\Actions\Game\PlaceGameBet;
use App\Models\Game;
use App\Models\GameArrivalProposal;
use App\Models\GameArrivalVote;
use App\Models\GameBet;
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

test('closed game history does not consume the single active-game slot', function () {
    Game::factory()->closed()->create();
    Game::factory()->closed()->create();

    expect(Game::query()->where('status', 'closed')->count())->toBe(2);
});

test('cancelling an active game refunds every stake and removes its cascaded records', function () {
    $owner = User::factory()->create(['balance' => 6]);
    $player = User::factory()->create(['balance' => 1]);
    $game = Game::factory()->started()->create(['created_by' => $owner->id]);
    $ownerBet = GameBet::factory()->create(['game_id' => $game->id, 'user_id' => $owner->id, 'amount' => 4]);
    $playerBet = GameBet::factory()->create(['game_id' => $game->id, 'user_id' => $player->id, 'amount' => 6]);
    $proposal = GameArrivalProposal::factory()->create(['game_id' => $game->id, 'proposed_by' => $owner->id]);
    $vote = GameArrivalVote::factory()->create(['game_arrival_proposal_id' => $proposal->id, 'user_id' => $owner->id]);

    $this->actingAs($owner)->delete(route('games.destroy', $game))->assertRedirect(route('games.index'));

    expect($owner->fresh()->balance)->toBe(10)
        ->and($player->fresh()->balance)->toBe(7);
    $this->assertModelMissing($game);
    $this->assertModelMissing($ownerBet);
    $this->assertModelMissing($playerBet);
    $this->assertModelMissing($proposal);
    $this->assertModelMissing($vote);
});

test('only the game owner or an explicit administrator of an orphaned game can manage it', function () {
    $owner = User::factory()->create();
    $globalAdministrator = User::factory()->create(['is_game_admin' => true]);
    $game = Game::factory()->started()->create(['created_by' => $owner->id]);

    $this->actingAs($globalAdministrator)->delete(route('games.destroy', $game))->assertForbidden();
    $this->actingAs($owner)->delete(route('games.destroy', $game))->assertRedirect(route('games.index'));

    $orphanedGame = Game::factory()->started()->create(['created_by' => null]);
    $this->actingAs($globalAdministrator)->delete(route('games.destroy', $orphanedGame))->assertRedirect(route('games.index'));
});

test('cancelling a closed game returns an explicit Italian validation error', function () {
    $owner = User::factory()->create();
    $game = Game::factory()->closed()->create(['created_by' => $owner->id]);

    $this->actingAs($owner)
        ->delete(route('games.destroy', $game))
        ->assertSessionHasErrors(['game' => __('game.cannot_cancel')]);
});

test('dashboard exposes safe FantaDaniele DTOs through the global URL', function () {
    $user = User::factory()->create(['balance' => 3]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('game', null)
            ->where('metrics.available', 3)
            ->where('metrics.gamesPlayed', 0)
            ->where('metrics.wins', 0)
            ->where('metrics.draws', 0)
            ->where('metrics.losses', 0)
            ->where('metrics.winRate', 0)
            ->where('canStartGame', true)
            ->has('leaderboard', 1)
            ->missing('myBet')
            ->missing('arrivalProposal')
            ->missing('votes')
            ->missing('pendingInvitations'),
        );
});

test('dashboard exposes an active game as a compact overview only', function () {
    $owner = User::factory()->create();
    $player = User::factory()->create();
    $game = Game::factory()->started()->create(['created_by' => $owner->id]);
    GameBet::factory()->create(['game_id' => $game->id, 'user_id' => $player->id, 'amount' => 4]);

    $this->actingAs($player)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
        ->where('game.id', $game->id)
        ->where('game.participantCount', 1)
        ->where('game.houseAmount', 4)
        ->where('game.owner.id', $owner->id)
        ->missing('game.participants')
        ->missing('myBet')
        ->missing('arrivalProposal'));
});

test('game bet validation errors are actionable in Italian', function () {
    $user = User::factory()->create(['balance' => 5]);
    $game = Game::factory()->create();

    $this->actingAs($user)
        ->post(route('games.bets.store', $game), [])
        ->assertSessionHasErrors([
            'amount' => 'Il campo puntata è obbligatorio.',
            'arrival_minute' => 'Il campo orario di arrivo è obbligatorio.',
        ]);

    $this->actingAs($user)
        ->post(route('games.bets.store', $game), ['amount' => 0, 'arrival_minute' => 600])
        ->assertSessionHasErrors(['amount' => 'Il campo puntata deve essere almeno 1.']);
});

test('active game detail exposes every proposal with its own votes and server clock contract', function () {
    $player = User::factory()->create();
    $game = Game::factory()->started()->create();
    $previousProposal = GameArrivalProposal::factory()->create([
        'game_id' => $game->id,
        'proposed_by' => $player->id,
        'created_at' => now()->subMinute(),
    ]);
    $currentProposal = GameArrivalProposal::factory()->create([
        'game_id' => $game->id,
        'proposed_by' => $player->id,
        'created_at' => now(),
    ]);
    GameArrivalVote::factory()->create([
        'game_arrival_proposal_id' => $previousProposal->id,
        'user_id' => $player->id,
    ]);
    GameArrivalVote::factory()->create([
        'game_arrival_proposal_id' => $previousProposal->id,
        'user_id' => User::factory()->create()->id,
    ]);
    $currentVote = GameArrivalVote::factory()->create([
        'game_arrival_proposal_id' => $currentProposal->id,
        'user_id' => $player->id,
    ]);

    $this->actingAs($player)->get(route('games.show', $game))->assertInertia(fn (Assert $page) => $page
        ->component('games/show')
        ->where('game.owner.id', $game->created_by)
        ->has('proposals', 2)
        ->where('proposals.0.id', $currentProposal->id)
        ->has('proposals.0.votes', 1)
        ->where('proposals.0.votes.0.id', $currentVote->id)
        ->has('proposals.1.votes', 2)
        ->where('canManageGame', false)
        ->has('serverNow')
        ->where('closesAt', $game->created_at->copy()->setTimezone(config('app.timezone'))->addDay()->startOfDay()->toIso8601String()));
});

test('only active games have a dedicated live page', function () {
    $player = User::factory()->create();
    $closedGame = Game::factory()->closed()->create();

    $this->actingAs($player)->get(route('games.show', $closedGame))->assertNotFound();
});

test('only the owner may confirm a majority proposal for a non-orphaned game', function () {
    $owner = User::factory()->create();
    $globalAdministrator = User::factory()->create(['is_game_admin' => true]);
    $game = Game::factory()->started()->create(['created_by' => $owner->id]);
    $proposal = GameArrivalProposal::factory()->create(['game_id' => $game->id, 'proposed_by' => $owner->id]);
    GameArrivalVote::factory()->create(['game_arrival_proposal_id' => $proposal->id, 'user_id' => $owner->id, 'approved' => true]);

    $this->actingAs($globalAdministrator)
        ->post(route('games.arrival.confirm', $game), ['proposal_id' => $proposal->id])
        ->assertForbidden();
    $this->actingAs($owner)
        ->post(route('games.arrival.confirm', $game), ['proposal_id' => $proposal->id])
        ->assertRedirect();

    expect($game->fresh()->status)->toBe('closed');
});

test('only the default team owner or explicit game admin can administer games', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $explicitAdmin = User::factory()->create(['is_game_admin' => true]);

    expect($owner->canAdministerGames())->toBeTrue()
        ->and($member->canAdministerGames())->toBeFalse()
        ->and($explicitAdmin->canAdministerGames())->toBeTrue();
});

test('only game administrators can start a game through the global endpoint', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $this->actingAs($member)->post(route('games.store'))->assertForbidden();
    $this->assertDatabaseCount('games', 0);

    $this->actingAs($owner)->post(route('games.store'))->assertRedirect();
    $this->assertDatabaseCount('games', 1);
});

test('an explicit game administrator can start a game through the global endpoint', function () {
    User::factory()->create();
    $administrator = User::factory()->create(['is_game_admin' => true]);

    $this->actingAs($administrator)->post(route('games.store'))->assertRedirect();

    $this->assertDatabaseCount('games', 1);
});
