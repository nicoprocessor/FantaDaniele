<?php

namespace App\Http\Controllers\Game;

use App\Actions\Game\ConfirmGameArrival;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\ConfirmArrivalRequest;
use App\Models\Game;
use App\Models\GameArrivalProposal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class GameArrivalConfirmationController extends Controller
{
    public function store(ConfirmArrivalRequest $request, Game $game, ConfirmGameArrival $confirmGameArrival): RedirectResponse
    {
        abort_unless($this->canManageGame($request->user(), $game), 403);

        $proposal = GameArrivalProposal::query()->findOrFail($request->integer('proposal_id'));
        $confirmGameArrival->handle($game, $proposal, $request->user());

        return back();
    }

    private function canManageGame(User $user, Game $game): bool
    {
        return $game->created_by === null
            ? $user->is_game_admin
            : $game->created_by === $user->id;
    }
}
