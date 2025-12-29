<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use App\Models\Ticket;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of regular users (non-staff)
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->where(function ($q) {
                $q->where('role', Role::USER)
                  ->orWhereNull('role');
            })
            ->withCount(['bookings', 'tickets'])
            ->with(['bookings' => function ($q) {
                $q->latest()->limit(3);
            }]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('active', true)->whereNull('deleted_at');
            } elseif ($request->status === 'inactive') {
                $query->where('active', false)->whereNull('deleted_at');
            } elseif ($request->status === 'deleted') {
                $query->onlyTrashed();
            }
        } else {
            // Default: show only non-deleted users
            $query->whereNull('deleted_at');
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSorts = ['name', 'email', 'created_at', 'bookings_count', 'tickets_count'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Booking activity filter
        if ($request->filled('booking_activity')) {
            if ($request->booking_activity === 'has_bookings') {
                $query->has('bookings');
            } elseif ($request->booking_activity === 'no_bookings') {
                $query->doesntHave('bookings');
            }
        }

        $users = $query->paginate(15)->withQueryString();

        // Get summary statistics
        $stats = $this->getUserStats();

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Show detailed view of a specific user
     */
    public function show(User $user)
    {
        // Ensure we're only showing regular users
        if ($user->isStaff()) {
            abort(404);
        }

        $user->load([
            'bookings.event:id,title,event_date',
            'tickets.event:id,title,event_date',
            'tickets.booking:id,booking_reference'
        ]);

        // Get user's booking and ticket statistics
        $bookingStats = $user->bookings()
            ->selectRaw('
                COUNT(*) as total_bookings,
                SUM(total_amount) as total_spent,
                SUM(quantity) as total_tickets_booked
            ')
            ->first();

        $ticketStats = $user->tickets()
            ->selectRaw('
                COUNT(*) as total_tickets,
                SUM(CASE WHEN scanned_at IS NOT NULL THEN 1 ELSE 0 END) as used_tickets,
                SUM(CASE WHEN scanned_at IS NULL THEN 1 ELSE 0 END) as unused_tickets
            ')
            ->first();

        return view('admin.users.show', compact('user', 'bookingStats', 'ticketStats'));
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        if ($user->isStaff()) {
            return response()->json(['error' => 'Cannot modify staff users'], 403);
        }

        $wasActive = $user->active;
        $user->update(['active' => !$user->active]);

        $message = $user->active
            ? 'User activated successfully. They can now log in to their account.'
            : 'User deactivated successfully. They will be logged out and cannot access their account until reactivated.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'status' => $user->active ? 'active' : 'inactive'
        ]);
    }

    /**
     * Soft delete a user
     */
    public function destroy(User $user)
    {
        if ($user->isStaff()) {
            return response()->json(['error' => 'Cannot delete staff users'], 403);
        }

        // Check if user has active bookings
        $activeBookings = $user->bookings()->where('status', 'confirmed')->count();
        if ($activeBookings > 0) {
            return response()->json([
                'error' => 'Cannot delete user with active bookings'
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Restore a soft-deleted user
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->isStaff()) {
            return response()->json(['error' => 'Cannot restore staff users'], 403);
        }

        $user->restore();

        return response()->json([
            'success' => true,
            'message' => 'User restored successfully'
        ]);
    }

    /**
     * Get user statistics for dashboard
     */
    private function getUserStats()
    {
        return [
            'total_users' => User::where(function ($q) {
                $q->where('role', Role::USER)->orWhereNull('role');
            })->count(),
            'active_users' => User::where(function ($q) {
                $q->where('role', Role::USER)->orWhereNull('role');
            })->where('active', true)->count(),
            'users_with_bookings' => User::where(function ($q) {
                $q->where('role', Role::USER)->orWhereNull('role');
            })->has('bookings')->count(),
            'new_users_this_month' => User::where(function ($q) {
                $q->where('role', Role::USER)->orWhereNull('role');
            })->whereMonth('created_at', now()->month)
              ->whereYear('created_at', now()->year)
              ->count(),
        ];
    }
}
