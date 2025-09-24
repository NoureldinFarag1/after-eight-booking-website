<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OperatorController extends Controller
{

    /**
     * List operators
     */
    public function index(Request $request)
    {
        $query = User::query()->where('role', Role::OPERATOR);

        // Filters
        $status = $request->query('status'); // active|inactive|deleted|null
        // Backward-compatibility for old ?deleted=1 param
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
        ]);

        $showDeleted = $status === 'deleted';

        return view('admin.operators.index', compact('operators', 'showDeleted', 'status', 'q'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.operators.create');
    }

    /**
     * Store a new operator
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'not_regex:/^\s*$/'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Build unique email from name
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $validated['name']));
        $base = trim($base, '-');
        if ($base === '') {
            $base = 'operator';
        }
        $domain = 'aftereight.com';
        $email = $base . '@' . $domain;

        // Ensure unique
        $counter = 1;
        while (\App\Models\User::withTrashed()->where('email', $email)->exists()) {
            $email = $base . '-' . $counter . '@' . $domain;
            $counter++;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => Role::OPERATOR,
        ]);

        return redirect()
            ->route('admin.operators.index')
            ->with('success', 'Operator account created for ' . $user->name . '.');
    }

    /** Restore a soft-deleted operator */
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        if ($user->role !== Role::OPERATOR) {
            abort(404);
        }
        $user->restore();
        return redirect()->route('admin.operators.index', ['status' => 'deleted'])
            ->with('success', 'Operator ' . $user->name . ' restored.');
    }

    /** Toggle active/inactive status */
    public function toggle(User $user)
    {
        if ($user->role !== Role::OPERATOR) {
            abort(404);
        }

        $user->active = !$user->active;
        $user->save();

        return redirect()->route('admin.operators.index')
            ->with('success', $user->name . ' is now ' . ($user->active ? 'Active' : 'Inactive'));
    }

    /** Show password reset form for operator */
    public function editPassword(User $user)
    {
        if ($user->role !== Role::OPERATOR) {
            abort(404);
        }
        return view('admin.operators.password', compact('user'));
    }

    /** Update operator password */
    public function updatePassword(Request $request, User $user)
    {
        if ($user->role !== Role::OPERATOR) {
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

    /** Soft delete an operator */
    public function destroy(User $user)
    {
        if ($user->role !== Role::OPERATOR) {
            abort(404);
        }
        $user->delete();
        return redirect()->route('admin.operators.index')
            ->with('success', 'Operator ' . $user->name . ' deleted.');
    }
}
