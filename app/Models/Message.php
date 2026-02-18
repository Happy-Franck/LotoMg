<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['salon_id', 'user_id', 'content'];

    protected $with = ['user'];

    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
