<?php

use App\Enums\Role;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ArtistController as AdminArtistController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Admin\TicketTypeController;
use App\Http\Controllers\EventRequestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite; // still used indirectly if needed

Route::get('/', [HomeController::class, 'index'])->name('home');

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

// Profile completion routes (accessible to authenticated users with incomplete profiles)
Route::middleware('auth')->group(function () {
    Route::get('/profile/complete', [\App\Http\Controllers\User\ProfileController::class, 'showComplete'])->name('profile.complete');
    Route::post('/profile/complete', [\App\Http\Controllers\User\ProfileController::class, 'complete'])->name('profile.complete.store');
});

// Public event routes
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Public ticket verification (for QR codes)
Route::get('/tickets/verify/{ticket}/{code}', [TicketController::class, 'verify'])->name('tickets.verify');

// QR Code test page (temporary)
Route::get('/qr-test', function () {
    return view('qr-test');
})->name('qr.test');

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
        Route::patch('/admin/events/{event}/toggle-featured', [EventController::class, 'toggleFeatured'])->name('admin.events.toggle-featured');
    // Exports (Admin only)
    Route::get('/admin/events/{event}/export', [EventController::class, 'exportSingle'])->name('admin.events.export');
    Route::get('/admin/events/export', [EventController::class, 'exportBulk'])->name('admin.events.export.bulk');

        // Artists management (Admin only)
        Route::resource('admin/artists', AdminArtistController::class)->names([
            'index' => 'admin.artists.index',
            'create' => 'admin.artists.create',
            'store' => 'admin.artists.store',
            'edit' => 'admin.artists.edit',
            'update' => 'admin.artists.update',
            'destroy' => 'admin.artists.destroy',
            'show' => 'admin.artists.show',
        ])->except(['show']);

        // Ticket Types per Event
        Route::prefix('admin/events/{event}')->group(function () {
            Route::get('ticket-types', [TicketTypeController::class, 'index'])->name('admin.events.ticket-types.index');
            Route::post('ticket-types', [TicketTypeController::class, 'store'])->name('admin.events.ticket-types.store');
            Route::put('ticket-types/{ticketType}', [TicketTypeController::class, 'update'])->name('admin.events.ticket-types.update');
            Route::delete('ticket-types/{ticketType}', [TicketTypeController::class, 'destroy'])->name('admin.events.ticket-types.destroy');
        });

        // Staff management (Admin only) - includes operators, approval officers, and finance officers
    Route::get('/admin/staff', [StaffController::class, 'index'])->name('admin.staff.index');
    Route::get('/admin/staff/create', [StaffController::class, 'create'])->name('admin.staff.create');
    Route::get('/admin/staff/{user}', [StaffController::class, 'show'])->name('admin.staff.show');
    Route::post('/admin/staff', [StaffController::class, 'store'])->name('admin.staff.store');
    Route::patch('/admin/staff/{user}/toggle', [StaffController::class, 'toggle'])->name('admin.staff.toggle');
    Route::get('/admin/staff/{user}/password', [StaffController::class, 'editPassword'])->name('admin.staff.password.edit');
    Route::post('/admin/staff/{user}/password', [StaffController::class, 'updatePassword'])->name('admin.staff.password.update');
    Route::delete('/admin/staff/{user}', [StaffController::class, 'destroy'])->name('admin.staff.destroy');
    Route::patch('/admin/staff/{id}/restore', [StaffController::class, 'restore'])->name('admin.staff.restore');

        // Users management (Admin only) - regular users (non-staff)
        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
        Route::patch('/admin/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
        Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::patch('/admin/users/{id}/restore', [UserController::class, 'restore'])->name('admin.users.restore');

    // Redirect old operators URL to new staff URL for backward compatibility
    Route::get('/admin/operators', function() {
        return redirect()->route('admin.staff.index');
    });
    });

    // Booking & user ticket routes (blocked for staff roles via middleware alias)
    Route::middleware('restrict_staff_personal')->group(function () {
        Route::get('/events/{event}/book', [BookingController::class, 'create'])->name('bookings.create');
    // Show checkout via GET for PRG/refresh and direct navigation safety
    Route::get('/bookings/checkout', [BookingController::class, 'checkout'])->name('bookings.checkout.view');
    // Process checkout from the create form (POST)
    Route::post('/bookings/checkout', [BookingController::class, 'checkout'])->name('bookings.checkout');
        Route::resource('bookings', BookingController::class)->except(['create']);
        Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

        // Ticket routes
        Route::resource('tickets', TicketController::class)->only(['index', 'show']);
        Route::get('/tickets/{ticket}/qr-code', [TicketController::class, 'qrCode'])->name('tickets.qr-code');
        Route::get('/tickets/{ticket}/download', [TicketController::class, 'download'])->name('tickets.download');

        // User Profile & Settings (Regular users only)
        Route::prefix('profile')->name('user.profile.')->group(function () {
            Route::get('/', [\App\Http\Controllers\User\ProfileController::class, 'index'])->name('index');
            Route::get('/edit', [\App\Http\Controllers\User\ProfileController::class, 'edit'])->name('edit');
            Route::put('/update', [\App\Http\Controllers\User\ProfileController::class, 'update'])->name('update');
            Route::get('/password', [\App\Http\Controllers\User\ProfileController::class, 'editPassword'])->name('password.edit');
            Route::put('/password', [\App\Http\Controllers\User\ProfileController::class, 'updatePassword'])->name('password.update');
        });
    });

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
    Route::get('/requests', [EventRequestController::class, 'myRequests'])->name('event_requests.index');

    Route::get('/events/{event}/request', [EventRequestController::class, 'create'])
        ->name('event-requests.create');
    Route::post('/events/{event}/request', [EventRequestController::class, 'store'])
        ->name('event-requests.store');

    Route::get('/event-requests/{eventRequest}/edit', [EventRequestController::class, 'edit'])->name('event_requests.edit');
    Route::put('/eventrequests/{eventRequest}', [EventRequestController::class, 'update'])->name('event_requests.update');

    Route::get('/event-requests/{eventRequest}', [EventRequestController::class, 'show'])->name('event_requests.show');
    Route::post('/event-requests/{eventRequest}/pay', [EventRequestController::class, 'completePayment'])->name('event_requests.pay');
    Route::post('/event-requests/{eventRequest}/pay', [EventRequestController::class, 'completePayment'])->name('event_requests.pay');
    Route::post('/event-requests/{eventRequest}/remake', [EventRequestController::class, 'remake'])->name('event_requests.remake');

