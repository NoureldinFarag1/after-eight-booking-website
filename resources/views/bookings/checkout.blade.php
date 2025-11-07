@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-3">
            <h4 class="mb-3">Review & Payment</h4>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>There were problems with your submission:</strong>
                    <ul class="mb-0 small">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
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

                <div class="mb-3 form-check">
                    <input type="checkbox" id="whatsapp" name="whatsapp" value="1" class="form-check-input" @checked($whatsappSelected)>
                    <label for="whatsapp" class="form-check-label">Add WhatsApp service notification (EGP 25)</label>
                </div>

                <div class="mb-3">
                    <strong>Total</strong>
                    <div id="totalDisplay" class="badge bg-secondary p-2">EGP {{ number_format($total,2) }}</div>
                </div>

                <div class="mb-3">
                    <button class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2" type="submit" id="confirmBtn">
                        <i class="bi bi-credit-card"></i>
                        <span id="confirmBtnText">Confirm & Pay</span>
                        <span id="confirmSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="text-center">
                    <a href="{{ route('events.show', $event) }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Back to event</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(()=>{
    const whatsapp = document.getElementById('whatsapp');
    const totalDisplay = document.getElementById('totalDisplay');
    const whatsappLine = document.getElementById('whatsappLine');
    const confirmBtn = document.getElementById('confirmBtn');
    const confirmSpinner = document.getElementById('confirmSpinner');
    const confirmBtnText = document.getElementById('confirmBtnText');
    const baseTotal = parseFloat({{ $totalBeforeWhatsapp ?? $total }});

    function recalc(){
        let total = baseTotal;
        if(whatsapp.checked){
            total += 25;
            whatsappLine.style.display='list-item';
        } else {
            whatsappLine.style.display='none';
        }
        totalDisplay.textContent = 'EGP ' + total.toFixed(2);
    }
    whatsapp.addEventListener('change', recalc);
    recalc();

    document.getElementById('checkoutForm').addEventListener('submit', function(){
        confirmBtn.disabled = true;
        confirmSpinner.classList.remove('d-none');
        confirmBtnText.textContent = 'Processing...';
    });
})();
</script>
@endsection
