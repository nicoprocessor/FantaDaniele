<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['created_by' => User::factory(), 'departure_at' => now(), 'status' => 'open'];
    }

    public function started(): static
    {
        return $this->state(fn () => ['status' => 'started', 'started_at' => now()]);
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => 'closed', 'closed_at' => now()]);
    }
}
