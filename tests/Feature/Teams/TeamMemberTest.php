<?php

use App\Models\User;

test('team member endpoints remain unavailable during the shared league phase', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch('/settings/teams/gruppo-daniele/members/1', [
        'role' => 'admin',
    ])->assertNotFound();
    $this->actingAs($user)->delete('/settings/teams/gruppo-daniele/members/1')->assertNotFound();
});
