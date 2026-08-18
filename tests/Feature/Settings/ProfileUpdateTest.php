<?php

use App\Enums\TeamRole;
use App\Models\Game;
use App\Models\GameBet;
use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar_seed' => $user->avatar_seed,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('profile validation errors are actionable in Italian', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [])
        ->assertSessionHasErrors([
            'name' => 'Il campo nome è obbligatorio.',
            'email' => 'Il campo email è obbligatorio.',
            'avatar_seed' => 'Il campo avatar è obbligatorio.',
        ]);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
            'avatar_seed' => $user->avatar_seed,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull()
        ->and($team->fresh()->memberships()->where('role', TeamRole::Owner->value)->exists())->toBeFalse();
});

test('deleting the shared league owner promotes the first remaining member', function () {
    $owner = User::factory()->create();
    $firstMember = User::factory()->create();
    $secondMember = User::factory()->create();
    $team = $owner->currentTeam;

    $this->actingAs($owner)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect(route('home'));

    expect($team->fresh()->memberships()->where('user_id', $firstMember->id)->first()?->role)->toBe(TeamRole::Owner)
        ->and($team->fresh()->memberships()->where('user_id', $secondMember->id)->first()?->role)->toBe(TeamRole::Member)
        ->and($team->fresh()->memberships()->where('role', TeamRole::Owner->value)->count())->toBe(1);
});

test('deleting a game creator preserves closed game history and other players bets', function () {
    $creator = User::factory()->create();
    $participant = User::factory()->create();
    $game = Game::factory()->closed()->create([
        'created_by' => $creator->id,
        'winner_user_id' => $participant->id,
        'winner_type' => 'exact',
    ]);
    $bet = GameBet::factory()->create([
        'game_id' => $game->id,
        'user_id' => $participant->id,
    ]);

    $this->actingAs($creator)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect(route('home'));

    expect($game->fresh())
        ->not->toBeNull()
        ->and($game->fresh()->created_by)->toBeNull()
        ->and($game->fresh()->winner_user_id)->toBe($participant->id)
        ->and($bet->fresh())
        ->not->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh())->not->toBeNull();
});
