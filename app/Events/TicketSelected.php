<?php

namespace App\Events;

use App\Models\Game;
use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketSelected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Ticket $ticket, public Game $game)
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
            'user_id' => $this->ticket->user_id,
            'user_name' => $this->ticket->user->name,
        ];
    }
}

