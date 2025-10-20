@extends('layouts.app')

@section('title', 'Staff Member')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h2 class="h4 mb-1">{{ $staff->name }}</h2>
          <div class="text-muted">{{ $staff->email }}</div>
          <div class="small mt-2">
            <span class="badge bg-secondary text-uppercase">{{ str_replace('_',' ', $staff->role->value) }}</span>
            @if(method_exists($staff,'created_at') && $staff->created_at)
            <span class="ms-2 text-muted">Member since {{ $staff->created_at->format('M j, Y') }}</span>
            @endif
          </div>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">Back to Staff</a>
          @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.staff.password.edit', $staff) }}" class="btn btn-outline-primary">Reset Password</a>
          @endif
        </div>
      </div>
    </div>

    @if($staff->role->value === \App\Enums\Role::OPERATOR->value)
    <div class="row">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white border-bottom"><strong>Scan Summary</strong></div>
          <div class="card-body">
            <div class="d-flex justify-content-between mb-2"><span>Today</span><span class="fw-bold">{{ $todayScans }}</span></div>
            <div class="d-flex justify-content-between mb-2"><span>This Week</span><span class="fw-bold">{{ $thisWeekScans }}</span></div>
            <div class="text-muted small">Recent 25 scans listed on the right.</div>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white border-bottom"><strong>Recent Activity</strong></div>
          <div class="card-body">
            @if($recentScans->count())
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Event</th>
                      <th>Scanned At</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($recentScans as $scan)
                    <tr>
                      <td>{{ $scan->event->title ?? 'Unknown Event' }}</td>
                      <td>{{ $scan->scanned_at->format('M j, Y g:i A') }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <div class="text-center text-muted py-4">No recent scans.</div>
            @endif
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
