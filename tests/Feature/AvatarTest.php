<?php

use App\AvatarSeeds;
use App\Models\User;

test('open peeps avatars are rendered locally with cache headers', function () {
    $user = User::factory()->create(['avatar_seed' => 'Ciclamino']);

    $this->get(route('avatars.show', $user))
        ->assertOk()
        ->assertHeader('content-type', 'image/svg+xml; charset=UTF-8')
        ->assertHeader('cache-control', 'immutable, max-age=604800, public')
        ->assertSee('<svg', false);
});

test('profile rejects values that are not approved Open Peeps seeds', function (string $avatarSeed) {
    $user = User::factory()->create();

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar_seed' => $avatarSeed,
    ])->assertSessionHasErrors('avatar_seed');
})->with([
    'unknown seed' => 'non-approvato',
    'DiceBear API URL' => 'https://api.dicebear.com/10.x/open-peeps/svg?seed=Sole',
    'different style' => 'lorelei:Sole',
    'style and seed parameters' => 'style=open-peeps&seed=Sole',
]);

test('profile requires an approved avatar seed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar_seed' => null,
    ])->assertSessionHasErrors('avatar_seed');
});

test('factories persist approved avatar seeds', function () {
    $user = User::factory()->create();

    expect($user->avatar_seed)->toBeIn(AvatarSeeds::all());
});

test('profile accepts an approved Open Peeps seed', function () {
    $user = User::factory()->create(['avatar_seed' => 'Ciclamino']);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar_seed' => 'Mandarino',
    ])->assertSessionHasNoErrors();

    expect($user->refresh()->avatar_seed)->toBe('Mandarino');
});

test('profile exposes authenticated Open Peeps previews and a versioned user avatar URL', function () {
    $user = User::factory()->create(['avatar_seed' => 'Ciclamino']);

    $this->actingAs($user)->get(route('profile.edit'))
        ->assertInertia(fn ($page) => $page
            ->has('avatarSeeds', 12)
            ->where('avatarSeeds.2', 'Ciclamino')
            ->where('auth.user.avatar', route('avatars.show', $user).'?v=Ciclamino'));

    $this->actingAs($user)->get(route('avatars.previews.show', 'Ciclamino'))->assertOk()->assertSee('<svg', false);
    $this->actingAs($user)->get(route('avatars.previews.show', 'invalid'))->assertNotFound();
});
