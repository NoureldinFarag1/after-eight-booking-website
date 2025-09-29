<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRequestController;
use App\Http\Controllers\Admin\OperatorController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Admin\TicketTypeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite; // still used indirectly if needed

Route::get('/', function () {
    $user = Auth::user();
    if ($user && $user->role === \App\Enums\Role::ADMIN) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('events.index');
})->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    // Password reset
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Public event routes
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Authentication required routes
Route::middleware(['auth', 'operator.redirect'])->group(function () {

    // Event management (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [EventController::class, 'dashboard'])->name('admin.dashboard');
        Route::resource('admin/events', EventController::class)->except(['index', 'show'])->names([
            'create' => 'admin.events.create',
            'store' => 'admin.events.store',
            'edit' => 'admin.events.edit',
            'update' => 'admin.events.update',
            'destroy' => 'admin.events.destroy',
        ]);
        Route::patch('/admin/events/{event}/toggle-publish', [EventController::class, 'togglePublish'])->name('admin.events.toggle-publish');

        // Ticket Types per Event
        Route::prefix('admin/events/{event}')->group(function () {
            Route::get('ticket-types', [TicketTypeController::class, 'index'])->name('admin.events.ticket-types.index');
            Route::post('ticket-types', [TicketTypeController::class, 'store'])->name('admin.events.ticket-types.store');
            Route::put('ticket-types/{ticketType}', [TicketTypeController::class, 'update'])->name('admin.events.ticket-types.update');
            Route::delete('ticket-types/{ticketType}', [TicketTypeController::class, 'destroy'])->name('admin.events.ticket-types.destroy');
        });

        // Operator management (Admin only)
    Route::get('/admin/operators', [OperatorController::class, 'index'])->name('admin.operators.index');
    Route::get('/admin/operators/create', [OperatorController::class, 'create'])->name('admin.operators.create');
    Route::post('/admin/operators', [OperatorController::class, 'store'])->name('admin.operators.store');
    Route::patch('/admin/operators/{user}/toggle', [OperatorController::class, 'toggle'])->name('admin.operators.toggle');
    Route::get('/admin/operators/{user}/password', [OperatorController::class, 'editPassword'])->name('admin.operators.password.edit');
    Route::post('/admin/operators/{user}/password', [OperatorController::class, 'updatePassword'])->name('admin.operators.password.update');
    Route::delete('/admin/operators/{user}', [OperatorController::class, 'destroy'])->name('admin.operators.destroy');
    Route::patch('/admin/operators/{id}/restore', [OperatorController::class, 'restore'])->name('admin.operators.restore');
    });

    // Booking routes
    Route::get('/events/{event}/book', [BookingController::class, 'create'])->name('bookings.create');
    Route::resource('bookings', BookingController::class)->except(['create']);
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    // Ticket routes
    Route::resource('tickets', TicketController::class)->only(['index', 'show']);
    Route::get('/tickets/{ticket}/qr-code', [TicketController::class, 'qrCode'])->name('tickets.qr-code');
    Route::get('/tickets/{ticket}/download', [TicketController::class, 'download'])->name('tickets.download');

    // Operator-only ticket scanning
    Route::middleware('role:operator')->group(function () {
        Route::get('/scan', [TicketController::class, 'scan'])->name('tickets.scan');
        Route::post('/tickets/validate/{qr_code}', [TicketController::class, 'validateTicket'])->name('tickets.validate');
    });

    // Admin ticket management
    Route::middleware('role:admin')->group(function () {
        Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.update-status');
    });
});

// API Routes for AJAX/QR scanning
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/tickets/validate/{qr_code}', [TicketController::class, 'validateTicket'])
         ->middleware('role:operator')
         ->name('api.tickets.validate');
});

/*
|--------------------------------------------------------------------------
| Event Requests Routes
|--------------------------------------------------------------------------
| - Users can create requests for events of type 'request'
| - Admin can approve/decline requests
*/

Route::middleware('auth')->group(function () {
    // User-side
    Route::get('/my-requests', [EventRequestController::class, 'myRequests'])->name('event_requests.index');

    Route::get('/events/{event}/request', [EventRequestController::class, 'create'])
        ->name('event-requests.create');
    Route::post('/events/{event}/request', [EventRequestController::class, 'store'])
        ->name('event-requests.store');

    Route::get('/event-requests/{eventRequest}/edit', [EventRequestController::class, 'edit'])->name('event_requests.edit');
    Route::put('/event-requests/{eventRequest}', [EventRequestController::class, 'update'])->name('event_requests.update');

    Route::get('/event-requests/{eventRequest}', [EventRequestController::class, 'show'])->name('event_requests.show');

// Admin-side

    // Admin-side for event requests
    Route::prefix('admin')->middleware(['auth'])->group(function() {
        Route::get('/event-requests', [EventRequestController::class, 'adminIndex'])->name('admin.event_requests.index');
        Route::post('/event-requests/{eventRequest}/approve', [EventRequestController::class, 'approve'])->name('admin.event_requests.approve');
        Route::post('/event-requests/{eventRequest}/decline', [EventRequestController::class, 'decline'])->name('admin.event_requests.decline');
    });

});

Route::middleware(['auth', 'role:admin'])->group(function () {

    // Admins management (manage admin users)
    Route::get('/admin/admins', [\App\Http\Controllers\Admin\AdminController::class, 'index'])->name('admin.admins.index');
    Route::get('/admin/admins/create', [\App\Http\Controllers\Admin\AdminController::class, 'create'])->name('admin.admins.create');
    Route::post('/admin/admins', [\App\Http\Controllers\Admin\AdminController::class, 'store'])->name('admin.admins.store');
    Route::get('/admin/admins/{user}/edit', [\App\Http\Controllers\Admin\AdminController::class, 'edit'])->name('admin.admins.edit');
    Route::put('/admin/admins/{user}', [\App\Http\Controllers\Admin\AdminController::class, 'update'])->name('admin.admins.update');
    Route::delete('/admin/admins/{user}', [\App\Http\Controllers\Admin\AdminController::class, 'destroy'])->name('admin.admins.destroy');
Route::resource('invitations', \App\Http\Controllers\InvitationController::class)
        ->only(['index', 'create', 'store']);
});

// Google OAuth
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');


// Approval Officer routes
Route::middleware(['auth','role:approval_officer'])->group(function () {
    Route::get('/approval', [\App\Http\Controllers\ApprovalRequestController::class, 'index'])->name('approval.index');
    Route::get('/approval/{eventRequest}', [\App\Http\Controllers\ApprovalRequestController::class, 'show'])->name('approval.show');
    Route::post('/approval/{eventRequest}/approve', [\App\Http\Controllers\ApprovalRequestController::class, 'approve'])->name('approval.approve');
    Route::post('/approval/{eventRequest}/reject', [\App\Http\Controllers\ApprovalRequestController::class, 'reject'])->name('approval.reject');
});
