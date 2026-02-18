<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['salon_id', 'status', 'drawn_numbers', 'winner_id', 'started_at', 'finished_at'];

    protected $casts = [
        'drawn_numbers' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }
}

