<?php

use App\Livewire\EventRegistration;
use App\Livewire\RegistrationSuccess;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/r/{code}', EventRegistration::class)->name('register');

Route::get('/r/{code}/success/{registrationGroupId}', RegistrationSuccess::class)->name('registration.success');
