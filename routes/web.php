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

// Trigger a test notification
// Route to trigger event
Route::get('/test', function () {
    broadcast(new TestEvent("Pusher Test Message!"));
    return "Test event broadcasted!";
});