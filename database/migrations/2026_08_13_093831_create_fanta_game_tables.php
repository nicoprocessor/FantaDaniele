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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title')->default('FantaDaniele');
            $table->string('destination')->default('Da definire');
            $table->timestamp('departure_at');
            $table->string('status')->default('open')->index();
            $table->unsignedSmallInteger('arrival_minute')->nullable();
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('winner_type')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        DB::statement("CREATE UNIQUE INDEX games_one_active_global_game ON games (status) WHERE status IN ('open', 'started')");

        Schema::create('game_bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('amount');
            $table->unsignedSmallInteger('arrival_minute');
            $table->timestamps();

            $table->unique(['game_id', 'user_id']);
            $table->unique(['game_id', 'arrival_minute']);
        });

        Schema::create('game_arrival_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proposed_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('arrival_minute');
            $table->timestamps();
        });

        Schema::create('game_arrival_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_arrival_proposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('approved');
            $table->timestamps();

            $table->unique(['game_arrival_proposal_id', 'user_id'], 'game_arrival_votes_proposal_user_unique');
        });

        Schema::create('game_property_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('granted_on');
            $table->timestamps();

            $table->unique(['user_id', 'granted_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS games_one_active_global_game');
        Schema::dropIfExists('game_property_grants');
        Schema::dropIfExists('game_arrival_votes');
        Schema::dropIfExists('game_arrival_proposals');
        Schema::dropIfExists('game_bets');
        Schema::dropIfExists('games');
    }
};
