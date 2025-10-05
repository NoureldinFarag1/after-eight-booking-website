<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Skip check for guests, staff, and already on profile completion route
        if (!$user || $user->isStaff() || $request->routeIs('profile.complete*')) {
            return $next($request);
        }

        // If user needs to complete their profile, redirect them
        if ($user->needsProfileCompletion()) {
            return redirect()->route('profile.complete')
                ->with('info', 'Please complete your profile to continue using our services.');
        }

        return $next($request);
    }
}
