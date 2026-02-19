<?php

namespace App\Http\Controllers;

use App\Events\SalonCreated;
use App\Events\SalonUpdated;
use App\Events\UserJoinedSalon;
use App\Events\UserLeftSalon;
use App\Models\Salon;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SalonController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $salons = Salon::with('owner', 'participants')->latest()->get();
        return view('salons.index', compact('salons'));
    }

    public function create()
    {
        return view('salons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $salon = auth()->user()->ownedSalons()->create($validated);
        $salon->participants()->attach(auth()->id());
        $salon->load('owner', 'participants');

        broadcast(new SalonCreated($salon));

        return redirect()->route('salons.show', $salon)->with('success', 'Salon créé avec succès !');
    }

    public function show(Salon $salon)
    {
        $salon->load('owner', 'participants');
        return view('salons.show', compact('salon'));
    }

    public function edit(Salon $salon)
    {
        $this->authorize('update', $salon);
        return view('salons.edit', compact('salon'));
    }

    public function update(Request $request, Salon $salon)
    {
        $this->authorize('update', $salon);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $salon->update($validated);
        $salon->load('owner', 'participants');

        broadcast(new SalonUpdated($salon));

        return redirect()->route('salons.show', $salon)->with('success', 'Salon mis à jour avec succès !');
    }

    public function destroy(Salon $salon)
    {
        $this->authorize('delete', $salon);
        $salon->delete();

        return redirect()->route('salons.index')->with('success', 'Salon supprimé avec succès !');
    }

    public function join(Salon $salon)
    {
        // Vérifier si le salon a un jeu actif
        if ($salon->currentGame()->exists()) {
            return redirect()->route('salons.index')->with('error', 'Ce salon a une partie en cours. Vous ne pouvez pas le rejoindre.');
        }

        if (!$salon->participants->contains(auth()->id())) {
            $salon->participants()->attach(auth()->id());
            $salon->refresh();
            $salon->load('owner', 'participants');
            
            \Log::info('User joined salon', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'salon_id' => $salon->id,
                'participants_count' => $salon->participants->count()
            ]);
            
            broadcast(new UserJoinedSalon(auth()->user(), $salon))->toOthers();
            broadcast(new SalonUpdated($salon));
            
            \Log::info('Broadcast sent for UserJoinedSalon');
        }

        return redirect()->route('salons.show', $salon)->with('success', 'Vous avez rejoint le salon !');
    }

    public function leave(Salon $salon)
    {
        if ($salon->user_id !== auth()->id()) {
            $salon->participants()->detach(auth()->id());
            $salon->refresh();
            $salon->load('owner', 'participants');
            
            broadcast(new UserLeftSalon(auth()->user(), $salon))->toOthers();
            broadcast(new SalonUpdated($salon));
            
            return redirect()->route('salons.index')->with('success', 'Vous avez quitté le salon !');
        }

        return redirect()->route('salons.show', $salon)->with('error', 'Le propriétaire ne peut pas quitter son salon !');
    }
}
