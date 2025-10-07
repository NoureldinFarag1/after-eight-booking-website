<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', Role::ADMIN);

        // Filters
        $status = $request->query('status');
        if ($status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%");
            });
        }

        $admins = $query->latest()->paginate(15)->appends($request->query());
        return view('admin.admins.index', compact('admins', 'status', 'q'));
    }

    public function create()
    {
        return view('admin.admins.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'not_regex:/^\s*$/'],
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Build unique email from name
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $validated['name']));
        $base = trim($base, '-');
        if ($base === '') {
            $base = 'admin';
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
            'role' => Role::ADMIN,
        ]);

        return redirect()->route('admin.admins.index')->with('success', 'Admin account created for ' . $user->name . '.');
    }

    public function edit(User $user)
    {
        return view('admin.admins.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ];

        // The main admin's email cannot be changed.
        if ($user->id !== 1) {
            $rules['email'] = 'required|email|unique:users,email,' . $user->id;
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];

        if ($user->id !== 1) {
            $user->email = $validated['email'];
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.admins.index')->with('success', 'Admin updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === 1) {
            return back()->with('error', 'The main administrator cannot be deleted.');
        }
        if (Auth::user()->id === $user->id) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();
        return redirect()->route('admin.admins.index')->with('success', 'Admin has been soft-deleted.');
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->where('role', Role::ADMIN)->findOrFail($id);
        $user->restore();
        return redirect()->route('admin.admins.index', ['status' => 'deleted'])->with('success', 'Admin restored.');
    }

    public function toggle(User $user)
    {
        if ($user->id === 1) {
            return back()->with('error', 'The main administrator status cannot be changed.');
        }
        if (Auth::user()->id === $user->id) {
            return back()->with('error', 'You cannot change your own status.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return redirect()->route('admin.admins.index')->with('success', $user->name . ' is now ' . ($user->is_active ? 'Active' : 'Inactive'));
    }
}
