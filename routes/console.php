<?php

use App\Console\Commands\ResetDemoCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Weekly reset of the public demo instance. Only demo instances run it; on
 * Laravel Cloud the environment wakes from Scale to Zero to execute it.
 */
Schedule::command(ResetDemoCommand::class, ['--force'])
    ->weeklyOn(0, '3:00')
    ->timezone('Europe/Zurich')
    ->onOneServer()
    ->when(fn (): bool => (bool) config('app.is_demo'));
