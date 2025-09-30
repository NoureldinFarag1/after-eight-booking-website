@extends('layouts.app')

@section('title', 'Book Event')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-ticket-perforated me-2"></i>Book Event: {{ $event->title }}
                </h4>
            </div>
            <div class="card-body">
                <!-- Event Summary -->
                <div class="row mb-4">
                    @if($event->image_url)
                        <div class="col-md-4">
                            <img src="{{ Storage::url($event->image_url) }}"
                                 alt="{{ $event->title }}"
                                 class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                    @else
                        <div class="col-12">
                    @endif
                        <h5>{{ $event->title }}</h5>
                        <p class="text-muted">{{ Str::limit($event->description, 150) }}</p>

                        <div class="row">
                            <div class="col-sm-6">
                                <p class="mb-1">
                                    <i class="bi bi-calendar text-primary me-1"></i>
                                    {{ $event->event_date->format('l, F j, Y') }}
                                </p>
                                <p class="mb-1">
                                    <i class="bi bi-clock text-primary me-1"></i>
                                    {{ $event->event_time->format('g:i A') }}
                                </p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-1">
                                    <i class="bi bi-geo-alt text-primary me-1"></i>
                                    {{ $event->location }}
                                </p>
                                <p class="mb-1">
                                    <i class="bi bi-people text-primary me-1"></i>
                                    @if(auth()->user()->isAdmin())
                                        {{ $event->getAvailableSeatsAttribute() }} seats available
                                    @else
                                        Registration Available
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                @if(auth()->user()->isAdmin())
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Administrators are not allowed to create bookings.
                    </div>
                @else
                <!-- Booking Form -->
                <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $event->id }}">

                    <div class="row">
                        @if(isset($types) && $types->count() > 0)
                            <div class="col-md-6 mb-3">
                                <label for="ticket_type_id" class="form-label">Ticket Type *</label>
                                <select class="form-select @error('ticket_type_id') is-invalid @enderror"
                                        id="ticket_type_id"
                                        name="ticket_type_id"
                                        required>
                                    <option value="">Select type</option>
                                    @foreach($types as $t)
                                        <option value="{{ $t->id }}" data-price="{{ $t->price }}" {{ old('ticket_type_id') == $t->id ? 'selected' : '' }}>
                                            {{ $t->name }} — EGP {{ number_format($t->price, 2) }}
                                            @if(auth()->user()->isAdmin() && !is_null($t->capacity)) (cap: {{ $t->capacity }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('ticket_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Number of Tickets *</label>
                            <select class="form-select @error('quantity') is-invalid @enderror"
                                    id="quantity"
                                    name="quantity"
                                    required>
                                <option value="">Select quantity</option>
                                @php
                                    $maxTickets = auth()->user()->isAdmin() ? min(10, $event->getAvailableSeatsAttribute()) : 10;
                                @endphp
                                @for($i = 1; $i <= $maxTickets; $i++)
                                    <option value="{{ $i }}" {{ old('quantity') == $i ? 'selected' : '' }}>
                                        {{ $i }} ticket{{ $i > 1 ? 's' : '' }}
                                    </option>
                                @endfor
                            </select>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price per Ticket</label>
                            <div class="form-control-plaintext h5 text-primary mb-0">
                                <span id="unit-price">
                                    @if(isset($types) && $types->count() > 0)
                                        Select a ticket type
                                    @else
                                        Pricing will be announced
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Order Summary</h6>
                            <div class="d-flex justify-content-between">
                                <span>Tickets (<span id="summary-quantity">0</span>):</span>
                                <span id="summary-subtotal">EGP 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Service Fee:</span>
                                <span>EGP 0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold h5">
                                <span>Total:</span>
                                <span class="text-primary" id="summary-total">EGP 0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="mb-4">
                        <h6>Customer Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> {{ auth()->user()->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    @if($event->terms_conditions)
                        <div class="mb-4">
                            <h6>Terms & Conditions</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <p class="mb-0 small">{{ $event->terms_conditions }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                        <label class="form-check-label" for="agreeTerms">
                            I agree to the event terms and conditions and understand that tickets are non-refundable
                            except as required by law.
                        </label>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('events.show', $event) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Back to Event
                        </a>

                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="bi bi-credit-card me-1"></i>Complete Booking
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantitySelect = document.getElementById('quantity');
        const agreeCheckbox = document.getElementById('agreeTerms');
        const submitBtn = document.getElementById('submitBtn');
        const summaryQuantity = document.getElementById('summary-quantity');
        const summarySubtotal = document.getElementById('summary-subtotal');
        const summaryTotal = document.getElementById('summary-total');

    const hasTypes = {{ isset($types) && $types->count() > 0 ? 'true' : 'false' }};
    const typeSelect = document.getElementById('ticket_type_id');
    let pricePerTicket = hasTypes ? parseFloat(typeSelect?.selectedOptions[0]?.dataset.price || 0) : 0;

        function updateSummary() {
            const quantity = parseInt(quantitySelect.value) || 0;
            const subtotal = quantity * pricePerTicket;

            summaryQuantity.textContent = quantity;

            const unitPriceEl = document.getElementById('unit-price');
            if (unitPriceEl) {
                unitPriceEl.textContent = pricePerTicket > 0 ? 'EGP ' + pricePerTicket.toFixed(2) : 'Free';
            }

            if (pricePerTicket > 0 && quantity > 0) {
                summarySubtotal.textContent = 'EGP ' + subtotal.toFixed(2);
                summaryTotal.textContent = 'EGP ' + subtotal.toFixed(2);
            } else {
                summarySubtotal.textContent = quantity > 0 ? 'EGP 0.00' : 'EGP 0.00';
                summaryTotal.textContent = quantity > 0 ? 'EGP 0.00' : 'EGP 0.00';
            }
        }

        function updateSubmitButton() {
            const hasQuantity = quantitySelect.value !== '';
            const hasAgreed = agreeCheckbox.checked;
            const hasTypeSelection = !hasTypes || (typeSelect && typeSelect.value !== '');

            submitBtn.disabled = !(hasQuantity && hasAgreed && hasTypeSelection);
        }

        quantitySelect.addEventListener('change', function() {
            updateSummary();
            updateSubmitButton();
        });

        if (hasTypes && typeSelect) {
            typeSelect.addEventListener('change', function() {
                pricePerTicket = parseFloat(this.selectedOptions[0].dataset.price || 0);
                updateSummary();
                updateSubmitButton();
            });
        }

        agreeCheckbox.addEventListener('change', updateSubmitButton);

        // Initial update
        updateSummary();
        updateSubmitButton();
    });
</script>
@endsection
