<?php

namespace App\Actions\Game;

use App\Models\Game;
use App\Models\GameArrivalProposal;
use App\Models\GameBet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConfirmGameArrival
{
    public function handle(Game $game, GameArrivalProposal $proposal, User $user): Game
    {
        return DB::transaction(function () use ($game, $proposal): Game {
            $lockedGame = Game::query()->lockForUpdate()->findOrFail($game->id);
            $lockedProposal = GameArrivalProposal::query()->lockForUpdate()->findOrFail($proposal->id);

            if ($lockedProposal->game_id !== $lockedGame->id || $lockedGame->status !== 'started') {
                throw ValidationException::withMessages(['proposal' => 'Arrival proposal cannot be confirmed for this game.']);
            }

            $votes = $lockedProposal->votes()->lockForUpdate()->get();
            $approvalCount = $votes->where('approved', true)->count();
            if ($votes->isEmpty() || $approvalCount * 2 <= $votes->count()) {
                throw ValidationException::withMessages(['proposal' => 'Arrival proposal has not reached majority approval.']);
            }

            $exactBet = GameBet::query()->whereBelongsTo($lockedGame)->where('arrival_minute', $lockedProposal->arrival_minute)->first();
            $winner = $exactBet === null ? null : User::query()->lockForUpdate()->findOrFail($exactBet->user_id);
            $pool = (int) $lockedGame->bets()->sum('amount');

            if ($winner !== null) {
                $winner->increment('balance', $pool);
            }

            $lockedGame->update([
                'status' => 'closed',
                'arrival_minute' => $lockedProposal->arrival_minute,
                'winner_user_id' => $winner?->id,
                'winner_type' => $exactBet === null ? 'sport' : 'exact',
                'closed_at' => now(),
            ]);

            return $lockedGame->fresh();
        }, attempts: 3);
    }
}
