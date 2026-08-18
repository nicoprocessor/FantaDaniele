<?php

namespace App\Http\Controllers;

use App\GamePageData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class GameIndexController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, GamePageData $gamePageData): Response
    {
        return Inertia::render('games/index', $gamePageData->games($request->user()));
    }
}
