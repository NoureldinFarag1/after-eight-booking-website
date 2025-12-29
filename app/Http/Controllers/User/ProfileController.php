<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\Ticket;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile and settings
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        // Ensure only regular users can access this
        if ($user->isStaff()) {
            abort(404);
        }

        // Get user's recent bookings (last 10)
        $recentBookings = $user->bookings()
            ->with(['event:id,title,event_date'])
            ->latest()
            ->limit(10)
            ->get();

        // Get user's recent tickets (last 10)
        $recentTickets = $user->tickets()
            ->with(['event:id,title,event_date', 'booking:id,booking_reference'])
            ->latest()
            ->limit(10)
            ->get();

        // Get user's invitations (last 10)
        $recentInvitations = Invitation::where('email', $user->email)
            ->with(['event:id,title,event_date'])
            ->latest()
            ->limit(10)
            ->get();

        // Get membership statistics
        $membershipStats = [
            'member_since' => $user->created_at,
            'total_bookings' => $user->bookings()->count(),
            'total_tickets' => $user->tickets()->count(),
            'total_spent' => $user->bookings()->sum('total_amount'),
            'used_tickets' => $user->tickets()->whereNotNull('scanned_at')->count(),
            'upcoming_events' => $user->bookings()
                ->whereHas('event', function($q) {
                    $q->where('event_date', '>', now());
                })
                ->count(),
        ];

        return view('user.profile.index', compact(
            'user',
            'recentBookings',
            'recentTickets',
            'recentInvitations',
            'membershipStats'
        ));
    }

    /**
     * Show the form for editing profile
     */
    public function edit(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isStaff()) {
            abort(404);
        }

        // If user has completed profile, they cannot edit core information
        if ($user->hasCompletedProfile()) {
            return redirect()->route('user.profile.index')
                ->with('error', 'Your profile information cannot be modified once completed. Contact support if you need to make changes.');
        }

        return view('user.profile.edit', compact('user'));
    }

    /**
     * Show profile completion form for incomplete profiles
     */
    public function showComplete(): View|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isStaff() || $user->hasCompletedProfile()) {
            return redirect()->route('user.profile.index');
        }

        return view('user.profile.complete', compact('user'));
    }

    /**
     * Complete user profile (first time only)
     */
    public function complete(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isStaff() || $user->hasCompletedProfile()) {
            return redirect()->route('user.profile.index');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'birthday' => ['required', 'date', 'before:' . now()->subYears(13)->format('Y-m-d')],
            'gender' => ['required', 'in:male,female'],
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'birthday' => $request->birthday,
            'gender' => $request->gender,
            'profile_completed' => true,
        ]);

        return redirect()->route('user.profile.index')
            ->with('success', 'Profile completed successfully! Welcome to After Eight Events.');
    }

    /**
     * Update the user's profile information (disabled after completion)
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isStaff()) {
            abort(404);
        }

        // Prevent updating if profile is already completed
        if ($user->hasCompletedProfile()) {
            return redirect()->route('user.profile.index')
                ->with('error', 'Your profile information cannot be modified once completed. Contact support if you need to make changes.');
        }

        // This method should not be used - profile completion should use complete() method
        return redirect()->route('profile.complete')
            ->with('info', 'Please use the profile completion form to set your information.');
    }

    /**
     * Show the form for changing password
     */
    public function editPassword(): View
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isStaff()) {
            abort(404);
        }

        return view('user.profile.password', compact('user'));
    }

    /**
     * Update the user's password
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isStaff()) {
            abort(404);
        }

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('user.profile.index')
            ->with('success', 'Password updated successfully!');
    }
}
