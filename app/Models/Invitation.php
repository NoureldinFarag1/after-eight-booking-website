<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'token',
        'qr_code_path',
        'qr_code',
        'qr_status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(32);
            }
            if (empty($invitation->qr_code)) {
                $invitation->qr_code = Str::random(40);
            }
        });
    }

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
