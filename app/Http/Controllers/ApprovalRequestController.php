<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventRequest;
use Illuminate\Support\Facades\Gate;

class ApprovalRequestController extends Controller
{
    public function index()
    {
        Gate::authorize('approval.manage');
        $requests = EventRequest::where('status','pending')->latest()->paginate(20);
        return view('approval.index', compact('requests'));
    }

    public function approve(EventRequest $eventRequest)
    {
        Gate::authorize('approval.manage');
        // Delegate to main approval flow which sets status to awaiting_payment and notifies user
        return app(\App\Http\Controllers\EventRequestController::class)->approve($eventRequest);
    }

    public function reject(EventRequest $eventRequest)
    {
        Gate::authorize('approval.manage');
        // Column enum uses 'declined' not 'rejected'
        $eventRequest->status = 'declined';
        $eventRequest->save();
        return back()->with('success','Request declined.');
    }

    public function show(EventRequest $eventRequest)
    {
        Gate::authorize('approval.manage');
        // ensure relations loaded
        $eventRequest->loadMissing(['user','event']);
        return view('approval.show', compact('eventRequest'));
    }
}
