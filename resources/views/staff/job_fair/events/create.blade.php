@extends('staff.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-calendar-plus-fill me-2" style="color:#4dd9c0;"></i>Create Job Fair Event
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">Set a new job fair schedule and venue</p>
</div>

<div class="card border-0 shadow-sm rounded-3" style="max-width:600px;">
    <div class="card-body p-4">
        <form action="{{ route('staff.jobfair.events.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Event Title <span class="text-danger">*</span>
                </label>
                <input type="text" name="title" class="form-control"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                    placeholder="e.g. PESO CDO Job Fair 2026"
                    value="{{ old('title') }}" required>
                @error('title')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Event Date <span class="text-danger">*</span>
                </label>
                <input type="date" name="event_date" class="form-control"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                    value="{{ old('event_date') }}"
                    min="{{ now()->addDays(10)->format('Y-m-d') }}" required>
                <div class="mt-1" style="font-size:11px;color:#888;">
                    <i class="bi bi-info-circle me-1"></i>
                    Event date must be at least 10 days from today
                    (earliest: {{ now()->addDays(10)->format('M d, Y') }})
                </div>
                @error('event_date')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Event Time <span class="text-danger">*</span>
                </label>
                <input type="time" name="event_time" class="form-control"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                    value="{{ old('event_time') }}" min="09:00" max="17:00" required>
                <div class="mt-1" style="font-size:11px;color:#888;">
                    <i class="bi bi-info-circle me-1"></i>
                    Must be between 9:00 AM and 5:00 PM.
                </div>
                @error('event_time')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Number of Employers Needed <span class="text-danger">*</span>
                </label>
                <input type="number" name="employer_capacity" class="form-control" min="1"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                    placeholder="e.g. 20"
                    value="{{ old('employer_capacity') }}" required>
                @error('employer_capacity')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Venue <span class="text-danger">*</span>
                </label>
                <input type="text" name="venue" class="form-control"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                    placeholder="e.g. SM City CDO Event Center"
                    value="{{ old('venue') }}" required>
                @error('venue')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Employers to Invite <span class="text-danger">*</span>
                </label>
                <div class="d-flex gap-3 p-3 rounded-3" style="background:#f0f9f6;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="cater[]" value="local"
                            id="caterLocal" {{ in_array('local', old('cater', ['local', 'overseas'])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="caterLocal" style="font-size:13px;color:#2d7a5f;font-weight:600;">
                            Local Employers
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="cater[]" value="overseas"
                            id="caterOverseas" {{ in_array('overseas', old('cater', ['local', 'overseas'])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="caterOverseas" style="font-size:13px;color:#2d7a5f;font-weight:600;">
                            Overseas Employers
                        </label>
                    </div>
                </div>
                <div class="mt-1" style="font-size:11px;color:#888;">
                    <i class="bi bi-info-circle me-1"></i>
                    Check at least one — only checked employer type(s) will receive the invitation.
                </div>
                @error('cater')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            

            <div class="d-flex gap-2">
                <button type="submit" class="btn fw-semibold"
                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                           color:#fff;border:none;border-radius:10px;
                           padding:10px 24px;font-size:14px;">
                    <i class="bi bi-calendar-check-fill me-2"></i>Create Event
                </button>
                <a href="{{ route('staff.jobfair.events') }}"
                   class="btn fw-semibold"
                   style="border:1.5px solid #a8e6cf;color:#2d7a5f;
                          background:#fff;border-radius:10px;
                          padding:10px 24px;font-size:14px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection