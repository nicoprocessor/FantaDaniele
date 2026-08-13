<?php

namespace App\Http\Controllers\Game;

use App\Actions\Game\PlaceGameBet;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\PlaceGameBetRequest;
use App\Models\Game;
use Illuminate\Http\RedirectResponse;

class GameBetController extends Controller
{
    public function store(PlaceGameBetRequest $request, Game $game, PlaceGameBet $placeGameBet): RedirectResponse
    {
        $placeGameBet->handle($game, $request->user(), $request->integer('amount'), $request->integer('arrival_minute'));

        return back();
    }
}
