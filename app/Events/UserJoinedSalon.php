<?php

namespace App\Events;

use App\Models\Salon;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserJoinedSalon implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user, public Salon $salon)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('salon.' . $this->salon->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'participants_count' => $this->salon->participants()->count(),
        ];
    }
}

