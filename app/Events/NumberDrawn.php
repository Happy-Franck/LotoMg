<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NumberDrawn implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Game $game, public int $number)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('salon.' . $this->game->salon_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'number' => $this->number,
            'drawn_numbers' => $this->game->drawn_numbers,
        ];
    }
}

