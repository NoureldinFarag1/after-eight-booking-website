<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'name',
        'email',
        'message',
        'status',
        'event_id',
        'sender_id',
    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function event()
    {
        return $this->belongsTo(\App\Models\Event::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}