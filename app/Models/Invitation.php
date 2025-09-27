<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'message',
        'status',
        'event_id',
    ];


    public function event()
    {
        return $this->belongsTo(\App\Models\Event::class);
    }

}
