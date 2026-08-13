<?php

use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    TeamInvitation::query()
        ->whereNotNull('expires_at')
        ->where('expires_at', '<', now())
        ->delete();
})->daily()->description('Delete expired team invitations');

Schedule::command('game:grant-daily-properties')->daily()->withoutOverlapping()->description('Grant daily game properties to users with no balance');
Schedule::command('game:close-expired')->dailyAt('00:00')->withoutOverlapping()->description('Close unfinished global games at midnight');
