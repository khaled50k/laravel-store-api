<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class CheckExternalSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            try {
                // External API Request
                $response = Http::get(env('EXTERNAL_API_URL'), [
                    'email' => $user->email,
                ]);


                // Handle API response
                if ($response->successful()) {
                    $data = $response->json();

                    if (!$data['active']) {
                        return response()->json([
                            'error' => 'Subscription Expired',
                            'message' => 'Your subscription has expired. Please renew it.',
                        ], 403); // Forbidden
                    }
                } else {
                    return response()->json([
                        'error' => 'API Error',
                        'message' => 'Unable to verify subscription. Please try again later.',
                    ], 500); // Internal Server Error
                }
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'API Connection Error',
                    'message' => 'Failed to connect to the external subscription service.',
                    'details' => $e->getMessage(),
                ], 500);
            }
        }

        // Allow the request if the subscription is valid
        return $next($request);
    }
}
