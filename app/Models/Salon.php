<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salon extends Model
{
    protected $fillable = ['name', 'description', 'user_id'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'salon_users');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function currentGame()
    {
        return $this->hasOne(Game::class)->whereIn('status', ['waiting', 'selecting', 'playing'])->latest();
    }
}
