<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRequest extends Model
{
    use HasFactory;

    /**
     * Cached result for schema checks that do not change at runtime.
     */
    protected static ?bool $supportsExpiresAt = null;

    protected $fillable = [
        'event_id',
        'user_id',
        'payload',
        'status',
        'approved_at',
        'expires_at',
        'paid_at',
        'primary_name',
        'primary_email',
        'primary_social_url',
        'primary_ticket_type_id',
        'guests',
        'attendee_count',
    ];

    protected $casts = [
        'guests' => 'array',
        'payload' => 'array',
        'attendee_count' => 'integer',
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function primaryTicketType()
    {
        return $this->belongsTo(TicketType::class, 'primary_ticket_type_id');
    }

    /**
     * Determine whether the request has an expired payment window.
     */
    public function hasPaymentWindowExpired(): bool
    {
    if ($this->status !== \App\Enums\EventRequestStatus::AWAITING_PAYMENT->value) {
            return false;
        }

        if (! static::supportsExpiresAtColumn()) {
            return false;
        }

        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Transition an awaiting payment request to expired when the deadline passes.
     */
    public function expireIfPastDeadline(): bool
    {
        if (! $this->hasPaymentWindowExpired()) {
            return false;
        }

    $this->status = \App\Enums\EventRequestStatus::EXPIRED->value;
        $this->save();

        return true;
    }

    protected static function supportsExpiresAtColumn(): bool
    {
        if (static::$supportsExpiresAt === null) {
            $table = (new static())->getTable();
            static::$supportsExpiresAt = \Illuminate\Support\Facades\Schema::hasColumn($table, 'expires_at');
        }

        return static::$supportsExpiresAt;
    }
}
