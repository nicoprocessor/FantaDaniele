<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GameBet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameBet>
 */
class GameBetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['game_id' => Game::factory(), 'user_id' => User::factory(), 'amount' => fake()->numberBetween(1, 10), 'arrival_minute' => fake()->numberBetween(0, 1439)];
    }
}
