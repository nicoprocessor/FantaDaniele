<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $team = DB::table('teams')->where('slug', 'gruppo-daniele')->first();
        $timestamp = now();

        if ($team === null) {
            $teamId = DB::table('teams')->insertGetId([
                'name' => 'Gruppo Daniele',
                'slug' => 'gruppo-daniele',
                'is_personal' => false,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        } else {
            $teamId = $team->id;
            DB::table('teams')->where('id', $teamId)->update([
                'name' => 'Gruppo Daniele',
                'is_personal' => false,
                'deleted_at' => null,
                'updated_at' => $timestamp,
            ]);
        }

        $ownerExists = DB::table('team_members')->where('team_id', $teamId)->where('role', 'owner')->exists();
        $seeds = ['Ariete', 'Caffè', 'Ciclamino', 'Corallo', 'Focaccia', 'Gelsomino', 'Lenticchia', 'Mandarino', 'Mirtillo', 'Pistacchio', 'Sole', 'Tiramisu'];

        DB::table('users')->orderBy('id')->eachById(function (object $user) use ($teamId, $timestamp, &$ownerExists, $seeds): void {
            $membership = DB::table('team_members')
                ->where('team_id', $teamId)
                ->where('user_id', $user->id)
                ->first();

            if ($membership === null) {
                $role = $ownerExists ? 'member' : 'owner';

                DB::table('team_members')->insert([
                    'team_id' => $teamId,
                    'user_id' => $user->id,
                    'role' => $role,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                $ownerExists = $role === 'owner';
            }

            DB::table('users')->where('id', $user->id)->update([
                'current_team_id' => $teamId,
                'avatar_seed' => $user->avatar_seed ?? $seeds[$user->id % count($seeds)],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This data migration intentionally preserves existing teams and memberships.
    }
};
