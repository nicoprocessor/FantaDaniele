<?php

use App\Models\Game;
use App\Models\GameBet;
use App\Models\GamePropertyGrant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('global product pages expose history ranking and statistics DTOs', function () {
    $winner = User::factory()->create(['balance' => 12]);
    $player = User::factory()->create(['balance' => 4]);
    $game = Game::factory()->closed()->create([
        'winner_user_id' => $winner->id,
        'winner_type' => 'exact',
        'arrival_minute' => 610,
        'closed_at' => now(),
    ]);
    GameBet::factory()->create(['game_id' => $game->id, 'user_id' => $winner->id, 'arrival_minute' => 610, 'amount' => 4]);
    GameBet::factory()->create(['game_id' => $game->id, 'user_id' => $player->id, 'arrival_minute' => 611, 'amount' => 3]);

    $this->actingAs($winner)->get(route('games.index'))->assertInertia(fn (Assert $page) => $page
        ->component('games/index')
        ->where('history.0.actualArrivalTime', '10:10')
        ->where('history.0.winnerName', $winner->name)
        ->has('history.0.participants', 2));
    $this->actingAs($winner)->get(route('leaderboard.index'))->assertInertia(fn (Assert $page) => $page
        ->component('leaderboard/index')
        ->where('leaderboard.0.id', $winner->id)
        ->where('leaderboard.0.wins', 1));
    $this->actingAs($winner)->get(route('statistics.index'))->assertInertia(fn (Assert $page) => $page
        ->component('statistics/index')
        ->has('arrivalTrend', 1)
        ->has('propertyTrend'));
});

test('active games are not counted as losses', function () {
    $user = User::factory()->create(['balance' => 5]);
    $otherUser = User::factory()->create(['balance' => 5]);
    $game = Game::factory()->started()->create();
    GameBet::factory()->create(['game_id' => $game->id, 'user_id' => $user->id]);
    GameBet::factory()->create(['game_id' => $game->id, 'user_id' => $otherUser->id, 'arrival_minute' => 700]);

    $this->actingAs($user)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
        ->where('metrics.gamesPlayed', 1)
        ->where('metrics.losses', 0));
});

test('administratively closed games without an exact winner count as draws', function () {
    $user = User::factory()->create(['balance' => 5]);
    $game = Game::factory()->closed()->create([
        'winner_type' => null,
        'winner_user_id' => null,
    ]);
    GameBet::factory()->create(['game_id' => $game->id, 'user_id' => $user->id]);

    $this->actingAs($user)->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
        ->where('metrics.gamesPlayed', 1)
        ->where('metrics.wins', 0)
        ->where('metrics.draws', 1)
        ->where('metrics.losses', 0));
});

test('statistics exposes empty trends when no games have closed', function () {
    $user = User::factory()->create();
    User::factory()->create();

    $this->actingAs($user)->get(route('statistics.index'))->assertInertia(fn (Assert $page) => $page
        ->where('arrivalTrend', [])
        ->where('propertyLabels', [])
        ->where('propertyTrend', []));
});

test('statistics reconstructs closed-game property balances from grants bets and payouts', function () {
    $alice = User::factory()->create(['name' => 'Alice', 'balance' => 13]);
    $bruno = User::factory()->create(['name' => 'Bruno', 'balance' => 8]);
    $firstClosedAt = now()->subDays(2);
    $secondClosedAt = now()->subDay();
    $firstGame = Game::factory()->closed()->create(['winner_user_id' => $alice->id, 'winner_type' => 'exact', 'closed_at' => $firstClosedAt]);
    $secondGame = Game::factory()->closed()->create(['winner_user_id' => $bruno->id, 'winner_type' => 'exact', 'closed_at' => $secondClosedAt]);
    GameBet::factory()->create(['game_id' => $firstGame->id, 'user_id' => $alice->id, 'amount' => 4, 'created_at' => $firstClosedAt->subHour()]);
    GameBet::factory()->create(['game_id' => $firstGame->id, 'user_id' => $bruno->id, 'amount' => 3, 'created_at' => $firstClosedAt->subHour()]);
    GamePropertyGrant::factory()->create(['user_id' => $alice->id, 'created_at' => $firstClosedAt->addHour()]);
    GameBet::factory()->create(['game_id' => $secondGame->id, 'user_id' => $alice->id, 'amount' => 1, 'created_at' => $secondClosedAt->subHour()]);
    GameBet::factory()->create(['game_id' => $secondGame->id, 'user_id' => $bruno->id, 'amount' => 2, 'created_at' => $secondClosedAt->subHour()]);

    $response = $this->actingAs($alice)->get(route('statistics.index'));
    $response->assertInertia(fn (Assert $page) => $page->has('propertyTrend'));
    $trend = $response->viewData('page')['props']['propertyTrend'];

    expect(collect($trend)->firstWhere('name', 'Alice')['values'])->toBe([13, 13])
        ->and(collect($trend)->firstWhere('name', 'Bruno')['values'])->toBe([7, 8]);
});

test('statistics property series expose stable player identifiers', function () {
    $firstPlayer = User::factory()->create(['name' => 'Daniele']);
    $secondPlayer = User::factory()->create(['name' => 'Daniele']);
    Game::factory()->closed()->create();

    $trend = $this->actingAs($firstPlayer)
        ->get(route('statistics.index'))
        ->viewData('page')['props']['propertyTrend'];

    expect(collect($trend)->pluck('id')->all())
        ->toContain($firstPlayer->id, $secondPlayer->id);
});
