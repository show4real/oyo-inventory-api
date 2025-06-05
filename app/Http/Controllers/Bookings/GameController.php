<?php

namespace App\Http\Controllers\Bookings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Game;

class GameController extends Controller
{
    public function index()
    {
        return Game::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
        ]);

        $game = Game::create([
            'name' => $request->name,
            'price' => $request->price,
            'user_id' => auth()->id(),
        ]);

        return response()->json($game, 201);
    }

    public function update(Request $request, Game $game)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
        ]);

        $game->update($request->only('name', 'price'));

        return response()->json($game);
    }

    public function destroy(Game $game)
    {
        $game->delete();
        return response()->json(['message' => 'Game deleted']);
    }
}
