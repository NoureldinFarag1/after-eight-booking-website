<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FinanceOfficerController extends Controller
{
    public function index()
    {
        $officers = User::where('role', Role::FINANCE_OFFICER)->paginate(15);
        return view('admin.finance_officers.index', compact('officers'));
    }

    public function create()
    {
        return view('admin.finance_officers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => Role::FINANCE_OFFICER,
        ]);

        return redirect()->route('admin.finance_officers.index')->with('success', 'Finance officer created.');
    }

    public function edit(User $user)
    {
        return view('admin.finance_officers.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|confirmed|min:6',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return redirect()->route('admin.finance_officers.index')->with('success', 'Finance officer updated.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        $user->delete();
        return redirect()->route('admin.finance_officers.index')->with('success', 'Finance officer deleted.');
    }
}
