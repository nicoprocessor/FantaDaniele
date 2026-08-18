<?php

use App\Models\User;

test('team invitation endpoints remain unavailable during the shared league phase', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/settings/teams/gruppo-daniele/invitations')->assertNotFound();
    $this->actingAs($user)->delete('/settings/teams/gruppo-daniele/invitations/invitation-code')->assertNotFound();
    $this->actingAs($user)->post('/invitations/invitation-code/accept')->assertNotFound();
    $this->actingAs($user)->delete('/invitations/invitation-code')->assertNotFound();
});
