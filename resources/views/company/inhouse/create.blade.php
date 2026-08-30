@extends('company.layouts.app')

@section('page-title', 'Request In-house Schedule')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-calendar-plus me-2" style="color:var(--g-600);"></i>Request In-house Schedule
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">Fill up the form to request an in-house interview at PESO</p>
</div>

<div style="max-width:650px;">
    @include('partials.overseas-inhouse-notice')
</div>

<div class="card border-0 shadow-sm rounded-3" style="max-width:650px;">
    <div class="card-body p-4">
        <form action="{{ route('company.inhouse.store') }}" method="POST">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="peso-label">
                        Available From <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="preferred_date" class="form-control peso-input"
                        min="{{ \App\Support\OfficeCalendar::earliestBookableDate() }}"
                        value="{{ old('preferred_date') }}" required>
                    @error('preferred_date')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="peso-label">Available Until</label>
                    <input type="date" name="preferred_date_end" class="form-control peso-input"
                        min="{{ \App\Support\OfficeCalendar::earliestBookableDate() }}"
                        value="{{ old('preferred_date_end') }}">
                    <div style="font-size:11px;color:var(--n-500);margin-top:4px;">
                        Leave blank if you can only make one day. Every day in the range is held
                        for you, and you choose which one to interview on.
                    </div>
                    @error('preferred_date_end')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="peso-label">
                        Preferred Time <span class="text-danger">*</span>
                    </label>
                    <input type="time" name="preferred_time" class="form-control peso-input"
                        value="{{ old('preferred_time') }}" required>
                    @error('preferred_time')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="peso-label">
                    Number of Applicants <span class="text-danger">*</span>
                </label>
                <input type="number" name="num_applicants" class="form-control peso-input"
                    placeholder="e.g. 10" min="1"
                    value="{{ old('num_applicants') }}" required>
                @error('num_applicants')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="peso-label">
                    Venue <span class="text-danger">*</span>
                </label>
                <div class="d-flex gap-3 mb-2 flex-wrap">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="venue_type" id="venuePeso"
                            value="peso_office"
                            {{ old('venue_type', 'peso_office') == 'peso_office' ? 'checked' : '' }}
                            onchange="toggleVenueAddress()">
                        <label class="form-check-label" for="venuePeso" style="font-size:13px;color:var(--n-700);">
                            PESO Office
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="venue_type" id="venueCompanyOffice"
                            value="company_office"
                            {{ old('venue_type') == 'company_office' ? 'checked' : '' }}
                            onchange="toggleVenueAddress()">
                        <label class="form-check-label" for="venueCompanyOffice" style="font-size:13px;color:var(--n-700);">
                            Company Office
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="venue_type" id="venueCustom"
                            value="custom"
                            {{ old('venue_type') == 'custom' ? 'checked' : '' }}
                            onchange="toggleVenueAddress()">
                        <label class="form-check-label" for="venueCustom" style="font-size:13px;color:var(--n-700);">
                            Other Venue (Mall, Restaurant, etc.)
                        </label>
                    </div>
                </div>
                <div id="venueAddressWrapper" style="{{ old('venue_type') == 'custom' ? '' : 'display:none;' }}">
                    <input type="text" name="venue_address" class="form-control peso-input"
                        placeholder="e.g. SM CDO Downtown Premier - Function Room 2"
                        value="{{ old('venue_address') }}">
                </div>
                @error('venue_type')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                @error('venue_address')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="peso-label">Notes</label>
                <textarea name="notes" class="form-control peso-input" rows="3"
                    placeholder="Any additional notes or requirements...">{{ old('notes') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-peso fw-semibold">
                    <i class="ph-fill ph-paper-plane-tilt me-2"></i>Submit Request
                </button>
                <a href="{{ route('company.inhouse') }}"
                   class="btn btn-peso-outline fw-semibold">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
    function toggleVenueAddress() {
        const isCustom = document.getElementById('venueCustom').checked;
        const wrapper = document.getElementById('venueAddressWrapper');
        const input = wrapper.querySelector('input');
        wrapper.style.display = isCustom ? 'block' : 'none';
        input.required = isCustom;
        if (!isCustom) input.value = '';
    }
</script>
@endsection

@endsection