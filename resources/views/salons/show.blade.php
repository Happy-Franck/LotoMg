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
                            <p class="text-xl" id="winner-name"></p>
                        </div>
                    </div>
                </div>

                <!-- Participants Sidebar -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Participants (<span id="participants-count">{{ $salon->participants->count() }}</span>)</h3>
                            @if($salon->user_id !== auth()->id())
                                <form action="{{ route('salons.leave', $salon) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white text-xs px-2 py-1 rounded">
                                        Quitter
                                    </button>
                                </form>
                            @endif
                        </div>
                        <ul id="participants-list" class="divide-y">
                            @foreach($salon->participants as $participant)
                                <li class="py-2 flex items-center justify-between" data-user-id="{{ $participant->id }}">
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
            background: linear-gradient(145deg, #d4edda, #a5d6a7) !important;
            border-color: #00c853 !important;
            animation: winner-pulse 1s ease-in-out infinite;
            box-shadow: 0 0 20px rgba(0, 200, 83, 0.5);
        }
        .ticket.loser {
            background: linear-gradient(145deg, #ffebee, #ef9a9a) !important;
            border-color: #f44336 !important;
            opacity: 0.7;
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
        @keyframes winner-pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 20px rgba(0, 200, 83, 0.5); }
            50% { transform: scale(1.02); box-shadow: 0 0 30px rgba(0, 200, 83, 0.8); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>

    <script type="module">
        // Attendre que Echo soit disponible
        const waitForEcho = () => {
            return new Promise((resolve) => {
                if (window.Echo) {
                    resolve(window.Echo);
                } else {
                    const checkEcho = setInterval(() => {
                        if (window.Echo) {
                            clearInterval(checkEcho);
                            resolve(window.Echo);
                        }
                    }, 100);
                }
            });
        };

        waitForEcho().then((Echo) => {
            const salonId = {{ $salon->id }};
        const currentUserId = {{ auth()->id() }};
        const salonOwnerId = {{ $salon->user_id }};
        const isParticipant = {{ $salon->participants->contains(auth()->id()) ? 'true' : 'false' }};
        let currentGame = null;
        let timerInterval = null;

        // Start Game
        const startGameBtn = document.getElementById('start-game-btn');
        if (startGameBtn) {
            startGameBtn.addEventListener('click', async () => {
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
        }

        function handleGameStarted(game) {
            currentGame = game;
            document.getElementById('start-game-btn').classList.add('hidden');
            
            if (isParticipant) {
                document.getElementById('timer-container').classList.remove('hidden');
                document.getElementById('ticket-selection').classList.remove('hidden');

                // Display ticket options
                const myTicket = game.tickets.find(t => t.user_id === currentUserId);
                if (myTicket && myTicket.generated_options) {
                    displayTicketOptions(myTicket.generated_options);
                    startSelectionTimer();
                }
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
                    
                    // Vérifier si tous les tickets sont sélectionnés
                    checkIfAllTicketsSelected();
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
        console.log('Connecting to salon channel:', salonId);
        
        Echo.private(`salon.${salonId}`)
            .subscribed(() => {
                console.log('✅ Successfully subscribed to salon channel');
            })
            .error((error) => {
                console.error('❌ Error subscribing to salon channel:', error);
            })
            .listen('UserJoinedSalon', (e) => {
                console.log('👤 UserJoinedSalon event received:', e);
                addParticipant(e.user);
                updateParticipantsCount(e.participants_count);
            })
            .listen('UserLeftSalon', (e) => {
                console.log('👋 UserLeftSalon event received:', e);
                removeParticipant(e.user.id);
                updateParticipantsCount(e.participants_count);
            })
            .listen('GameStarted', (e) => {
                console.log('🎮 GameStarted event received:', e);
                fetch(`/games/${e.game_id}/status`)
                    .then(res => res.json())
                    .then(game => handleGameStarted(game));
            })
            .listen('TicketSelected', (e) => {
                console.log('🎫 TicketSelected event received:', e);
                checkIfAllTicketsSelected();
            })
            .listen('NumberDrawn', (e) => {
                console.log('🎲 NumberDrawn event received:', e);
                console.log('Current game:', currentGame);
                displayDrawnNumber(e.number);
                highlightMatchingNumbers(e.number);
            })
            .listen('GameFinished', (e) => {
                console.log('🏆 GameFinished event received:', e);
                showWinner(e.winner_name, e.winner_id);
            });

        function addParticipant(user) {
            const participantsList = document.getElementById('participants-list');
            const existingParticipant = participantsList.querySelector(`[data-user-id="${user.id}"]`);
            
            if (!existingParticipant) {
                const li = document.createElement('li');
                li.className = 'py-2 flex items-center justify-between';
                li.dataset.userId = user.id;
                li.innerHTML = `
                    <span>${user.name}</span>
                    ${user.id === salonOwnerId ? '<span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Propriétaire</span>' : ''}
                `;
                participantsList.appendChild(li);
            }
        }

        function removeParticipant(userId) {
            const participant = document.querySelector(`[data-user-id="${userId}"]`);
            if (participant) {
                participant.remove();
            }
        }

        function updateParticipantsCount(count) {
            document.getElementById('participants-count').textContent = count;
        }

        const drawnNumbersSet = new Set(); // Pour éviter les doublons

        function displayDrawnNumber(number) {
            // Vérifier si le numéro a déjà été affiché
            if (drawnNumbersSet.has(number)) {
                console.log(`Number ${number} already displayed, skipping`);
                return;
            }
            
            drawnNumbersSet.add(number);
            const container = document.getElementById('drawn-numbers');
            const numberDiv = document.createElement('div');
            numberDiv.className = 'drawn-number';
            numberDiv.textContent = number;
            container.appendChild(numberDiv);
        }

        function showWinner(winnerName, winnerId) {
            // Afficher l'annonce pour tous
            document.getElementById('winner-announcement').classList.remove('hidden');
            
            // Message différent selon si on a gagné ou perdu
            if (winnerId === currentUserId) {
                document.getElementById('winner-name').textContent = `🎉 Félicitations ! Vous avez gagné !`;
            } else {
                document.getElementById('winner-name').textContent = `😔 ${winnerName} a gagné ! Vous avez perdu.`;
            }
            
            // Colorer les tickets
            const tickets = document.querySelectorAll('.ticket[data-ticket-id]');
            tickets.forEach(ticket => {
                const ticketUserId = parseInt(ticket.dataset.userId);
                
                if (ticketUserId === winnerId) {
                    // Ticket gagnant en vert
                    ticket.classList.add('winner');
                    ticket.classList.remove('loser');
                } else {
                    // Tickets perdants en rouge
                    ticket.classList.add('loser');
                    ticket.classList.remove('winner');
                }
            });
        }

        async function checkIfAllTicketsSelected() {
            if (!currentGame) return;

            const response = await fetch(`/games/${currentGame.id}/status`);
            const game = await response.json();

            const allSelected = game.tickets.every(t => t.is_selected);

            if (allSelected) {
                // Tous les tickets sont sélectionnés, afficher les tickets et commencer le tirage
                document.getElementById('ticket-selection').classList.add('hidden');
                document.getElementById('timer-container').classList.add('hidden');
                document.getElementById('drawn-numbers-container').classList.remove('hidden');
                document.getElementById('player-tickets').classList.remove('hidden');

                displayAllPlayerTickets(game.tickets);
            }
        }

        function displayAllPlayerTickets(tickets) {
            const container = document.getElementById('tickets-container');
            container.innerHTML = '';

            tickets.forEach(ticket => {
                const ticketDiv = document.createElement('div');
                ticketDiv.className = 'ticket';
                ticketDiv.dataset.ticketId = ticket.id;
                ticketDiv.dataset.userId = ticket.user.id; // Ajouter l'ID de l'utilisateur

                // Titre avec le nom du joueur
                const title = document.createElement('div');
                title.className = 'text-center font-semibold mb-2';
                title.textContent = ticket.user.name;
                if (ticket.user.id === currentUserId) {
                    title.textContent += ' (Vous)';
                    title.classList.add('text-blue-600');
                }
                ticketDiv.appendChild(title);

                // Afficher la grille du ticket
                ticket.numbers.forEach(row => {
                    const rowDiv = document.createElement('div');
                    rowDiv.className = 'ticket-row';

                    row.forEach(num => {
                        const cell = document.createElement('div');
                        cell.className = 'ticket-cell';
                        cell.dataset.number = num;
                        
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

        function highlightMatchingNumbers(drawnNumber) {
            const allCells = document.querySelectorAll('.ticket-cell[data-number]');
            allCells.forEach(cell => {
                if (parseInt(cell.dataset.number) === drawnNumber) {
                    cell.classList.add('highlight');
                }
            });

            // Vérifier les lignes gagnantes
            checkWinningLines();
        }

        function checkWinningLines() {
            const tickets = document.querySelectorAll('.ticket[data-ticket-id]');
            tickets.forEach(ticket => {
                const rows = ticket.querySelectorAll('.ticket-row');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('.ticket-cell:not(.empty)');
                    const highlightedCells = row.querySelectorAll('.ticket-cell.highlight');

                    if (cells.length > 0 && cells.length === highlightedCells.length) {
                        ticket.classList.add('winner');
                    }
                });
            });
        }
        }); // Fin du waitForEcho
    </script>
</x-app-layout>
