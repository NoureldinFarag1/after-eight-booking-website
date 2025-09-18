<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('events.index');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Public event routes
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Authentication required routes
Route::middleware('auth')->group(function () {

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
    });

    // Booking routes
    Route::get('/events/{event}/book', [BookingController::class, 'create'])->name('bookings.create');
    Route::resource('bookings', BookingController::class)->except(['create']);
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    // Ticket routes
    Route::resource('tickets', TicketController::class)->only(['index', 'show']);
    Route::get('/tickets/{ticket}/qr-code', [TicketController::class, 'qrCode'])->name('tickets.qr-code');
    Route::get('/tickets/{ticket}/download', [TicketController::class, 'download'])->name('tickets.download');

    // Operator/Admin ticket scanning
    Route::middleware('role:operator,admin')->group(function () {
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
         ->middleware('role:operator,admin')
         ->name('api.tickets.validate');
});
