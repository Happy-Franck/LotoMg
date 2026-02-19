<?php

namespace App\Console\Commands;

use App\Events\GameFinished;
use App\Events\NumberDrawn;
use App\Models\Game;
use Illuminate\Console\Command;

class ProcessGameDrawing extends Command
{
    protected $signature = 'game:process-drawing';
    protected $description = 'Process automatic game drawing for loto games';

    public function handle()
    {
        // Trouver les jeux en attente de tirage (tous les tickets sélectionnés)
        $games = Game::with(['salon', 'tickets.user'])
            ->where('status', 'selecting')
            ->whereDoesntHave('tickets', function($query) {
                $query->where('is_selected', false);
            })
            ->get();

        if ($games->isEmpty()) {
            return 0;
        }

        foreach ($games as $game) {
            $this->info("Starting drawing for game {$game->id} in salon {$game->salon_id}");
            $this->startDrawing($game);
        }

        return 0;
    }

    private function startDrawing(Game $game)
    {
        // Vérifier que le jeu n'est pas déjà terminé
        if ($game->status === 'finished') {
            $this->warn("Game {$game->id} is already finished, skipping");
            return;
        }

        $game->update(['status' => 'playing']);

        // Générer la liste des numéros (1-99)
        $numbers = range(1, 99);
        shuffle($numbers);

        $drawnNumbers = [];
        $winner = null;

        // Tirer les numéros un par un
        foreach ($numbers as $number) {
            // Vérifier à chaque itération que le jeu n'a pas été arrêté
            $game->refresh();
            if ($game->status === 'finished') {
                $this->warn("Game {$game->id} was stopped externally");
                break;
            }

            $drawnNumbers[] = $number;
            $game->update(['drawn_numbers' => $drawnNumbers]);

            // Broadcaster le numéro tiré
            $event = new NumberDrawn($game, $number);
            broadcast($event);
            
            \Log::info("Broadcasting number: {$number} for game {$game->id}");

            $this->info("Drew number: {$number}");

            // Vérifier s'il y a un gagnant
            $winner = $this->checkForWinner($game, $drawnNumbers);

            if ($winner) {
                $this->info("Winner found: {$winner->user->name}");
                break; // Arrêter le tirage immédiatement
            }

            // Pause de 1 seconde entre chaque tirage
            sleep(1);
        }

        // Marquer le jeu comme terminé
        $game->update([
            'status' => 'finished',
            'winner_id' => $winner ? $winner->user_id : null,
            'finished_at' => now(),
        ]);

        if ($winner) {
            $winner->update(['is_winner' => true]);
        }

        $finishedEvent = new GameFinished($game);
        broadcast($finishedEvent);
        
        \Log::info("Game {$game->id} finished, winner: " . ($winner ? $winner->user->name : 'none'));

        $this->info("Game {$game->id} finished");
    }

    private function checkForWinner(Game $game, array $drawnNumbers)
    {
        foreach ($game->tickets as $ticket) {
            if ($this->hasWinningLine($ticket->numbers, $drawnNumbers)) {
                return $ticket;
            }
        }

        return null;
    }

    private function hasWinningLine(array $ticketGrid, array $drawnNumbers)
    {
        // Vérifier chaque ligne (3 lignes)
        foreach ($ticketGrid as $row) {
            $numbersInRow = array_filter($row, fn($num) => $num !== null);
            $matchedNumbers = array_filter($numbersInRow, fn($num) => in_array($num, $drawnNumbers));

            // Si tous les numéros de la ligne sont tirés, c'est gagné
            if (count($matchedNumbers) === count($numbersInRow)) {
                return true;
            }
        }

        return false;
    }
}
