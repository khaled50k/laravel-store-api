<?php
// Middleware: app/Http/Middleware/AdminMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized. Admin access only.'], 403);
    }
}
