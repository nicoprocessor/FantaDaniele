<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\Game\VoteOnArrivalRequest;
use App\Models\Game;
use App\Models\GameArrivalProposal;
use App\Models\GameArrivalVote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class GameArrivalVoteController extends Controller
{
    public function store(VoteOnArrivalRequest $request, Game $game, GameArrivalProposal $proposal): RedirectResponse
    {
        if ($proposal->game_id !== $game->id || $game->status !== 'started' || ! $game->bets()->where('user_id', $request->user()->id)->exists()) {
            throw ValidationException::withMessages(['proposal' => __('game.cannot_vote')]);
        }

        GameArrivalVote::query()->updateOrCreate(
            ['game_arrival_proposal_id' => $proposal->id, 'user_id' => $request->user()->id],
            ['approved' => $request->boolean('approved')],
        );

        return back();
    }
}
