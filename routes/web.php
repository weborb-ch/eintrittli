<?php

use App\Models\Event;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register/{event:slug}', function (Event $event) {
    return view('register', ['event' => $event]);
})->name('register');
