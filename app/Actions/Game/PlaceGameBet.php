<?php

namespace App\Actions\Game;

use App\Models\Game;
use App\Models\GameBet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaceGameBet
{
    public function handle(Game $game, User $user, int $amount, int $arrivalMinute): GameBet
    {
        return DB::transaction(function () use ($game, $user, $amount, $arrivalMinute): GameBet {
            $lockedGame = Game::query()->lockForUpdate()->findOrFail($game->id);
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if (! in_array($lockedGame->status, ['open', 'started'], true)) {
                throw ValidationException::withMessages(['game' => __('game.closed')]);
            }

            if ($amount < 1 || $amount > $lockedUser->balance) {
                throw ValidationException::withMessages(['amount' => __('game.amount_out_of_range')]);
            }

            if (GameBet::query()->whereBelongsTo($lockedGame)->whereBelongsTo($lockedUser)->exists()) {
                throw ValidationException::withMessages(['game' => __('game.already_bet')]);
            }

            if (GameBet::query()->whereBelongsTo($lockedGame)->where('arrival_minute', $arrivalMinute)->exists()) {
                throw ValidationException::withMessages(['arrival_minute' => __('game.arrival_taken')]);
            }

            $bet = GameBet::create(['game_id' => $lockedGame->id, 'user_id' => $lockedUser->id, 'amount' => $amount, 'arrival_minute' => $arrivalMinute]);
            $lockedUser->decrement('balance', $amount);

            if ($lockedGame->status === 'open' && $lockedGame->bets()->count() >= 2) {
                $lockedGame->update(['status' => 'started', 'started_at' => now()]);
            }

            return $bet;
        }, attempts: 3);
    }
}
