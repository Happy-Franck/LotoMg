<?php

namespace App\Events;

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
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => 'Le salon va être supprimé. Redirection...',
        ];
    }
}
