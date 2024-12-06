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
    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:15',
        'password' => 'required|string|min:8',
    ]);

    if ($validator->fails()) {
        return $this->sendError('Validation Error.', $validator->errors());
    }

    $input = $request->only([
        'first_name', 'last_name', 'email', 'phone', 'password'
    ]);
    $input['password'] = bcrypt($input['password']); // Hash the password

    $user = User::create($input);

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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] = $user->createToken('MyApp')->plainTextToken;
            $success['first_name'] = $user->first_name;
            $success['last_name'] = $user->last_name;
            $success['role'] = $user->role;

            return $this->sendResponse($success, 'User logged in successfully.');
        }

        return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
    }
}
