<?php

use App\Actions\Fortify\CreateNewUser;
use App\AvatarSeeds;
use App\Enums\TeamRole;
use App\JoinDefaultTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('shared-team backfill preserves existing membership roles', function () {
    $owner = User::query()->forceCreate(User::factory()->raw());
    $member = User::query()->forceCreate(User::factory()->raw());
    $team = Team::query()->firstWhere('slug', 'gruppo-daniele');

    $team->members()->attach($owner, ['role' => TeamRole::Owner->value]);
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);

    (require database_path('migrations/2026_08_18_083855_backfill_gruppo_daniele_team.php'))->up();

    expect($team->fresh()->memberships()->where('user_id', $owner->id)->value('role'))->toBe(TeamRole::Owner)
        ->and($team->fresh()->memberships()->where('user_id', $member->id)->value('role'))->toBe(TeamRole::Member);
});

test('joining the default team preserves an existing owner role', function () {
    $user = User::factory()->create();

    app(JoinDefaultTeam::class)->handle($user);

    expect($user->fresh()->teamRole($user->fresh()->currentTeam))->toBe(TeamRole::Owner);
});

test('forward repair appoints the first member as owner without demoting other members', function () {
    $firstMember = User::query()->forceCreate(User::factory()->raw());
    $secondMember = User::query()->forceCreate(User::factory()->raw());
    $team = Team::query()->firstWhere('slug', 'gruppo-daniele');

    $team->members()->attach($firstMember, ['role' => TeamRole::Member->value]);
    $team->members()->attach($secondMember, ['role' => TeamRole::Member->value]);

    (require database_path('migrations/2026_08_18_092936_restore_default_team_owner_and_require_avatar_seed.php'))->up();

    expect($team->fresh()->memberships()->where('user_id', $firstMember->id)->value('role'))->toBe(TeamRole::Owner)
        ->and($team->fresh()->memberships()->where('user_id', $secondMember->id)->value('role'))->toBe(TeamRole::Member)
        ->and(DB::table('team_members')->where('team_id', $team->id)->where('role', TeamRole::Owner->value)->count())->toBe(1);
});

test('registration persists an approved deterministic avatar seed', function () {
    $user = app(CreateNewUser::class)->create([
        'name' => 'Nuovo utente',
        'email' => 'nuovo@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect($user->avatar_seed)->toBeIn(AvatarSeeds::all());
});
