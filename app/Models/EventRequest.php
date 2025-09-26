<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'payload',
        'status',
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
}
