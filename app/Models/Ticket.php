<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'event_id',
        'booking_id',
        'ticket_number',
        'qr_code',
        'status',
        'scanned_at',
        'scanned_by',
        'seat_number',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'status' => TicketStatus::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TK-' . strtoupper(Str::random(12));
            }
            if (empty($ticket->qr_code)) {
                $ticket->qr_code = Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns the ticket
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event for this ticket
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the booking this ticket belongs to
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user who scanned this ticket
     */
    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    /**
     * Check if ticket is valid for scanning
     */
    public function isValid(): bool
    {
        return $this->status === TicketStatus::VALID
            && $this->event->event_date >= now()->toDateString();
    }

    /**
     * Check if ticket has been used
     */
    public function isUsed(): bool
    {
        return $this->status === TicketStatus::USED;
    }

    /**
     * Mark ticket as used
     */
    public function markAsUsed(int $scannedByUserId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $this->update([
            'status' => TicketStatus::USED,
            'scanned_at' => now(),
            'scanned_by' => $scannedByUserId,
        ]);

        return true;
    }

    /**
     * Check if ticket is expired
     */
    public function isExpired(): bool
    {
        return $this->event->event_date < now()->toDateString()
            && $this->status === TicketStatus::VALID;
    }

    /**
     * Generate QR code URL for the ticket
     */
    public function getQrCodeUrlAttribute(): string
    {
        return route('tickets.scan', ['qr_code' => $this->qr_code]);
    }

    /**
     * Scope for valid tickets
     */
    public function scopeValid($query)
    {
        return $query->where('status', TicketStatus::VALID);
    }

    /**
     * Scope for used tickets
     */
    public function scopeUsed($query)
    {
        return $query->where('status', TicketStatus::USED);
    }
}
