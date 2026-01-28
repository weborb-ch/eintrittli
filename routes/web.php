<?php

use App\Models\Event;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/r/{slug}/{short_code}', function (string $slug, string $short_code) {
    $event = Event::where('slug', $slug)
        ->where('short_code', $short_code)
        ->firstOrFail();

    return view('register', ['event' => $event]);
})->name('register');
