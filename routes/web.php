<?php

use App\Models\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/r/{code}', function (string $code) {
    $key = 'register-page:'.request()->ip();

    if (RateLimiter::tooManyAttempts($key, 30)) {
        abort(Response::HTTP_TOO_MANY_REQUESTS, 'Too many requests. Please try again later.');
    }
    RateLimiter::hit($key, 60);

    $event = Event::where('code', $code)->firstOrFail();

    return view('register', ['event' => $event]);
})->name('register');
