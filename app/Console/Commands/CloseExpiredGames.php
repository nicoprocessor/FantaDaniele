<?php

namespace App\Console\Commands;

use App\Actions\Game\CloseExpiredGames as CloseExpiredGamesAction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('game:close-expired')]
#[Description('Close global games that remained active past midnight')]
class CloseExpiredGames extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(CloseExpiredGamesAction $closeExpiredGames): int
    {
        $closeExpiredGames->handle();

        return self::SUCCESS;
    }
}
