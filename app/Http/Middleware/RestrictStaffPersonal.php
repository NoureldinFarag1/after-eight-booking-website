<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\Role;

class RestrictStaffPersonal
{
    /**
     * Block manageable staff roles (operator, approval_officer, finance_officer) from personal bookings/tickets.
     * Admins and normal users are allowed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->role instanceof Role && $user->role->isManageableStaff()) {
            abort(403, 'Staff roles cannot access personal bookings or tickets.');
        }
        return $next($request);
    }
}
