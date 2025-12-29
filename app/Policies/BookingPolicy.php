<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        // Admins can view any booking; users can view their own bookings
        return $user->isAdmin() || $user->id === $booking->user_id;
    }

    /**
     * Determine whether the user can update the booking.
     * Used for edit/update and for cancel flow in controller.
     */
    public function update(User $user, Booking $booking): bool
    {
        // Allow only admins to edit/update bookings in admin UI
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the booking.
     */
    public function delete(User $user, Booking $booking): bool
    {
        // Restrict deletes to admins
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        // Admins or booking owner can cancel
        return $user->isAdmin() || $user->id === $booking->user_id;
    }

    /**
     * Determine whether the user can create a booking.
     * Business rule: Administrators are NOT allowed to create bookings.
     */
    public function create(User $user): bool
    {
        return !$user->isAdmin();
    }
}
