<?php

use App\Models\Salon;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('salon.{salonId}', function ($user, $salonId) {
    $salon = Salon::with('participants')->find($salonId);
    if (!$salon) {
        return false;
    }
    
    // Vérifier si l'utilisateur est participant
    $isParticipant = $salon->participants->contains($user->id);
    
    \Log::info('Channel authorization', [
        'user_id' => $user->id,
        'salon_id' => $salonId,
        'is_participant' => $isParticipant,
        'participants' => $salon->participants->pluck('id')->toArray()
    ]);
    
    return $isParticipant;
});

// Channel public pour la liste des salons
Broadcast::channel('salons', function ($user) {
    return true; // Tous les utilisateurs authentifiés peuvent écouter
});
