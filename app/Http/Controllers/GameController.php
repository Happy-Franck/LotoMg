<?php

namespace App\Http\Controllers;

use App\Events\GameFinished;
use App\Events\GameStarted;
use App\Events\NumberDrawn;
use App\Events\TicketSelected;
use App\Models\Game;
use App\Models\Salon;
use App\Models\Ticket;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function start(Salon $salon)
    {
        if (!$salon->participants->contains(auth()->id())) {
            return response()->json(['error' => 'Vous devez être membre du salon'], 403);
        }

        $existingGame = $salon->games()->whereIn('status', ['waiting', 'selecting', 'playing'])->first();
        if ($existingGame) {
            return response()->json(['error' => 'Une partie est déjà en cours'], 400);
        }

        $game = $salon->games()->create([
            'status' => 'selecting',
            'started_at' => now(),
        ]);

        // Générer 4 tickets pour chaque participant
        foreach ($salon->participants as $participant) {
            $options = [];
            for ($i = 0; $i < 4; $i++) {
                $options[] = $this->generateLottoTicket();
            }

            Ticket::create([
                'game_id' => $game->id,
                'user_id' => $participant->id,
                'numbers' => [],
                'generated_options' => $options,
                'is_selected' => false,
            ]);
        }

        broadcast(new GameStarted($game, $salon))->toOthers();

        return response()->json($game->load('tickets'));
    }

    public function selectTicket(Request $request, Game $game)
    {
        $validated = $request->validate([
            'ticket_index' => 'required|integer|min:0|max:3',
        ]);

        $ticket = $game->tickets()->where('user_id', auth()->id())->first();
        
        if (!$ticket) {
            return response()->json(['error' => 'Ticket non trouvé'], 404);
        }

        if ($ticket->is_selected) {
            return response()->json(['error' => 'Ticket déjà sélectionné'], 400);
        }

        $selectedNumbers = $ticket->generated_options[$validated['ticket_index']];
        $ticket->update([
            'numbers' => $selectedNumbers,
            'is_selected' => true,
        ]);

        broadcast(new TicketSelected($ticket, $game))->toOthers();

        // Vérifier si tous les tickets sont sélectionnés
        $allSelected = $game->tickets()->where('is_selected', false)->count() === 0;
        
        if ($allSelected) {
            // Tous les tickets sont sélectionnés, le scheduler va démarrer le tirage
            \Log::info("All tickets selected for game {$game->id}, drawing will start automatically");
        }

        return response()->json($ticket);
    }

    public function getStatus(Game $game)
    {
        return response()->json($game->load(['tickets.user', 'winner']));
    }

    private function generateLottoTicket()
    {
        $ticket = [];
        $columns = array_fill(0, 10, 0);

        while (count($ticket) < 15) {
            $num = rand(1, 99);
            $column = floor(($num - 1) / 10);

            if (!in_array($num, $ticket) && $columns[$column] < 3) {
                $ticket[] = $num;
                $columns[$column]++;
            }
        }

        sort($ticket);
        return $this->organizeTicket($ticket);
    }

    private function organizeTicket($numbers)
    {
        $grid = array_fill(0, 3, array_fill(0, 10, null));
        $columns = array_fill(0, 10, 0);
        $rows = [0, 0, 0];

        foreach ($numbers as $num) {
            $column = floor(($num - 1) / 10);
            $row = array_search(min($rows), $rows);

            while ($grid[$row][$column] !== null) {
                $row = ($row + 1) % 3;
            }

            $grid[$row][$column] = $num;
            $rows[$row]++;
        }

        return $grid;
    }
}
