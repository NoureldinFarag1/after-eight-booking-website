<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures that only active users can access the application.
     * If a user becomes inactive while logged in, they will be logged out.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check authenticated users
        if (Auth::check()) {
            $user = Auth::user();

            // If user is inactive, log them out and redirect to login
            if (!$user->active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Your account has been deactivated. Please contact support for assistance.']);
            }
        }

        return $next($request);
    }
}
