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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-2">Description</h3>
                    <p class="text-gray-600">{{ $salon->description ?? 'Aucune description' }}</p>
                    <p class="text-sm text-gray-500 mt-4">Créé par {{ $salon->owner->name }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Participants ({{ $salon->participants->count() }})</h3>
                        @if($salon->participants->contains(auth()->id()))
                            @if($salon->user_id !== auth()->id())
                                <form action="{{ route('salons.leave', $salon) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white text-sm px-3 py-1 rounded">
                                        Quitter le salon
                                    </button>
                                </form>
                            @endif
                        @else
                            <form action="{{ route('salons.join', $salon) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded">
                                    Rejoindre le salon
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
</x-app-layout>
