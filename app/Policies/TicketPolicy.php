<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Ticket;

class TicketPolicy
{
    /**
     * Determine if the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Admins can view any ticket
        if ($user->isAdmin()) {
            return true;
        }

        // Operators can view any ticket (for scanning purposes)
        if ($user->isOperator()) {
            return true;
        }

        // Users can only view their own tickets
        return $user->id === $ticket->user_id;
    }

    /**
     * Determine if the user can download the ticket.
     */
    public function download(User $user, Ticket $ticket): bool
    {
        // Same rules as view for now
        return $this->view($user, $ticket);
    }

    /**
     * Determine if the user can see the QR code.
     */
    public function qrCode(User $user, Ticket $ticket): bool
    {
        // Same rules as view for now
        return $this->view($user, $ticket);
    }

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
}
