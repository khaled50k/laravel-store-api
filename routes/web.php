<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;


Route::get('/', function () {
    return view('welcome');
});

Route::get('reset-password/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->name('password.reset');
