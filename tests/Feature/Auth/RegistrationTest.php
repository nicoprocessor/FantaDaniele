<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('registration screen does not expose team invitation context', function () {
    $this->get(route('register', ['invitation' => 'disabled']))
        ->assertInertia(fn (Assert $page) => $page->missing('teamInvitation'));
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->first();
    $response->assertRedirect(route('dashboard'));
    expect($user->currentTeam?->name)->toBe('Gruppo Daniele')
        ->and($user->currentTeam?->is_personal)->toBeFalse();
});
