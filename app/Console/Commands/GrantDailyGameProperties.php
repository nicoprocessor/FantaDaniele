<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\DailyGamePropertyGranted;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('game:grant-daily-properties')]
#[Description('Grant one daily game property to users with no balance')]
class GrantDailyGameProperties extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $grantedOn = today()->toDateString();

        User::query()->where('balance', 0)->eachById(function (User $user) use ($grantedOn): void {
            $created = $user->gamePropertyGrants()->firstOrCreate(['granted_on' => $grantedOn]);

            if ($created->wasRecentlyCreated) {
                $user->increment('balance');
                $user->notify(new DailyGamePropertyGranted);
            }
        });

        return self::SUCCESS;
    }
}
