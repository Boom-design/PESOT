@extends('staff.layouts.app')

@section('content')

@include('partials.staff-activity-tabs')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">
            <i class="ph-fill ph-calendar-dots me-2" style="color:var(--g-600);"></i>
            Job Fair
        </h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">
            Local employers who accepted the invitation to a job fair. Pick the event to see who is on it.
        </p>
    </div>
    <form method="GET" action="{{ route('staff.participants') }}" class="d-flex gap-2">
        <input type="hidden" name="event" value="{{ $event->job_fair_events_id ?? '' }}">
        <input type="text" name="search" class="form-control peso-input"
               placeholder="Search company..." style="width:240px;font-size:12px;"
               value="{{ $search }}">
        <button type="submit" class="btn btn-peso" style="font-size:12px;">
            <i class="ph ph-magnifying-glass me-1"></i> Search
        </button>
    </form>
</div>

{{-- EVENT PICKER — one fair at a time.

     An employer's answer belongs to the fair it was asked about, so the list
     is read against a single event and never as a running total. --}}
<div class="card border-0 shadow-sm rounded-3 mb-3">
    <div class="card-body p-3">
        <div class="d-flex align-items-end gap-2 flex-wrap">
            <div style="min-width:320px;flex:1;max-width:460px;">
                <label class="peso-label">Job Fair Event</label>
                <select id="eventPicker" class="form-select peso-input" style="font-size:12px;">
                    @forelse($events as $option)
                        <option value="{{ $option->job_fair_events_id }}"
                            {{ optional($event)->job_fair_events_id === $option->job_fair_events_id ? 'selected' : '' }}>
                            {{ $option->title }}
                            @if($option->event_date)
                                — {{ \Carbon\Carbon::parse($option->event_date)->format('M d, Y') }}
                            @endif
                        </option>
                    @empty
                        <option value="">No job fair event yet</option>
                    @endforelse
                </select>
            </div>

            @if($event)
            <div style="font-size:12px;color:var(--n-500);line-height:1.7;">
                <i class="ph ph-map-pin me-1" style="color:var(--g-600);"></i>
                {{ $event->venue ?: 'Venue not set' }}
                <span class="mx-2" style="color:var(--n-200);">|</span>
                <i class="ph ph-users-three me-1" style="color:var(--g-600);"></i>
                {{ $participants->count() }} local employer(s) accepted
            </div>
            @endif
        </div>
    </div>
</div>

@php
    $th = 'background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;';
    $td = 'padding:12px 16px;color:var(--n-700);font-size:13px;';

    // The employer's own word on the invitation, in the office's language.
    $statusMap = [
        'pending'      => ['label' => 'Awaiting reply', 'color' => 'var(--warn)'],
        'accepted'     => ['label' => 'Accepted',       'color' => 'var(--g-600)'],
        'confirmed'    => ['label' => 'Confirmed',      'color' => 'var(--g-700)'],
        'declined'     => ['label' => 'Declined',       'color' => 'var(--danger)'],
        'expired'      => ['label' => 'No reply',       'color' => 'var(--n-500)'],
        'not_selected' => ['label' => 'Not selected',   'color' => 'var(--n-500)'],
    ];
@endphp

{{-- The table is drawn whether or not there is anything in it: the columns are
     the answer to what this page holds, and they should not depend on a fair
     having employers on it yet. --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="{{ $th }}">#</th>
                    <th style="{{ $th }}">Company</th>
                    <th style="{{ $th }}">Industry</th>
                    <th style="{{ $th }}">Contact Person</th>
                    <th style="{{ $th }}">Contact Number</th>
                    <th style="{{ $th }}text-align:center;">Vacancies Brought</th>
                    <th style="{{ $th }}text-align:center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($participants as $i => $row)
                @php
                    $company = $row->employer;
                    $badge   = $statusMap[$row->confirmation_status]
                        ?? ['label' => ucfirst($row->confirmation_status ?? 'None'), 'color' => 'var(--n-500)'];
                @endphp
                <tr>
                    <td style="{{ $td }}color:var(--n-500);">{{ $i + 1 }}</td>
                    <td style="{{ $td }}font-weight:600;color:var(--g-700);">
                        {{ $company->company_name ?? 'None' }}
                        <div style="font-size:11px;font-weight:400;color:var(--n-500);">
                            {{ $company->employer->email ?? 'None' }}
                        </div>
                    </td>
                    <td style="{{ $td }}">{{ $company->industry_group ?? 'None' }}</td>
                    <td style="{{ $td }}">{{ $company->contact_person ?? 'None' }}</td>
                    <td style="{{ $td }}">{{ $company->mobile_number ?? 'None' }}</td>
                    <td style="{{ $td }}text-align:center;font-weight:600;color:var(--g-700);">
                        {{ $vacancyCounts[$row->employer_id] ?? 0 }}
                    </td>
                    <td style="{{ $td }}text-align:center;">
                        <span class="fw-semibold" style="color:{{ $badge['color'] }};font-size:12px;">
                            {{ $badge['label'] }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center"
                        style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                        <i class="ph ph-users-three me-1"
                           style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                        @if(!$event)
                            No job fair event has been created yet.
                        @elseif($search)
                            No employer on this fair matches "{{ $search }}".
                        @else
                            No local employer has accepted the invitation to this fair yet.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    // Picking a fair is the whole instruction; there is nothing else to fill in
    // first, so it loads on change rather than waiting for a button.
    document.getElementById('eventPicker')?.addEventListener('change', function () {
        if (!this.value) return;
        const url = new URL(window.location.href);
        url.searchParams.set('event', this.value);
        window.location.href = url.toString();
    });
</script>
@endpush

@endsection
