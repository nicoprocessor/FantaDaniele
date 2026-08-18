<?php

namespace App\Actions\Game;

use App\Models\Game;
use App\Models\GameBet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CancelGame
{
    public function handle(Game $game): void
    {
        DB::transaction(function () use ($game): void {
            $lockedGame = Game::query()->lockForUpdate()->findOrFail($game->id);

            if (! in_array($lockedGame->status, ['open', 'started'], true)) {
                throw ValidationException::withMessages(['game' => __('game.cannot_cancel')]);
            }

            $bets = GameBet::query()
                ->whereBelongsTo($lockedGame)
                ->orderBy('user_id')
                ->lockForUpdate()
                ->get();
            $users = User::query()
                ->whereKey($bets->pluck('user_id')->unique()->sort()->values())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $bets->groupBy('user_id')->each(function ($userBets, int $userId) use ($users): void {
                $user = $users->get($userId);

                if ($user === null) {
                    throw ValidationException::withMessages(['game' => __('game.cannot_cancel')]);
                }

                $user->increment('balance', $userBets->sum('amount'));
            });

            $lockedGame->delete();
        }, attempts: 3);
    }
}
