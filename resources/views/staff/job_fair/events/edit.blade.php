@extends('staff.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-calendar-check-fill me-2" style="color:#4dd9c0;"></i>Edit Job Fair Event
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">Update job fair schedule and venue</p>
</div>

<div class="card border-0 shadow-sm rounded-3" style="max-width:600px;">
    <div class="card-body p-4">
        <form action="{{ route('staff.jobfair.events.update', $event->job_fair_events_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Event Title <span class="text-danger">*</span>
                </label>
                <input type="text" name="title" class="form-control"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                    value="{{ old('title', $event->title) }}" required>
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
                    value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" required>
                @error('event_date')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Venue <span class="text-danger">*</span>
                </label>
                <input type="text" name="venue" class="form-control"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                    value="{{ old('venue', $event->venue) }}" required>
                @error('venue')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Status <span class="text-danger">*</span>
                </label>
                <select name="status" class="form-select"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;">
                    @foreach(['upcoming' => 'Upcoming', 'ongoing' => 'Ongoing', 'completed' => 'Completed'] as $val => $label)
                    <option value="{{ $val }}" {{ old('status', $event->status) === $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn fw-semibold"
                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                           color:#fff;border:none;border-radius:10px;
                           padding:10px 24px;font-size:14px;">
                    <i class="bi bi-check-circle-fill me-2"></i>Update Event
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