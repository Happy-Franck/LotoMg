<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🎰 {{ $salon->name }}
            </h2>
            <div class="flex gap-2">
                @can('update', $salon)
                    <a href="{{ route('salons.edit', $salon) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Modifier
                    </a>
                @endcan
                @can('delete', $salon)
                    <form action="{{ route('salons.destroy', $salon) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Supprimer
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if($salon->participants->contains(auth()->id()))
                <!-- Game Area -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div id="game-status" class="text-center mb-4">
                            <button id="start-game-btn" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg">
                                🎮 Démarrer une partie
                            </button>
                        </div>

                        <!-- Timer -->
                        <div id="timer-container" class="hidden text-center mb-6">
                            <div class="text-4xl font-bold text-red-600" id="timer">15</div>
                            <p class="text-gray-600">Sélectionnez votre ticket !</p>
                        </div>

                        <!-- Ticket Selection -->
                        <div id="ticket-selection" class="hidden mb-6">
                            <h3 class="text-xl font-semibold mb-4 text-center">Choisissez votre ticket</h3>
                            <div id="ticket-options" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
                        </div>

                        <!-- Drawn Numbers -->
                        <div id="drawn-numbers-container" class="hidden mb-6">
                            <h3 class="text-xl font-semibold mb-4 text-center">Numéros tirés</h3>
                            <div id="drawn-numbers" class="flex flex-wrap gap-2 justify-center"></div>
                        </div>

                        <!-- Player Tickets -->
                        <div id="player-tickets" class="hidden">
                            <h3 class="text-xl font-semibold mb-4 text-center">Tickets des joueurs</h3>
                            <div id="tickets-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>
                        </div>

                        <!-- Winner Announcement -->
                        <div id="winner-announcement" class="hidden text-center py-8">
                            <div class="text-6xl mb-4">🎉</div>
                            <h2 class="text-3xl font-bold text-green-600 mb-2">Félicitations !</h2>
                            <p class="text-xl" id="winner-name"></p>
                        </div>
                    </div>
                </div>

                <!-- Participants Sidebar -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Participants ({{ $salon->participants->count() }})</h3>
                            @if($salon->user_id !== auth()->id())
                                <form action="{{ route('salons.leave', $salon) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white text-xs px-2 py-1 rounded">
                                        Quitter
                                    </button>
                                </form>
                            @endif
                        </div>
                        <ul class="divide-y">
                            @foreach($salon->participants as $participant)
                                <li class="py-2 flex items-center justify-between">
                                    <span>{{ $participant->name }}</span>
                                    @if($participant->id === $salon->user_id)
                                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Propriétaire</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <p class="mb-4">Vous devez rejoindre le salon pour participer au jeu.</p>
                        <form action="{{ route('salons.join', $salon) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Rejoindre le salon
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($salon->participants->contains(auth()->id()))
    <style>
        .ticket {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .ticket:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-color: #667eea;
        }
        .ticket.selected {
            border-color: #00c853;
            background: linear-gradient(145deg, #d4edda, #c3e6cb);
        }
        .ticket.winner {
            animation: winner 1s ease-in-out infinite;
        }
        .ticket-row {
            display: flex;
            justify-content: center;
            gap: 0.25rem;
            margin-bottom: 0.25rem;
        }
        .ticket-cell {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e1e8ed;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            background: white;
        }
        .ticket-cell.empty {
            background: #f8f9fa;
            border-color: #e9ecef;
        }
        .ticket-cell.highlight {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            border-color: #ffd700;
            animation: pulse 1s infinite;
        }
        .drawn-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        @keyframes winner {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>

    <script type="module">
        import Echo from 'laravel-echo';
        import Pusher from 'pusher-js';

        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ config('broadcasting.connections.reverb.key') }}',
            wsHost: '{{ config('broadcasting.connections.reverb.host') }}',
            wsPort: {{ config('broadcasting.connections.reverb.port') }},
            wssPort: {{ config('broadcasting.connections.reverb.port') }},
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
        });

        const salonId = {{ $salon->id }};
        const currentUserId = {{ auth()->id() }};
        let currentGame = null;
        let timerInterval = null;

        // Start Game
        document.getElementById('start-game-btn').addEventListener('click', async () => {
            try {
                const response = await fetch(`/salons/${salonId}/game/start`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (response.ok) {
                    const game = await response.json();
                    handleGameStarted(game);
                }
            } catch (error) {
                console.error('Error starting game:', error);
            }
        });

        function handleGameStarted(game) {
            currentGame = game;
            document.getElementById('start-game-btn').classList.add('hidden');
            document.getElementById('timer-container').classList.remove('hidden');
            document.getElementById('ticket-selection').classList.remove('hidden');

            // Display ticket options
            const myTicket = game.tickets.find(t => t.user_id === currentUserId);
            if (myTicket && myTicket.generated_options) {
                displayTicketOptions(myTicket.generated_options);
                startSelectionTimer();
            }
        }

        function displayTicketOptions(options) {
            const container = document.getElementById('ticket-options');
            container.innerHTML = '';

            options.forEach((ticketGrid, index) => {
                const ticketDiv = document.createElement('div');
                ticketDiv.className = 'ticket';
                ticketDiv.onclick = () => selectTicket(index);

                ticketGrid.forEach(row => {
                    const rowDiv = document.createElement('div');
                    rowDiv.className = 'ticket-row';

                    row.forEach(num => {
                        const cell = document.createElement('div');
                        cell.className = 'ticket-cell';
                        if (num !== null) {
                            cell.textContent = num;
                        } else {
                            cell.classList.add('empty');
                        }
                        rowDiv.appendChild(cell);
                    });

                    ticketDiv.appendChild(rowDiv);
                });

                container.appendChild(ticketDiv);
            });
        }

        async function selectTicket(index) {
            if (!currentGame) return;

            try {
                const response = await fetch(`/games/${currentGame.id}/select-ticket`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ticket_index: index })
                });

                if (response.ok) {
                    clearInterval(timerInterval);
                    document.getElementById('timer-container').classList.add('hidden');
                    document.getElementById('ticket-selection').classList.add('hidden');
                    alert('Ticket sélectionné ! En attente des autres joueurs...');
                }
            } catch (error) {
                console.error('Error selecting ticket:', error);
            }
        }

        function startSelectionTimer() {
            let timeLeft = 15;
            const timerEl = document.getElementById('timer');

            timerInterval = setInterval(() => {
                timeLeft--;
                timerEl.textContent = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    // Auto-select random ticket
                    const randomIndex = Math.floor(Math.random() * 4);
                    selectTicket(randomIndex);
                }
            }, 1000);
        }

        // Listen for game events
        Echo.private(`salon.${salonId}`)
            .listen('GameStarted', (e) => {
                fetch(`/games/${e.game_id}/status`)
                    .then(res => res.json())
                    .then(game => handleGameStarted(game));
            })
            .listen('TicketSelected', (e) => {
                console.log(`${e.user_name} a sélectionné son ticket`);
            })
            .listen('NumberDrawn', (e) => {
                displayDrawnNumber(e.number);
            })
            .listen('GameFinished', (e) => {
                showWinner(e.winner_name);
            });

        function displayDrawnNumber(number) {
            const container = document.getElementById('drawn-numbers');
            const numberDiv = document.createElement('div');
            numberDiv.className = 'drawn-number';
            numberDiv.textContent = number;
            container.appendChild(numberDiv);
        }

        function showWinner(winnerName) {
            document.getElementById('winner-announcement').classList.remove('hidden');
            document.getElementById('winner-name').textContent = `${winnerName} a gagné !`;
        }
    </script>
    @endif
</x-app-layout>
