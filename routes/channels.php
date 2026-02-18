<?php

use App\Models\Salon;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('salon.{salonId}', function ($user, $salonId) {
    $salon = Salon::find($salonId);
    return $salon && $salon->participants->contains($user->id);
});