// Admin-side

    // Admin-side for event requests
    Route::prefix('admin')->middleware(['auth','role:admin'])->group(function() {
        Route::get('/event-requests', [EventRequestController::class, 'adminIndex'])->name('admin.event_requests.index');
        Route::post('/event-requests/{eventRequest}/approve', [EventRequestController::class, 'approve'])->name('admin.event_requests.approve');
        Route::post('/event-requests/{eventRequest}/decline', [EventRequestController::class, 'decline'])->name('admin.event_requests.decline');
    });

});

Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/admin/admins', [\App\Http\Controllers\Admin\AdminController::class, 'index'])->name('admin.admins.index');
    Route::get('/admin/admins/create', [\App\Http\Controllers\Admin\AdminController::class, 'create'])->name('admin.admins.create');
    Route::post('/admin/admins', [\App\Http\Controllers\Admin\AdminController::class, 'store'])->name('admin.admins.store');
    Route::get('/admin/admins/{user}/edit', [\App\Http\Controllers\Admin\AdminController::class, 'edit'])->name('admin.admins.edit');
    Route::put('/admin/admins/{user}', [\App\Http\Controllers\Admin\AdminController::class, 'update'])->name('admin.admins.update');
    Route::delete('/admin/admins/{user}', [\App\Http\Controllers\Admin\AdminController::class, 'destroy'])->name('admin.admins.destroy');
    Route::patch('/admin/admins/{id}/restore', [\App\Http\Controllers\Admin\AdminController::class, 'restore'])->name('admin.admins.restore');
    Route::patch('/admin/admins/{user}/toggle', [\App\Http\Controllers\Admin\AdminController::class, 'toggle'])->name('admin.admins.toggle');
Route::resource('invitations', \App\Http\Controllers\InvitationController::class)
        ->only(['index', 'create', 'store', 'show']);
});

// Public invitation verification route (like ticket verification)
Route::get('/invitations/verify/{invitation}/{code}', [\App\Http\Controllers\InvitationController::class, 'verify'])->name('invitations.verify');


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
