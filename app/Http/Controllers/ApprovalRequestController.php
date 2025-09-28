<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventRequest;

class ApprovalRequestController extends Controller
{
    public function index()
    {
        $requests = EventRequest::where('status','pending')->latest()->paginate(20);
        return view('approval.index', compact('requests'));
    }

    public function approve(EventRequest $eventRequest)
    {
        $eventRequest->status = 'approved';
        $eventRequest->save();
        return back()->with('success','Request approved.');
    }

    public function reject(EventRequest $eventRequest)
    {
        $eventRequest->status = 'rejected';
        $eventRequest->save();
        return back()->with('success','Request rejected.');
    }
}
