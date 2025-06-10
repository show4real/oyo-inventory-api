<?php

namespace App\Http\Controllers\Bookings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Game;
use App\Booking;
use App\BookingTransaction;
use App\User;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'bookings' => 'required|array|min:1',
            'bookings.*.id' => 'required|exists:games,id',
            'bookings.*.quantity' => 'required|integer|min:1',
            //'bookings.*.price' => 'required|integer|min:0',
        ]);

        $sessionId = time();
        $cashierId = auth()->user()->id;
        $playedAt = now();

        $createdBookings = [];
        $totalSales = 0;

        foreach ($request->bookings as $booking) {
            $totalPrice = $booking['price'] * $booking['quantity'];
            $totalSales += $totalPrice;

            $createdBookings[] = Booking::create([
                'session_id' => $sessionId,
                'game_id' => $booking['id'],
                'rounds' => $booking['quantity'],
                'price_per_round' => $booking['price'],
                'total_price' => $totalPrice,
                'played_at' => $playedAt,
                'cashier_id' => $cashierId,
            ]);
        }

       
        $transaction = BookingTransaction::create([
            'session_id' => $sessionId,
            'total_price' => $totalSales,
            'cashier_id' => $cashierId,
            'payment_mode' => $request->payment_mode
        ]);

        return response()->json([
            'session_id' => $sessionId,
            'bookings' => $createdBookings,
            'transaction' => $transaction,
        ], 201);
    }

    public function getBooking(Request $request, $id){
        
        $transaction = BookingTransaction::where('id', $id)->first();

        $bookings = Booking::where('session_id', $transaction->session_id)->get();

        return response()->json(compact('transaction', 'bookings'));

    }



    public function index(Request $request)
    {
        $query = BookingTransaction::latest();

        if ($request->filled('fromdate') && $request->filled('todate')) {
            $query->dateBetween($request->fromdate, $request->todate);
        }

        if ($request->filled('cashier_id')) {
            $query->soldBy($request->cashier_id);
        }

        
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $totalSales = $query->sum('total_price');

        $perPage = $request->input('rows', 10);
        $bookings = $query->paginate($perPage);

        $cashiers = User::where('organization_id', 0)
            ->select('id', 'firstname', 'lastname')
            ->get();

        return response()->json([
            'bookings' => $bookings,
            'total_sales' => $totalSales,
            'cashiers' => $cashiers
        ]);
    }

    public function destroy($id)
    {
        $bookingTransaction = BookingTransaction::where('session_id', $id)->first();

        if (!$bookingTransaction) {
            return response()->json(['message' => 'Booking transaction not found'], 404);
        }

        $bookings = Booking::where('session_id', $bookingTransaction->session_id)->get();

        foreach ($bookings as $booking) {
            $booking->delete();
        }

        $bookingTransaction->delete();

        return response()->json(['message' => 'Booking deleted']);
    }


    public function dashboard(){

        $user_count = User::where('organization_id', 0)->count();
        $game_count = Game::count();
        $total_sales = BookingTransaction::sum('total_price');


        return response()->json(compact('user_count','game_count','total_sales'));


    }


}
