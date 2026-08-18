<?php

namespace App\Http\Controllers;

use App\GamePageData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, GamePageData $gamePageData): Response
    {
        return Inertia::render('dashboard', $gamePageData->dashboard($request->user()));
    }
}
