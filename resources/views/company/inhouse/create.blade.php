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

            {{-- What the interview is for.

                 A schedule request carries no job posting, so this is the
                 only thing that tells PESO whom to call in. Without it the
                 desk sees a day, a time and a number of applicants, and no
                 way to know which jobseekers to invite. --}}
            <div class="mb-3">
                <label class="peso-label">
                    Job Positions <span class="text-danger">*</span>
                </label>
                <div style="font-size:11.5px;color:var(--n-500);margin-bottom:6px;">
                    The positions you will be interviewing for. PESO uses these to
                    decide which jobseekers to invite.
                </div>

                <div id="positionRows">
                    @php $oldPositions = old('job_positions', ['']); @endphp
                    @foreach($oldPositions as $position)
                        <div class="d-flex gap-2 mb-2 position-row">
                            <input type="text" name="job_positions[]" class="form-control peso-input"
                                placeholder="e.g. Staff Nurse" maxlength="120"
                                value="{{ $position }}" {{ $loop->first ? 'required' : '' }}>
                            <button type="button" class="btn btn-sm peso-remove-position"
                                style="border:1px solid var(--n-200);background:#fff;color:var(--danger);border-radius:8px;padding:6px 12px;"
                                aria-label="Remove this position">
                                <i class="ph ph-x"></i>
                            </button>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="addPositionRow" class="btn btn-sm"
                    style="border:1px dashed var(--n-300);background:#fff;color:var(--g-700);border-radius:8px;padding:6px 14px;font-size:12px;">
                    <i class="ph ph-plus me-1"></i>Add another position
                </button>

                @if(!empty($openTitles) && count($openTitles))
                    <div class="mt-3">
                        <div style="font-size:11.5px;color:var(--n-500);margin-bottom:6px;">
                            From your open postings — tap to fill:
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @foreach($openTitles as $title)
                                <button type="button" class="btn btn-sm peso-title-chip"
                                    data-title="{{ $title }}"
                                    style="border:1px solid var(--g-300,#bcd9c0);background:var(--g-50,#f2f8f3);color:var(--g-700);border-radius:999px;padding:5px 12px;font-size:12px;">
                                    {{ $title }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                @error('job_positions')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                @error('job_positions.*')
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
    // ── The positions list ──
    // One row to start with, a button for more, and the titles of the
    // employer's own open postings as one-tap fills. The first row keeps the
    // `required` mark so the browser asks before the server has to.
    (function () {
        const rows = document.getElementById('positionRows');
        const add  = document.getElementById('addPositionRow');
        if (!rows || !add) return;

        function newRow(value) {
            const row = document.createElement('div');
            row.className = 'd-flex gap-2 mb-2 position-row';
            row.innerHTML = `
                <input type="text" name="job_positions[]" class="form-control peso-input"
                    placeholder="e.g. Staff Nurse" maxlength="120">
                <button type="button" class="btn btn-sm peso-remove-position"
                    style="border:1px solid var(--n-200);background:#fff;color:var(--danger);border-radius:8px;padding:6px 12px;"
                    aria-label="Remove this position">
                    <i class="ph ph-x"></i>
                </button>`;
            if (value) row.querySelector('input').value = value;
            rows.appendChild(row);
            return row;
        }

        // The first row is the one that must be filled; the rest may be left
        // blank and are dropped on save.
        function markFirst() {
            rows.querySelectorAll('input[name="job_positions[]"]').forEach((input, i) => {
                input.required = i === 0;
            });
        }

        add.addEventListener('click', () => {
            newRow('').querySelector('input').focus();
            markFirst();
        });

        rows.addEventListener('click', (e) => {
            const button = e.target.closest('.peso-remove-position');
            if (!button) return;

            // Never leave the list with no row at all.
            if (rows.querySelectorAll('.position-row').length === 1) {
                rows.querySelector('input').value = '';
            } else {
                button.closest('.position-row').remove();
            }
            markFirst();
        });

        // Tapping a posting title fills the first empty row, or adds one.
        document.querySelectorAll('.peso-title-chip').forEach((chip) => {
            chip.addEventListener('click', () => {
                const title = chip.dataset.title || '';
                const taken = Array.from(rows.querySelectorAll('input[name="job_positions[]"]'))
                    .some((input) => input.value.trim().toLowerCase() === title.toLowerCase());
                if (taken) return;

                const empty = Array.from(rows.querySelectorAll('input[name="job_positions[]"]'))
                    .find((input) => !input.value.trim());

                if (empty) {
                    empty.value = title;
                } else {
                    newRow(title);
                }
                markFirst();
            });
        });

        markFirst();
    })();

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