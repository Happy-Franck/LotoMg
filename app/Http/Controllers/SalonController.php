<?php

namespace App\Http\Controllers;

use App\Models\Salon;
use Illuminate\Http\Request;

class SalonController extends Controller
{
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
        if (!$salon->participants->contains(auth()->id())) {
            $salon->participants()->attach(auth()->id());
        }

        return redirect()->route('salons.show', $salon)->with('success', 'Vous avez rejoint le salon !');
    }

    public function leave(Salon $salon)
    {
        if ($salon->user_id !== auth()->id()) {
            $salon->participants()->detach(auth()->id());
            return redirect()->route('salons.index')->with('success', 'Vous avez quitté le salon !');
        }

        return redirect()->route('salons.show', $salon)->with('error', 'Le propriétaire ne peut pas quitter son salon !');
    }
}

