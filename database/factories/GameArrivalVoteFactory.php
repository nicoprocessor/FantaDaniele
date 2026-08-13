<?php

namespace Database\Factories;

use App\Models\GameArrivalProposal;
use App\Models\GameArrivalVote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameArrivalVote>
 */
class GameArrivalVoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['game_arrival_proposal_id' => GameArrivalProposal::factory(), 'user_id' => User::factory(), 'approved' => true];
    }
}
