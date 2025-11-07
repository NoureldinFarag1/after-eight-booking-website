@extends('layouts.app')

@section('title', 'Create Event')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-plus-circle me-2"></i>Create New Event
                </h4>
            </div>
            <div class="card-body">
                <form id="create-event-form" action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="title" class="form-label">Event Title *</label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status"
                                    name="status"
                                    required>
                                <option value="">Select Status</option>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="10" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">You can format text, add links, headings and images.</div>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Location *</label>
                        <input type="text"
                               class="form-control @error('location') is-invalid @enderror"
                               id="location"
                               name="location"
                               value="{{ old('location') }}"
                               required>
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="google_maps_url" class="form-label">Google Maps Link *</label>
               <input type="url"
                               class="form-control @error('google_maps_url') is-invalid @enderror"
                               id="google_maps_url"
                               name="google_maps_url"
                               placeholder="https://maps.app.goo.gl/... or https://www.google.com/maps/..."
                   value="{{ old('google_maps_url') }}" required>
                        @error('google_maps_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="map-preview-toggle" class="mt-2 d-none">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="show-map-btn">Show Map Preview</button>
                        </div>
                        <div id="map-preview-wrapper" class="mt-2 d-none">
                            <div class="ratio ratio-16x9 border rounded">
                                <iframe id="map-preview-iframe" src="" style="border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="event_date" class="form-label">Event Date *</label>
                            <input type="date"
                                   class="form-control @error('event_date') is-invalid @enderror"
                                   id="event_date"
                                   name="event_date"
                                   value="{{ old('event_date') }}"
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            @error('event_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="event_time" class="form-label">Event Time *</label>
                            <input type="time"
                                   class="form-control @error('event_time') is-invalid @enderror"
                                   id="event_time"
                                   name="event_time"
                                   value="{{ old('event_time') }}"
                                   required>
                            @error('event_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="capacity" class="form-label">Capacity *</label>
                            <input type="number"
                                   class="form-control @error('capacity') is-invalid @enderror"
                                   id="capacity"
                                   name="capacity"
                                   value="{{ old('capacity') }}"
                                   min="1"
                                   required>
                            @error('capacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                    <div class="col-md-6 mb-3">
                        <label for="type">Event Type</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="booking">Booking</option>
                            <option value="request">Request</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Event Image</label>
                        <input type="file"
                               class="form-control @error('image') is-invalid @enderror"
                               id="image"
                               name="image"
                               accept="image/*">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Recommended size: 800x600 pixels. Max file size: 2MB</div>
                    </div>

                    <div class="mb-3">
                        <label for="layout_image" class="form-label">Venue Layout (required)</label>
                        <input type="file"
                               class="form-control @error('layout_image') is-invalid @enderror"
                               id="layout_image"
                               name="layout_image"
                               accept="image/*" required>
                        @error('layout_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Upload a layout image. It will be shown on the event page and opens in a popup.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assign Existing Artists</label>
                        <select name="artist_ids[]" class="form-select" multiple>
                            @foreach($artists as $artist)
                                <option value="{{ $artist->id }}">{{ $artist->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold Cmd/Ctrl to select multiple artists.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            Add New Artists
                            <a href="{{ route('admin.artists.create') }}" id="add-artist-btn" class="btn btn-sm btn-outline-primary">Add Artist</a>
                        </label>
                        <div id="artists-new-list"></div>
                        <div class="form-text">Each artist needs a name and optional picture.</div>
                        @error('artists_new.*.name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @error('artists_new.*.photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="terms_conditions" class="form-label">Terms & Conditions</label>
                        <textarea class="form-control @error('terms_conditions') is-invalid @enderror"
                                  id="terms_conditions"
                                  name="terms_conditions"
                                  rows="3">{{ old('terms_conditions') }}</textarea>
                        @error('terms_conditions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Fees removed from creation; managed later with ticket management --}}

                    <div class="mb-3">
                        <label class="form-label">Finance Officer</label>
                        <select name="finance_officer_id" class="form-control">
                            <option value="">-- None --</option>
                            @foreach($financeOfficers as $officer)
                                <option value="{{ $officer->id }}" {{ old('finance_officer_id', isset($event) ? $event->finance_officer_id : '') == $officer->id ? 'selected' : '' }}>
                                    {{ $officer->name }} ({{ $officer->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assign Operators</label>
                        <select name="operators[]" class="form-control" multiple>
                            @foreach($operators as $operator)
                                <option value="{{ $operator->id }}">{{ $operator->name }} ({{ $operator->email }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">Select one or more operators to assign to this event.</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('events.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Events
                        </a>

                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Create Event
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.tiny.cloud/1/tn7ylhy4oklqxlnvfyipk51mn4ydwn567wo8heugvrcwkkf0/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // TinyMCE full-featured editor
        tinymce.init({
            selector: '#description',
            menubar: false,
            plugins: 'lists link image table code autoresize',
            toolbar: 'undo redo | styles | bold italic underline | bullist numlist | link image | table | removeformat | code',
            height: 340,
            branding: false,
            convert_urls: false,
            paste_data_images: true,
            image_caption: true,
            setup: function (editor) {
                const save = () => editor.save();
                editor.on('change keyup input undo redo', save);
            },
            file_picker_types: 'image',
            file_picker_callback: function (cb, value, meta) {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
                input.onchange = function () {
                    const file = this.files[0];
                    const reader = new FileReader();
                    reader.onload = function () {
                        cb(reader.result, { title: file.name });
                    };
                    reader.readAsDataURL(file);
                };
                input.click();
            }
        });
        // Final guard to ensure content is synced before HTML5 validation / submit
        const formEl = document.getElementById('create-event-form');
        if (formEl && window.tinymce) {
            formEl.addEventListener('submit', function(){
                try { window.tinymce.triggerSave(); } catch (e) {}
            });
        }

    const typeSelect = document.getElementById('type');
    const container = document.getElementById('ticket-types-container');
        const addBtn = document.getElementById('add-ticket-type');
        const mapInput = document.getElementById('google_maps_url');
        const toggleWrap = document.getElementById('map-preview-toggle');
        const showBtn = document.getElementById('show-map-btn');
        const previewWrap = document.getElementById('map-preview-wrapper');
        const iframe = document.getElementById('map-preview-iframe');
        function extractCoords(url){
            if(!url) return null;
            const patterns = [/@(-?[0-9]{1,3}\.[0-9]+),(-?[0-9]{1,3}\.[0-9]+)/, /[?&]q=(-?[0-9]{1,3}\.[0-9]+),(-?[0-9]{1,3}\.[0-9]+)/, /\/(-?[0-9]{1,3}\.[0-9]+),(-?[0-9]{1,3}\.[0-9]+)(?:\/|$)/];
            for(const p of patterns){
                const m=url.match(p); if(m){const lat=parseFloat(m[1]); const lng=parseFloat(m[2]); if(Math.abs(lat)<=90 && Math.abs(lng)<=180) return {lat,lng};}
            }
            return null;
        }
        function evaluate(){
            const coords = extractCoords(mapInput.value.trim());
            if(coords){
                toggleWrap.classList.remove('d-none');
                iframe.dataset.src = `https://www.google.com/maps?q=${coords.lat},${coords.lng}&z=15&output=embed`;
            } else {
                toggleWrap.classList.add('d-none');
                previewWrap.classList.add('d-none');
                iframe.removeAttribute('src');
            }
        }
        if(mapInput){ mapInput.addEventListener('input', evaluate); mapInput.addEventListener('change', evaluate); evaluate(); }
        if(showBtn){ showBtn.addEventListener('click', ()=>{ if(!iframe.getAttribute('src') && iframe.dataset.src){ iframe.src=iframe.dataset.src; } previewWrap.classList.toggle('d-none'); showBtn.textContent = previewWrap.classList.contains('d-none') ? 'Show Map Preview' : 'Hide Map Preview'; }); }

        function toggleTypes() {
            if (!container) return;
            const isBooking = typeSelect.value === 'booking';
            container.style.display = isBooking ? '' : 'none';
        }
        if (typeSelect) {
            typeSelect.addEventListener('change', toggleTypes);
            toggleTypes();
        }

        if (addBtn) {
            addBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const list = document.getElementById('ticket-types-list');
                const idx = list.children.length;
                const row = document.createElement('div');
                row.className = 'row g-2 align-items-end mb-2';
                row.innerHTML = `
                    <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="ticket_types[${idx}][name]" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Price</label>
                        <input type="number" name="ticket_types[${idx}][price]" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class=\"col-md-2\">
                        <label class=\"form-label\">Fee Type</label>
                        <select name=\"ticket_types[${idx}][fee_type]\" class=\"form-select\">
                            <option value=\"\">None</option>
                            <option value=\"fixed\">Fixed</option>
                            <option value=\"percentage\">%</option>
                        </select>
                    </div>
                    <div class=\"col-md-2\">
                        <label class=\"form-label\">Fee</label>
                        <input type=\"number\" name=\"ticket_types[${idx}][fee_amount]\" class=\"form-control\" min=\"0\" step=\"0.01\" placeholder=\"0\">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Capacity (optional)</label>
                        <input type="number" name="ticket_types[${idx}][capacity]" class="form-control" min="0">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check form-switch">
                            <input type="hidden" name="ticket_types[${idx}][is_active]" value="0">
                            <input class="form-check-input" type="checkbox" name="ticket_types[${idx}][is_active]" value="1" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                    <div class="col-md-1 text-end">
                        <button class="btn btn-outline-danger btn-sm" onclick="this.closest('.row').remove()" title="Remove">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `;
                list.appendChild(row);
            });
        }

        // Artists repeater
        const addArtistBtn = document.getElementById('add-artist-btn');
        const artistsList = document.getElementById('artists-new-list');
        if(addArtistBtn){
            addArtistBtn.addEventListener('click', function(){
                const idx = artistsList.children.length;
                const row = document.createElement('div');
                row.className = 'row g-2 align-items-end mb-2';
                row.innerHTML = `
                    <div class="col-md-5">
                        <label class="form-label">Name</label>
                        <input type="text" name="artists_new[${idx}][name]" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Photo</label>
                        <input type="file" name="artists_new[${idx}][photo]" accept="image/*" class="form-control">
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.row').remove()">Remove</button>
                    </div>`;
                artistsList.appendChild(row);
            });
        }
    });
</script>
@endpush
@endsection

@push('after-content')
<div class="row justify-content-center mt-3" id="ticket-types-container" style="display:none;">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Ticket Types</strong>
                <button id="add-ticket-type" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus"></i> Add Type
                </button>
            </div>
            <div class="card-body">
                <div id="ticket-types-list"></div>
                <div class="form-text text-muted">You can also manage ticket types later from the event page.</div>
            </div>
        </div>
    </div>
    </div>
@endpush
