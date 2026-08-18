<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $timestamp = now();
        $team = DB::table('teams')->where('slug', 'gruppo-daniele')->first();

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

        $ownerExists = DB::table('team_members')
            ->where('team_id', $teamId)
            ->where('role', 'owner')
            ->exists();

        if (! $ownerExists) {
            $memberId = DB::table('team_members')
                ->where('team_id', $teamId)
                ->orderBy('user_id')
                ->value('user_id');

            if ($memberId !== null) {
                DB::table('team_members')
                    ->where('team_id', $teamId)
                    ->where('user_id', $memberId)
                    ->update(['role' => 'owner', 'updated_at' => $timestamp]);
            } else {
                $userId = DB::table('users')->orderBy('id')->value('id');

                if ($userId !== null) {
                    DB::table('team_members')->insert([
                        'team_id' => $teamId,
                        'user_id' => $userId,
                        'role' => 'owner',
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }
            }
        }

        $seeds = ['Ariete', 'Caffè', 'Ciclamino', 'Corallo', 'Focaccia', 'Gelsomino', 'Lenticchia', 'Mandarino', 'Mirtillo', 'Pistacchio', 'Sole', 'Tiramisu'];

        DB::table('users')->whereNull('avatar_seed')->orderBy('id')->eachById(function (object $user) use ($seeds): void {
            DB::table('users')->where('id', $user->id)->update([
                'avatar_seed' => $seeds[$user->id % count($seeds)],
            ]);
        });

        $memberships = DB::table('team_members')->get();

        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('avatar_seed')->nullable(false)->change();
            });
        });

        $memberships->each(function (object $membership): void {
            DB::table('team_members')->insertOrIgnore((array) $membership);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('avatar_seed')->nullable()->change();
            });
        });
    }
};
