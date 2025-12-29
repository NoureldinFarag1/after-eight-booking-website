<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Generate QR code for a ticket
     */
    public function generateTicketQrCode(Ticket $ticket): string
    {
        // Create QR code data with ticket verification URL
        $qrData = route('tickets.verify', [
            'ticket' => $ticket->id,
            'code' => $ticket->qr_code
        ]);

        // Generate QR code as PNG
        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->errorCorrection('M')
            ->generate($qrData);

        // Store QR code image
        $filename = "qr-codes/ticket-{$ticket->id}.png";
        Storage::disk('public')->put($filename, $qrCode);

        return $filename;
    }

    /**
     * Generate QR code for multiple tickets and return paths
     */
    public function generateMultipleTicketQrCodes(array $tickets): array
    {
        $qrCodes = [];
        foreach ($tickets as $ticket) {
            $qrCodes[$ticket->id] = $this->generateTicketQrCode($ticket);
        }
        return $qrCodes;
    }

    /**
     * Get QR code path for a ticket (generate if doesn't exist)
     */
    public function getTicketQrCodePath(Ticket $ticket): string
    {
        $filename = "qr-codes/ticket-{$ticket->id}.png";

        if (!Storage::disk('public')->exists($filename)) {
            return $this->generateTicketQrCode($ticket);
        }

        return $filename;
    }

    /**
     * Generate QR code for an invitation
     */
    public function generateInvitationQrCode(\App\Models\Invitation $invitation): string
    {
        // Create QR code data with invitation verification URL (similar to tickets)
        $qrData = route('invitations.verify', [
            'invitation' => $invitation->id,
            'code' => $invitation->qr_code
        ]);

        // Generate QR code as PNG
        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->errorCorrection('M')
            ->generate($qrData);

        // Store QR code image
        $filename = "qr-codes/invitation-{$invitation->id}.png";
        Storage::disk('public')->put($filename, $qrCode);

        return $filename;
    }

    /**
     * Get QR code path for an invitation (generate if doesn't exist)
     */
    public function getInvitationQrCodePath(\App\Models\Invitation $invitation): string
    {
        $filename = "qr-codes/invitation-{$invitation->id}.png";

        if (!Storage::disk('public')->exists($filename)) {
            return $this->generateInvitationQrCode($invitation);
        }

        return $filename;
    }

    /**
     * Clean up QR code files for cancelled tickets
     */
    public function cleanupTicketQrCode(Ticket $ticket): void
    {
        $filename = "qr-codes/ticket-{$ticket->id}.png";
        if (Storage::disk('public')->exists($filename)) {
            Storage::disk('public')->delete($filename);
        }
    }

    /**
     * Clean up QR code files for invitations
     */
    public function cleanupInvitationQrCode(\App\Models\Invitation $invitation): void
    {
        $filename = "qr-codes/invitation-{$invitation->id}.png";
        if (Storage::disk('public')->exists($filename)) {
            Storage::disk('public')->delete($filename);
        }
    }
}
