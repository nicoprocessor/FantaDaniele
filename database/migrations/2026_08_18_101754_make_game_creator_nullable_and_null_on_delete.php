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
        $usesPartialActiveGameIndex = in_array(DB::getDriverName(), ['sqlite', 'pgsql'], true);

        if ($usesPartialActiveGameIndex) {
            DB::statement('DROP INDEX IF EXISTS games_one_active_global_game');
        }

        Schema::table('games', function (Blueprint $table): void {
            $table->dropForeign(['created_by']);
            $table->foreignId('created_by')->nullable()->change();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        if ($usesPartialActiveGameIndex) {
            DB::statement("CREATE UNIQUE INDEX games_one_active_global_game ON games (status) WHERE status IN ('open', 'started')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
