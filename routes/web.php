<?php

use App\Livewire\EventRegistration;
use Illuminate\Support\Facades\Route;

Route::get('/r/{code}', EventRegistration::class)->name('register');
