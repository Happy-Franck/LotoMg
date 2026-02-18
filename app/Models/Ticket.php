<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = ['game_id', 'user_id', 'numbers', 'generated_options', 'is_selected', 'is_winner'];

    protected $casts = [
        'numbers' => 'array',
        'generated_options' => 'array',
        'is_selected' => 'boolean',
        'is_winner' => 'boolean',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

