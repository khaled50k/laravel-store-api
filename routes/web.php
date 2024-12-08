<?php

use App\Events\TestEvent;
use App\Events\NewUserRegistered;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;


Route::get('/', function () {
    // Load the Pusher listener page (Frontend)
    return view('pusher-test');
});

Route::get('reset-password/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->name('password.reset');

