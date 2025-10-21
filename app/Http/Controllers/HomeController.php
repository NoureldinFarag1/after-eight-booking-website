<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Preserve existing role-based redirection
        if ($user) {
            if ($user->role === Role::ADMIN) {
                return redirect()->route('admin.dashboard');
            }
            $staffRoles = [Role::OPERATOR, Role::APPROVAL_OFFICER, Role::FINANCE_OFFICER];
            if (in_array($user->role, $staffRoles, true)) {
                return redirect()->route('admin.staff.index');
            }
        }

        $now = now();
        $dateKey = $now->toDateString();
        $tomorrowKey = $now->copy()->addDay()->toDateString();
        $monthKey = $now->format('Y-m');
    $currentMonthName = $now->format('F');

        $tomorrow = Cache::remember("home:tomorrow:{$tomorrowKey}", 600, function () use ($now) {
            return Event::query()
                ->whereDate('event_date', $now->copy()->addDay()->toDateString())
                ->published()
                ->orderBy('event_time')
                ->take(10)
                ->get();
        });

        $hotMonth = Cache::remember("home:hotMonth:{$monthKey}", 600, function () use ($now) {
            return Event::query()
                ->whereMonth('event_date', $now->month)
                ->whereYear('event_date', $now->year)
                ->published()
                ->withCount('bookings')
                ->orderByDesc('bookings_count')
                ->take(10)
                ->get();
        });

        $today = Cache::remember("home:today:{$dateKey}", 600, function () use ($now) {
            return Event::query()
                ->whereDate('event_date', $now->toDateString())
                ->published()
                ->orderBy('event_time')
                ->take(10)
                ->get();
        });

        $featured = Cache::remember("home:featured:{$monthKey}", 600, function () use ($hotMonth, $now) {
            if ($hotMonth->isNotEmpty()) {
                return $hotMonth->first();
            }
            return Event::query()
                ->published()
                ->whereDate('event_date', '>=', $now->toDateString())
                ->orderBy('event_date')
                ->orderBy('event_time')
                ->first();
        });

        return view('welcome', compact('tomorrow', 'hotMonth', 'today', 'featured', 'currentMonthName'));
    }
}
