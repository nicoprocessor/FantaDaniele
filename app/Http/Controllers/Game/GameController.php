<?php

namespace App\Http\Controllers\Game;

use App\Actions\Game\CancelGame;
use App\Actions\Game\CreateGame;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function store(Request $request, CreateGame $createGame): RedirectResponse
    {
        abort_unless($request->user()->canAdministerGames(), 403);

        $createGame->handle($request->user());

        return back();
    }

    public function destroy(Request $request, Game $game, CancelGame $cancelGame): RedirectResponse
    {
        abort_unless($this->canManageGame($request->user(), $game), 403);

        $cancelGame->handle($game);

        return to_route('games.index');
    }

    private function canManageGame(User $user, Game $game): bool
    {
        return $game->created_by === null
            ? $user->is_game_admin
            : $game->created_by === $user->id;
    }
}
