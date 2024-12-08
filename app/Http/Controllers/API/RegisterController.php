<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Events\NewUserRegistered;

class RegisterController extends BaseController
{
    /**
     * Register API
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
{
    $validatedData = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:15',
        'password' => 'required|string|min:8|confirmed',
        'password_confirmation' => 'required_with:password|same:password|min:8',
    ]);

    $user = User::create([
        'first_name' => $validatedData['first_name'],
        'last_name' => $validatedData['last_name'],
        'email' => $validatedData['email'],
        'phone' => $validatedData['phone'],
        'password' => bcrypt($validatedData['password']),
    ]);

    // Broadcast event for real-time notification
    broadcast(new NewUserRegistered((object) $user));
    $success['token'] = $user->createToken('MyApp')->plainTextToken;
    $success['first_name'] = $user->first_name;
    $success['last_name'] = $user->last_name;

    return $this->sendResponse($success, 'User registered successfully.');
}

    /**
     * Login API
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8',
        ]);

        if (!Auth::attempt(['email' => $validatedData['email'], 'password' => $validatedData['password']])) {
            return $this->sendError('Unauthorised.', ['error' => 'Invalid credentials.']);
        }

        $user = Auth::user();
        $token = $user->createToken('MyApp')->plainTextToken;

        return $this->sendResponse([
            'token' => $token,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
        ], 'User logged in successfully.');
    }
}
