<?php

namespace App\Http\Controllers\Game;

use App\Actions\Game\CloseGame;
use App\Actions\Game\CreateGame;
use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function store(Request $request, CreateGame $createGame): RedirectResponse
    {
        $createGame->handle($request->user());

        return back();
    }

    public function destroy(Request $request, Game $game, CloseGame $closeGame): RedirectResponse
    {
        abort_unless($request->user()->canAdministerGames(), 403);

        $closeGame->handle($game);

        return back();
    }
}
