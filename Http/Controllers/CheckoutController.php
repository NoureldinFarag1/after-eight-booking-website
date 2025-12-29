<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        $request->validate([
            'ticket_type_id' => 'required|exists:ticket_types,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $ticketType = TicketType::findOrFail($request->ticket_type_id);
        $quantity = (int) $request->quantity;
        $unitPrice = $ticketType->price;
        $subtotal = $unitPrice * $quantity;

        return view('checkout', compact('ticketType', 'quantity', 'unitPrice', 'subtotal'));
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'ticket_type_id' => 'required|exists:ticket_types,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $ticketType = TicketType::findOrFail($request->ticket_type_id);
        $quantity = (int) $request->quantity;
        $unitPrice = $ticketType->price;
        $subtotal = $unitPrice * $quantity;

        $whatsappFee = $request->has('whatsapp') && $request->whatsapp ? 25.00 : 0.00;
        $total = $subtotal + $whatsappFee;

        DB::transaction(function () use ($ticketType, $quantity, $subtotal, $whatsappFee, $total) {
            Booking::create([
                'user_id' => auth()->id(),
                'event_id' => $ticketType->event_id ?? null,
                'total_amount' => $total,
                'whatsapp_fee' => $whatsappFee,
            ]);
        });
        
        return redirect()->route('events.index')->with('success', 'Booking completed successfully!');
    }
}
