<?php

namespace App\Actions\Game;

use App\Models\Game;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CloseGame
{
    public function handle(Game $game): Game
    {
        return DB::transaction(function () use ($game): Game {
            $lockedGame = Game::query()->lockForUpdate()->findOrFail($game->id);

            if (! in_array($lockedGame->status, ['open', 'started'], true)) {
                throw ValidationException::withMessages(['game' => 'This game is already closed.']);
            }

            $lockedGame->update(['status' => 'closed', 'closed_at' => now()]);

            return $lockedGame->fresh();
        }, attempts: 3);
    }
}
