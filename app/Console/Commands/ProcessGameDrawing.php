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
        $games = Game::where('status', 'selecting')->get();

        foreach ($games as $game) {
            $allTicketsSelected = $game->tickets()->where('is_selected', false)->count() === 0;

            if ($allTicketsSelected) {
                $this->info("Starting drawing for game {$game->id}");
                $this->startDrawing($game);
            }
        }

        return 0;
    }

    private function startDrawing(Game $game)
    {
        $game->update(['status' => 'playing']);

        // Générer la liste des numéros (1-99)
        $numbers = range(1, 99);
        shuffle($numbers);

        $drawnNumbers = [];
        $winner = null;

        // Tirer les numéros un par un
        foreach ($numbers as $number) {
            $drawnNumbers[] = $number;
            $game->update(['drawn_numbers' => $drawnNumbers]);

            // Broadcaster le numéro tiré
            broadcast(new NumberDrawn($game, $number));

            $this->info("Drew number: {$number}");

            // Vérifier s'il y a un gagnant
            $winner = $this->checkForWinner($game, $drawnNumbers);

            if ($winner) {
                $this->info("Winner found: {$winner->user->name}");
                break;
            }

            // Pause de 300ms entre chaque tirage
            usleep(300000);
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

        broadcast(new GameFinished($game));

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
