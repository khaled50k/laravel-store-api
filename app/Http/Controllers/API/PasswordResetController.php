<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class PasswordResetController extends BaseController
{
    /**
     * Send Password Reset Link
     */
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
    
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
    
        $status = Password::sendResetLink($request->only('email'));

    
        if ($status === Password::RESET_LINK_SENT) {
            return $this->sendResponse([], 'Password reset link sent successfully.');
        }
        return $this->sendError('Unable to send reset link.', ['error' => __($status)]);
    }
    

    /**
     * Reset Password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => bcrypt($request->password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->sendResponse([], 'Password reset successfully.');
        }

        return $this->sendError('Unable to reset password.', ['error' => __($status)]);
    }
}
