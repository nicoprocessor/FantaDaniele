<?php

namespace App\Http\Controllers;

use App\GamePageData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class StatisticsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, GamePageData $gamePageData): Response
    {
        return Inertia::render('statistics/index', $gamePageData->statistics($request->user()));
    }
}
