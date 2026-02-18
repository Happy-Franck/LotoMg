<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Salon;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request, Salon $salon)
    {
        if (!$salon->participants->contains(auth()->id())) {
            return response()->json(['error' => 'Vous devez être membre du salon'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message = $salon->messages()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content'],
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message->load('user'));
    }

    public function index(Salon $salon)
    {
        if (!$salon->participants->contains(auth()->id())) {
            return response()->json(['error' => 'Vous devez être membre du salon'], 403);
        }

        return response()->json(
            $salon->messages()->with('user')->latest()->take(50)->get()->reverse()->values()
        );
    }
}
