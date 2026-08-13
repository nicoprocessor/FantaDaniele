<?php

namespace App\Actions\Game;

use App\Models\Game;
use Illuminate\Support\Facades\DB;

class CloseExpiredGames
{
    public function handle(): int
    {
        return DB::transaction(function (): int {
            return Game::query()
                ->whereIn('status', ['open', 'started'])
                ->whereDate('created_at', '<', today())
                ->lockForUpdate()
                ->update(['status' => 'closed', 'closed_at' => now()]);
        }, attempts: 3);
    }
}
