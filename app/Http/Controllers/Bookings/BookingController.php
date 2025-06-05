<?php

namespace App\Http\Controllers\Bookings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Game;
use App\Booking;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'rounds' => 'required|integer|min:1',
            'price' => 'required|integer',
        ]);

        $game = Game::findOrFail($request->game_id);


        $booking = Booking::create([
            'session_id' => time(),
            'game_id' => $game->id,
            'rounds' => $request->rounds,
            'price_per_round' => $request->price,
            'total_price' => $request->price * $request->rounds,
            'played_at' => now(),
            'cashier_id' => auth()->user()->id
        ]);

        return response()->json($booking, 201);
    }

    public function index(Request $request)
    {
        $query = Booking::with('game')->latest();

    
        if ($request->filled('from') && $request->filled('to')) {
            $query->dateBetween($request->from, $request->to);
        }

        if ($request->filled('cashier_id')) {
            $query->soldBy($request->cashier_id);
        }

        $totalSales = $query->clone()->sum('total_price');
        $bookings = $query->paginate(10);

        return response()->json([
            'data' => $bookings,
            'total_sales' => $totalSales,
        ]);

        return response()->json($bookings);
    }

    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return response()->json(['message' => 'Booking deleted']);
    }
}
