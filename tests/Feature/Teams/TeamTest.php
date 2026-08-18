<?php

use App\Models\User;

test('team management and invitation endpoints are unavailable during the shared league phase', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/settings/teams')->assertNotFound();
    $this->actingAs($user)->post('/settings/teams')->assertNotFound();
    $this->actingAs($user)->get('/settings/teams/gruppo-daniele')->assertNotFound();
    $this->actingAs($user)->post('/settings/teams/gruppo-daniele/switch')->assertNotFound();
    $this->actingAs($user)->delete('/settings/teams/gruppo-daniele/leave')->assertNotFound();
    $this->actingAs($user)->patch('/settings/teams/gruppo-daniele/members/1')->assertNotFound();
    $this->actingAs($user)->post('/settings/teams/gruppo-daniele/invitations')->assertNotFound();
    $this->actingAs($user)->post('/invitations/1/accept')->assertNotFound();
    $this->actingAs($user)->delete('/invitations/1')->assertNotFound();
});

test('profile security appearance and passkey discovery routes remain available', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('profile.edit'))->assertOk();
    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertOk();
    $this->actingAs($user)->get(route('appearance.edit'))->assertOk();
    $this->get(route('well-known.passkeys'))->assertOk();
});
