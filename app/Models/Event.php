<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'type',
        'price',
        'status',
        'image_url',
        'terms_conditions',
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_time' => 'datetime',
        'price' => 'decimal:2',
        'status' => EventStatus::class,
    ];

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
     * Get available seats for the event
     */
    public function getAvailableSeatsAttribute(): int
    {
        return $this->capacity - $this->bookings()->sum('quantity');
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
}
