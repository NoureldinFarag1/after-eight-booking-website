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
        'guests',
    ];

    protected $casts = [
        'guests' => 'array',
        'payload' => 'array',
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
}
