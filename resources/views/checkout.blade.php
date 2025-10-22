@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Checkout</h2>
    <div class="card p-4 shadow-sm rounded-3">
        <h5>{{ $ticketType->name ?? 'Ticket' }}</h5>
        <p>Quantity: {{ $quantity }}</p>
        <p>Unit Price: EGP {{ number_format($unitPrice, 2) }}</p>
        <hr>
        <p>Subtotal: EGP <span id="subtotal">{{ number_format($subtotal, 2) }}</span></p>

        <form method="POST" action="{{ route('checkout.purchase') }}">
            @csrf
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="whatsapp" name="whatsapp" value="1" onchange="updateTotal()">
                <label class="form-check-label" for="whatsapp">WhatsApp Service (+EGP 25.00)</label>
            </div>

            <h5>Total: EGP <span id="total">{{ number_format($subtotal, 2) }}</span></h5>

            <input type="hidden" name="ticket_type_id" value="{{ $ticketType->id }}">
            <input type="hidden" name="quantity" value="{{ $quantity }}">
            <button type="submit" class="btn btn-primary mt-3">Buy Now</button>
        </form>
    </div>
</div>

<script>
function updateTotal() {
    const checked = document.getElementById('whatsapp').checked;
    const subtotal = parseFloat({{ $subtotal }});
    const total = checked ? subtotal + 25 : subtotal;
    document.getElementById('total').textContent = total.toFixed(2);
}
</script>
@endsection
