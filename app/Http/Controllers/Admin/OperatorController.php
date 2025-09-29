<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OperatorController extends Controller
{
    // Route protection handled in routes/web.php via middleware('role:admin') or can:staff.manage.
    // Keeping constructor empty to avoid undefined middleware() base method (base Controller is minimal).
    /**
     * List operators & approval officers
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
                $query->where('active', true);
            } elseif ($status === 'inactive') {
                $query->where('active', false);
            }
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%");
            });
        }

        $operators = $query->latest()->paginate(15)->appends([
            'status' => $status,
            'q' => $q !== '' ? $q : null,
            'role' => $roleFilter,
        ]);

        $showDeleted = $status === 'deleted';
        $manageableRoles = $manageable;
        return view('admin.operators.index', compact('operators', 'showDeleted', 'status', 'q', 'manageableRoles', 'roleFilter'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.operators.create');
    }

    /**
     * Store a new operator or approval officer
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

        // Always return to staff listing (admin area) – avoids 403 for admin after creating approval officer
        $roleLabel = str_replace('_',' ', $user->role->value);
        return redirect()
            ->route('admin.operators.index')
            ->with('success', ucfirst($roleLabel).' account created for '.$user->name.'.');
    }

    /** Restore a soft-deleted operator/approval officer */
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        if (!$user->role->isManageableStaff()) {
            abort(404);
        }
        $user->restore();
        return redirect()->route('admin.operators.index', ['status' => 'deleted'])
            ->with('success', ucfirst(str_replace('_', ' ', $user->role->value)) . ' ' . $user->name . ' restored.');
    }

    /** Toggle active/inactive status */
    public function toggle(User $user)
    {
        if (!$user->role->isManageableStaff()) {
            abort(404);
        }

        $user->active = !$user->active;
        $user->save();

        return redirect()->route('admin.operators.index')
            ->with('success', $user->name . ' is now ' . ($user->active ? 'Active' : 'Inactive'));
    }

    /** Show password reset form */
    public function editPassword(User $user)
    {
        if (!$user->role->isManageableStaff()) {
            abort(404);
        }
        return view('admin.operators.password', compact('user'));
    }

    /** Update password */
    public function updatePassword(Request $request, User $user)
    {
        if (!in_array($user->role, [Role::OPERATOR, Role::APPROVAL_OFFICER])) {
            abort(404);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('admin.operators.index')
            ->with('success', 'Password reset for ' . $user->name . '.');
    }

    /** Soft delete */
    public function destroy(User $user)
    {
        if (!$user->role->isManageableStaff()) {
            abort(404);
        }
        $user->delete();
        return redirect()->route('admin.operators.index')
            ->with('success', ucfirst(str_replace('_', ' ', $user->role->value)) . ' ' . $user->name . ' deleted.');
    }
}
