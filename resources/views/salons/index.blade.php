<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Salons') }}
            </h2>
            <a href="{{ route('salons.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Créer un salon
            </a>
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" id="salons-list">
                    @forelse($salons as $salon)
                        <div class="border-b pb-4 mb-4 last:border-b-0 salon-item" data-salon-id="{{ $salon->id }}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold">
                                        <a href="{{ route('salons.show', $salon) }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $salon->name }}
                                        </a>
                                        @if($salon->currentGame()->exists())
                                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded ml-2">🔒 Partie en cours</span>
                                        @endif
                                    </h3>
                                    <p class="text-gray-600 mt-1 salon-description">{{ $salon->description }}</p>
                                    <p class="text-sm text-gray-500 mt-2 salon-info">
                                        Créé par <span class="salon-owner">{{ $salon->owner->name }}</span> • <span class="salon-participants">{{ $salon->participants->count() }}</span> participant(s)
                                    </p>
                                </div>
                                <div class="ml-4 salon-actions">
                                    @if($salon->currentGame()->exists())
                                        <span class="bg-gray-300 text-gray-600 text-xs px-2 py-1 rounded cursor-not-allowed">Verrouillé</span>
                                    @elseif($salon->participants->contains(auth()->id()))
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Membre</span>
                                    @else
                                        <form action="{{ route('salons.join', $salon) }}" method="POST" class="join-form">
                                            @csrf
                                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded">
                                                Rejoindre
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500" id="no-salons-message">Aucun salon disponible. Créez-en un !</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script type="module">
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
            const currentUserId = {{ auth()->id() }};

            Echo.channel('salons')
                .listen('SalonCreated', (e) => {
                    console.log('✨ SalonCreated:', e);
                    addSalon(e.salon);
                })
                .listen('SalonUpdated', (e) => {
                    console.log('🔄 SalonUpdated:', e);
                    updateSalon(e.salon);
                })
                .listen('SalonDeleted', (e) => {
                    console.log('🗑️ SalonDeleted:', e);
                    removeSalon(e.salon_id);
                });

            function addSalon(salon) {
                const noSalonsMsg = document.getElementById('no-salons-message');
                if (noSalonsMsg) {
                    noSalonsMsg.remove();
                }

                const salonsList = document.getElementById('salons-list');
                const salonDiv = createSalonElement(salon);
                salonsList.insertAdjacentHTML('afterbegin', salonDiv);
            }

            function updateSalon(salon) {
                const salonElement = document.querySelector(`[data-salon-id="${salon.id}"]`);
                if (salonElement) {
                    const newElement = createSalonElement(salon);
                    salonElement.outerHTML = newElement;
                }
            }

            function removeSalon(salonId) {
                const salonElement = document.querySelector(`[data-salon-id="${salonId}"]`);
                if (salonElement) {
                    salonElement.remove();
                }

                const remainingSalons = document.querySelectorAll('.salon-item');
                if (remainingSalons.length === 0) {
                    document.getElementById('salons-list').innerHTML = '<p class="text-gray-500" id="no-salons-message">Aucun salon disponible. Créez-en un !</p>';
                }
            }

            function createSalonElement(salon) {
                const isLocked = salon.has_active_game;
                const isMember = false; // On ne peut pas savoir côté client facilement

                return `
                    <div class="border-b pb-4 mb-4 last:border-b-0 salon-item" data-salon-id="${salon.id}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold">
                                    <a href="/salons/${salon.id}" class="text-blue-600 hover:text-blue-800">
                                        ${salon.name}
                                    </a>
                                    ${isLocked ? '<span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded ml-2">🔒 Partie en cours</span>' : ''}
                                </h3>
                                <p class="text-gray-600 mt-1 salon-description">${salon.description || ''}</p>
                                <p class="text-sm text-gray-500 mt-2 salon-info">
                                    Créé par <span class="salon-owner">${salon.owner.name}</span> • <span class="salon-participants">${salon.participants_count}</span> participant(s)
                                </p>
                            </div>
                            <div class="ml-4 salon-actions">
                                ${isLocked ? 
                                    '<span class="bg-gray-300 text-gray-600 text-xs px-2 py-1 rounded cursor-not-allowed">Verrouillé</span>' :
                                    `<form action="/salons/${salon.id}/join" method="POST" class="join-form">
                                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded">
                                            Rejoindre
                                        </button>
                                    </form>`
                                }
                            </div>
                        </div>
                    </div>
                `;
            }
        });
    </script>
</x-app-layout>
