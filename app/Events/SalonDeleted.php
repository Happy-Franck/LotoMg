<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalonDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $salonId)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('salon.' . $this->salonId),
            new Channel('salons'), // Aussi broadcaster sur le channel public
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'salon_id' => $this->salonId,
            'message' => 'Le salon va être supprimé. Redirection...',
        ];
    }
}
