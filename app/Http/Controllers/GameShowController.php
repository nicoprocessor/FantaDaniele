<?php

namespace App\Http\Controllers;

use App\GamePageData;
use App\Models\Game;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class GameShowController extends Controller
{
    public function __invoke(Request $request, Game $game, GamePageData $gamePageData): Response
    {
        abort_unless(in_array($game->status, ['open', 'started'], true), 404);

        return Inertia::render('games/show', $gamePageData->show($game, $request->user()));
    }
}
