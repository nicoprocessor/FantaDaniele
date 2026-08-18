<?php

namespace App;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;

final class JoinDefaultTeam
{
    public function handle(User $user): Team
    {
        $team = Team::query()->withTrashed()->firstOrCreate(
            ['slug' => 'gruppo-daniele'],
            ['name' => 'Gruppo Daniele', 'is_personal' => false],
        );

        if ($team->trashed()) {
            $team->restore();
        }

        $lockedTeam = Team::query()->lockForUpdate()->findOrFail($team->id);
        $membership = $lockedTeam->memberships()->where('user_id', $user->id)->first();

        if ($membership === null) {
            $role = $lockedTeam->memberships()->where('role', TeamRole::Owner->value)->exists()
                ? TeamRole::Member
                : TeamRole::Owner;

            $lockedTeam->memberships()->create([
                'user_id' => $user->id,
                'role' => $role,
            ]);
        }

        $user->forceFill(['current_team_id' => $team->id])->save();

        return $lockedTeam;
    }
}
