<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OperatorRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only apply to authenticated operators
        if ($user && $user->isOperator()) {
            $currentRoute = $request->route()->getName();
            $allowedRoutes = [
                'tickets.scan',
                'tickets.validate',
                'logout'
            ];

            // If operator is trying to access any page other than allowed ones, redirect to scan
            if (!in_array($currentRoute, $allowedRoutes)) {
                return redirect()->route('tickets.scan');
            }
        }

        return $next($request);
    }
}
