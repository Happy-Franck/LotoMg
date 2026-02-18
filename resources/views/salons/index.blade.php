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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @forelse($salons as $salon)
                        <div class="border-b pb-4 mb-4 last:border-b-0">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold">
                                        <a href="{{ route('salons.show', $salon) }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $salon->name }}
                                        </a>
                                    </h3>
                                    <p class="text-gray-600 mt-1">{{ $salon->description }}</p>
                                    <p class="text-sm text-gray-500 mt-2">
                                        Créé par {{ $salon->owner->name }} • {{ $salon->participants->count() }} participant(s)
                                    </p>
                                </div>
                                <div class="ml-4">
                                    @if($salon->participants->contains(auth()->id()))
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Membre</span>
                                    @else
                                        <form action="{{ route('salons.join', $salon) }}" method="POST">
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
                        <p class="text-gray-500">Aucun salon disponible. Créez-en un !</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
