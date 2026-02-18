<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $salon->name }}
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Chat Section -->
                <div class="lg:col-span-2">
                    @if($salon->participants->contains(auth()->id()))
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900">
                                <h3 class="text-lg font-semibold mb-4">Chat</h3>
                                
                                <!-- Messages Container -->
                                <div id="messages" class="h-96 overflow-y-auto mb-4 p-4 bg-gray-50 rounded border">
                                    <div class="text-center text-gray-500">Chargement des messages...</div>
                                </div>

                                <!-- Message Form -->
                                <form id="message-form" class="flex gap-2">
                                    @csrf
                                    <input type="text" id="message-input" 
                                           class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                           placeholder="Votre message..." required>
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Envoyer
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 text-center">
                                <p class="mb-4">Vous devez rejoindre le salon pour voir le chat.</p>
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

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Description -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-semibold mb-2">Description</h3>
                            <p class="text-gray-600">{{ $salon->description ?? 'Aucune description' }}</p>
                            <p class="text-sm text-gray-500 mt-4">Créé par {{ $salon->owner->name }}</p>
                        </div>
                    </div>

                    <!-- Participants -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Participants ({{ $salon->participants->count() }})</h3>
                                @if($salon->participants->contains(auth()->id()))
                                    @if($salon->user_id !== auth()->id())
                                        <form action="{{ route('salons.leave', $salon) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white text-xs px-2 py-1 rounded">
                                                Quitter
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <form action="{{ route('salons.join', $salon) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white text-xs px-2 py-1 rounded">
                                            Rejoindre
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
                </div>
            </div>
        </div>
    </div>

    @if($salon->participants->contains(auth()->id()))
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

        const messagesContainer = document.getElementById('messages');
        const messageForm = document.getElementById('message-form');
        const messageInput = document.getElementById('message-input');
        const salonId = {{ $salon->id }};
        const currentUserId = {{ auth()->id() }};

        // Load initial messages
        fetch(`/salons/${salonId}/messages`)
            .then(res => res.json())
            .then(messages => {
                messagesContainer.innerHTML = '';
                messages.forEach(msg => appendMessage(msg));
                scrollToBottom();
            });

        // Listen for new messages
        Echo.private(`salon.${salonId}`)
            .listen('MessageSent', (e) => {
                appendMessage(e);
                scrollToBottom();
            });

        // Send message
        messageForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const content = messageInput.value.trim();
            if (!content) return;

            try {
                const response = await fetch(`/salons/${salonId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ content })
                });

                if (response.ok) {
                    const message = await response.json();
                    appendMessage(message);
                    messageInput.value = '';
                    scrollToBottom();
                }
            } catch (error) {
                console.error('Error sending message:', error);
            }
        });

        function appendMessage(msg) {
            const isOwn = msg.user.id === currentUserId;
            const div = document.createElement('div');
            div.className = `mb-3 ${isOwn ? 'text-right' : ''}`;
            div.innerHTML = `
                <div class="inline-block max-w-xs lg:max-w-md">
                    <div class="text-xs text-gray-500 mb-1 ${isOwn ? 'text-right' : ''}">${msg.user.name}</div>
                    <div class="${isOwn ? 'bg-blue-500 text-white' : 'bg-white'} rounded-lg px-4 py-2 shadow">
                        ${escapeHtml(msg.content)}
                    </div>
                    <div class="text-xs text-gray-400 mt-1">${formatTime(msg.created_at)}</div>
                </div>
            `;
            messagesContainer.appendChild(div);
        }

        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        }
    </script>
    @endif
</x-app-layout>
