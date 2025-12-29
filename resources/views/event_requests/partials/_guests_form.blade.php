@php($context = $context ?? 'create')
@php($guestsData = old('guests', $guestsData ?? ($context === 'edit' ? ($eventRequest->guests ?? []) : [])))
<div class="card mb-4" id="guestsCard">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Guests (Optional, up to 4)</span>
        <button type="button" class="btn btn-sm btn-outline-primary" id="addGuestBtn"><i class="bi bi-person-plus"></i> Add Guest</button>
    </div>
    <div class="card-body">
        <div id="guestsContainer" class="row g-3">
            @foreach($guestsData as $idx => $g)
                <div class="col-12 guest-item" data-index="{{ $idx }}">
                    <div class="border rounded p-3 position-relative">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-guest" aria-label="Remove"></button>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Name</label>
                                <input type="text" name="guests[{{ $idx }}][name]" value="{{ $g['name'] ?? '' }}" class="form-control" required>
                                @error("guests.$idx.name")<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Email</label>
                                <input type="email" name="guests[{{ $idx }}][email]" value="{{ $g['email'] ?? '' }}" class="form-control" required>
                                @error("guests.$idx.email")<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Social URL</label>
                                <input type="url" name="guests[{{ $idx }}][social_url]" value="{{ $g['social_url'] ?? '' }}" class="form-control" required>
                                @error("guests.$idx.social_url")<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Ticket Type</label>
                                <select name="guests[{{ $idx }}][ticket_type_id]" class="form-select" required>
                                    <option value="">Type</option>
                                    @foreach($ticketTypes as $tt)
                                        <option value="{{ $tt->id }}" {{ (int)($g['ticket_type_id'] ?? 0) === $tt->id ? 'selected' : '' }}>{{ $tt->name }}</option>
                                    @endforeach
                                </select>
                                @error("guests.$idx.ticket_type_id")<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <template id="guestTemplate">
            <div class="col-12 guest-item" data-index="__INDEX__">
                <div class="border rounded p-3 position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-guest" aria-label="Remove"></button>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Name</label>
                            <input type="text" name="guests[__INDEX__][name]" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Email</label>
                            <input type="email" name="guests[__INDEX__][email]" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Social URL</label>
                            <input type="url" name="guests[__INDEX__][social_url]" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Ticket Type</label>
                            <select name="guests[__INDEX__][ticket_type_id]" class="form-select" required>
                                <option value="">Type</option>
                                @foreach($ticketTypes as $tt)
                                    <option value="{{ $tt->id }}">{{ $tt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <div class="small text-muted mt-3">You can add up to 4 guests.</div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    const addBtn = document.getElementById('addGuestBtn');
    if(!addBtn) return;
    const container = document.getElementById('guestsContainer');
    const template = document.getElementById('guestTemplate').innerHTML;
    function currentCount(){ return container.querySelectorAll('.guest-item').length; }
    function nextIndex(){ let max=-1; container.querySelectorAll('.guest-item').forEach(el=>{ const i=parseInt(el.dataset.index,10); if(i>max) max=i;}); return max+1; }
    function addGuest(){ if(currentCount()>=4) return; const idx = nextIndex(); const html = template.replace(/__INDEX__/g, idx); const wrapper=document.createElement('div'); wrapper.innerHTML=html.trim(); container.appendChild(wrapper.firstChild); }
    container.addEventListener('click', e=>{ if(e.target.classList.contains('remove-guest')) { e.preventDefault(); const item=e.target.closest('.guest-item'); if(item) item.remove(); }});
    addBtn.addEventListener('click', ()=> addGuest());
})();
</script>
@endpush
