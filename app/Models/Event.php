<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Schema;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'location',
        'event_date',
        'event_time',
        'capacity',
        'initial_capacity',
        'type',
        'status',
        'image_url',
        'terms_conditions',
        'fee_type',
        'fee_amount',
        'finance_officer_id',
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_time' => 'datetime',
        'status' => EventStatus::class,
        'fee_amount' => 'float',
    ];

    public function calculateFee(float $total): float
    {
        if ($this->fee_type === 'percentage' && $this->fee_amount > 0) {
            return ($total * $this->fee_amount) / 100;
        }
        if ($this->fee_type === 'fixed' && $this->fee_amount > 0) {
            return $this->fee_amount;
        }
        return 0.0;
    }

    public function getTotalWithFees(float $total): float
    {
        return $total + $this->calculateFee($total);
    }

    public function getAllocatedCapacityAttribute(): int
    {
        return (int) $this->ticketTypes()->whereNotNull('capacity')->sum('capacity');
    }

    public function getRemainingAllocatableCapacityAttribute(): int
    {
        return max(0, (int)$this->capacity - $this->getAllocatedCapacityAttribute());
    }

    /**
     * Get bookings for this event
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get tickets for this event through bookings
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Get ticket types for this event
     */
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    /**
     * Get available seats for the event
     */
    public function getAvailableSeatsAttribute(): int
    {
        $booked = $this->bookings()->sum('quantity');
        // Sum approved event request attendees (attendee_count) if column exists
        $approvedRequestAttendees = 0;
    if (Schema::hasTable('event_requests') && Schema::hasColumn('event_requests','attendee_count')) {
            $approvedRequestAttendees = (int) $this->requests()
                ->where('status', 'approved')
                ->sum('attendee_count');
        }
        return max(0, $this->capacity - $booked - $approvedRequestAttendees);
    }

    /**
     * Check if event is bookable
     */
    public function isBookable(): bool
    {
        return $this->status === EventStatus::PUBLISHED
            && $this->event_date >= now()->toDateString()
            && $this->getAvailableSeatsAttribute() > 0;
    }

    /**
     * Check if event is sold out
     */
    public function isSoldOut(): bool
    {
        return $this->getAvailableSeatsAttribute() <= 0;
    }

    /**
     * Scope for published events
     */
    public function scopePublished($query)
    {
        return $query->where('status', EventStatus::PUBLISHED);
    }

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->toDateString());
    }

    /**
     * Scope for past events
     */
    public function scopePast($query)
    {
        return $query->where('event_date', '<', now()->toDateString());
    }

    public function requests()
    {
        return $this->hasMany(EventRequest::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function financeOfficer()
    {
        return $this->belongsTo(\App\Models\User::class, 'finance_officer_id');
    }

    public function operators()
    {
        return $this->belongsToMany(User::class, 'event_operator', 'event_id', 'user_id');
    }
}
