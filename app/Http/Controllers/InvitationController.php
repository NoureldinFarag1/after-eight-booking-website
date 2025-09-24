<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    /**
     * Show all invitations.
     */
    public function index()
    {
        $invitations = Invitation::latest()->paginate(10);
        return view('invitations.index', compact('invitations'));
    }

    /**
     * Show the form to create a new invitation.
     */
    public function create()
    {
        return view('invitations.create');
    }

    /**
     * Store a newly created invitation in the database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        Invitation::create($validated);

        return redirect()
            ->route('invitations.index')
            ->with('success', 'Invitation created successfully!');
    }
}