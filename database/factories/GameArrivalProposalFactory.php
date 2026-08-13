<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GameArrivalProposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameArrivalProposal>
 */
class GameArrivalProposalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['game_id' => Game::factory(), 'proposed_by' => User::factory(), 'arrival_minute' => fake()->numberBetween(0, 1439)];
    }
}
