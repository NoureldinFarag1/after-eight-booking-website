<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'fee_type',
        'fee_amount',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Calculate fee for given base price (defaults to its own price)
     */
    public function calculateFee(?float $base = null): float
    {
        $baseAmount = $base ?? (float)$this->price;
        if(!$this->fee_type || $this->fee_amount <= 0){
            return 0.0;
        }
        return match($this->fee_type){
            'percentage' => ($baseAmount * (float)$this->fee_amount)/100,
            'fixed' => (float)$this->fee_amount,
            default => 0.0,
        };
    }

    public function getTotalWithFeeAttribute(): float
    {
        return (float)$this->price + $this->calculateFee();
    }
}
