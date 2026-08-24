<?php

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;

function demoResetEvent(): Event
{
    $event = collect(app(Schedule::class)->events())
        ->first(fn (Event $event): bool => str_contains($event->command ?? '', 'demo:reset'));

    expect($event)->not->toBeNull();

    return $event;
}

it('schedules the demo reset every sunday at 03:00 UTC', function () {
    $event = demoResetEvent();

    expect($event->expression)->toBe('0 3 * * 0')
        ->and($event->timezone)->toBe('Europe/Zurich')
        ->and($event->command)->toContain('--force');
});

it('skips the demo reset on non-demo instances', function () {
    config()->set('app.is_demo', false);

    expect(demoResetEvent()->filtersPass(app()))->toBeFalse();
});

it('runs the demo reset on demo instances', function () {
    config()->set('app.is_demo', true);

    expect(demoResetEvent()->filtersPass(app()))->toBeTrue();
});
