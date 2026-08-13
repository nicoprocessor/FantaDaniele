<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\Game\ProposeArrivalRequest;
use App\Models\Game;
use App\Models\GameArrivalProposal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class GameArrivalProposalController extends Controller
{
    public function store(ProposeArrivalRequest $request, Game $game): RedirectResponse
    {
        if ($game->status !== 'started' || ! $game->bets()->where('user_id', $request->user()->id)->exists()) {
            throw ValidationException::withMessages(['game' => 'Only players in a started game can propose an arrival.']);
        }

        GameArrivalProposal::create(['game_id' => $game->id, 'proposed_by' => $request->user()->id, 'arrival_minute' => $request->integer('arrival_minute')]);

        return back();
    }
}
