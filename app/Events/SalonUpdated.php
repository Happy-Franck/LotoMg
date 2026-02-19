<?php

namespace App\Events;

use App\Models\Salon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalonUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Salon $salon)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('salons'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'salon' => [
                'id' => $this->salon->id,
                'name' => $this->salon->name,
                'description' => $this->salon->description,
                'owner' => [
                    'id' => $this->salon->owner->id,
                    'name' => $this->salon->owner->name,
                ],
                'participants_count' => $this->salon->participants->count(),
                'has_active_game' => $this->salon->currentGame()->exists(),
            ],
        ];
    }
}
