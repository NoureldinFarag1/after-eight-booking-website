<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * List all manageable staff members.
     */
    public function index(Request $request)
    {
        $manageable = Role::manageableStaff();
        $query = User::query()->whereIn('role', array_map(fn($r) => $r->value, $manageable));

        // Filters
        $status = $request->query('status'); // active|inactive|deleted|null
        $roleFilter = $request->query('role'); // specific role value
        if ($roleFilter && in_array($roleFilter, array_map(fn($r)=>$r->value,$manageable), true)) {
            $query->where('role', $roleFilter);
        }
        if ($status === null && $request->has('deleted') && $request->boolean('deleted')) {
            $status = 'deleted';
        }
        $q = trim((string) $request->query('q', ''));

        if ($status === 'deleted') {
            $query->onlyTrashed();
        } else {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%");
            });
        }

        $users = $query->latest()->paginate(15)->appends([
            'status' => $status,
            'q' => $q !== '' ? $q : null,
            'role' => $roleFilter,
        ]);

        $showDeleted = $status === 'deleted';
        $manageableRoles = $manageable;
        return view('admin.staff.index', compact('users', 'showDeleted', 'status', 'q', 'manageableRoles', 'roleFilter'));
    }

    /**
     * Show create form for a staff member.
     */
    public function create()
    {
        return view('admin.staff.create');
    }

    /** Show a specific staff member (operator/admin/etc.) */
    public function show(User $user)
    {
        if (!$user->role->isManageableStaff()) {
            abort(404);
        }

        // Gather simple activity details for operators
        $recentScans = collect();
        $todayScans = 0;
        $thisWeekScans = 0;
        if ($user->role->value === Role::OPERATOR->value) {
            $recentScans = $user->scannedTickets()
                ->select('id', 'event_id', 'scanned_at')
                ->with('event:id,title')
                ->latest('scanned_at')
                ->limit(25)
                ->get();
            $todayScans = $user->scannedTickets()->whereDate('scanned_at', today())->count();
            $thisWeekScans = $user->scannedTickets()->whereBetween('scanned_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        }

        return view('admin.staff.show', [
            'staff' => $user,
            'recentScans' => $recentScans,
            'todayScans' => $todayScans,
            'thisWeekScans' => $thisWeekScans,
        ]);
    }

    /**
     * Store a new staff member.
     */
    public function store(Request $request)
    {
        $manageableValues = array_map(fn($r)=>$r->value, Role::manageableStaff());
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'not_regex:/^\s*$/'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => 'required|in:'.implode(',', $manageableValues),
        ]);

        // Build unique email from name
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $validated['name']));
        $base = trim($base, '-');
        if ($base === '') {
            $base = $validated['role'];
        }
        $domain = 'aftereight.com';
        $email = $base . '@' . $domain;

        $counter = 1;
        while (User::withTrashed()->where('email', $email)->exists()) {
            $email = $base . '-' . $counter . '@' . $domain;
            $counter++;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        $roleLabel = str_replace('_',' ', $user->role->value);
        return redirect()
            ->route('admin.staff.index')
            ->with('success', ucfirst($roleLabel).' account created for '.$user->name.'.');
    }

    /** Restore a soft-deleted staff member. */
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        if (!$user->role->isManageableStaff()) {
            abort(404);
        }
        $user->restore();
        return redirect()->route('admin.staff.index', ['status' => 'deleted'])
            ->with('success', ucfirst(str_replace('_', ' ', $user->role->value)) . ' ' . $user->name . ' restored.');
    }

    /** Toggle active/inactive status. */
    public function toggle(User $user)
    {
        if (!$user->role->isManageableStaff()) {
            abort(404);
        }
        $user->is_active = !$user->is_active;
        $user->save();

        return redirect()->route('admin.staff.index')
        ->with('success', $user->name . ' is now ' . ($user->is_active ? 'Active' : 'Inactive'));
    }

    /** Show password reset form. */
    public function editPassword(User $user)
    {
        if (!$user->role->isManageableStaff()) {
            abort(404);
        }
        return view('admin.staff.password', compact('user'));
    }

    /** Update password. */
    public function updatePassword(Request $request, User $user)
    {
        if (!$user->role->isManageableStaff()) {
            abort(404);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('admin.staff.index')
            ->with('success', 'Password reset for ' . $user->name . '.');
    }

    /** Soft delete a staff member. */
    public function destroy(User $user)
    {
        if (!$user->role->isManageableStaff()) {
            abort(404);
        }
        $user->delete();
        return redirect()->route('admin.staff.index')
            ->with('success', ucfirst(str_replace('_', ' ', $user->role->value)) . ' ' . $user->name . ' deleted.');
    }
}
