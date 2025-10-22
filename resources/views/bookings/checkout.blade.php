@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-3">
            <h4>Payment Method</h4>
            <form action="{{ route('bookings.store') }}" method="POST" id="checkoutForm">
                @csrf
                <input type="hidden" name="event_id" value="{{ $event->id }}">
                <input type="hidden" name="quantity" value="{{ $quantity }}">
                @if($ticketType) <input type="hidden" name="ticket_type_id" value="{{ $ticketType->id }}"> @endif

                <div class="mb-3">
                    <label class="form-label"><strong>Ticket</strong></label>
                    <div class="card p-2">
                        <div>{{ $ticketLabel }}</div>
                        <div>Unit Price: EGP {{ number_format($unitBase,2) }}</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label"><strong>Cart Contains</strong></label>
                    <ul>
                        <li>{{ $ticketLabel }} - EGP {{ number_format($subtotal,2) }}</li>
                        <li>Handling Fees - EGP {{ number_format($handlingFee,2) }}</li>
                        <li id="whatsappLine" style="display:none">WhatsApp Fees - EGP 25.00</li>
                    </ul>
                </div>

                <div class="mb-3">
                    <input type="checkbox" id="whatsapp" name="whatsapp" value="1">
                    <label for="whatsapp">Add WhatsApp Fees (EGP 25)</label>
                </div>

                <div class="mb-3">
                    <strong>Total</strong>
                    <div id="totalDisplay" class="badge bg-secondary p-2">EGP {{ number_format($total,2) }}</div>
                </div>

                <div class="mb-3">
                    <button class="btn btn-danger w-100" type="submit">Confirm & Pay</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function(){
    const whatsapp = document.getElementById('whatsapp');
    const totalDisplay = document.getElementById('totalDisplay');
    const whatsappLine = document.getElementById('whatsappLine');
    const baseTotal = parseFloat({{ $total }});
    whatsapp.addEventListener('change', function(){
        let total = baseTotal;
        if(this.checked){
            total += 25;
            whatsappLine.style.display='list-item';
        }else{
            whatsappLine.style.display='none';
        }
        totalDisplay.textContent = 'EGP ' + total.toFixed(2);
    });
})();
</script>
@endsection
