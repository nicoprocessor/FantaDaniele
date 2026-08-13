<?php

namespace App\Actions\Game;

use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateGame
{
    public function handle(User $user): Game
    {
        return DB::transaction(function () use ($user): Game {
            $activeGame = Game::query()->whereIn('status', ['open', 'started'])->lockForUpdate()->first();

            if ($activeGame !== null) {
                throw ValidationException::withMessages(['game' => 'A global game is already active.']);
            }

            return Game::create(['created_by' => $user->id, 'departure_at' => now()]);
        }, attempts: 3);
    }
}
