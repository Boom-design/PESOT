{{--
    The form fields for one office schedule entry — shared by the Add modal and
    every Edit modal, so the two can never fall out of step.

    $event is the row being edited, or null when adding.
--}}
<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Title *</label>
        <input type="text" name="title" required maxlength="255"
            class="form-control" style="border-color:var(--n-200);font-size:13px;border-radius:8px;"
            placeholder="e.g. Monthly staff meeting"
            value="{{ old('title', $event->title ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Type *</label>
        <select name="type" required class="form-select"
            style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
            @foreach(\App\Models\OfficeCalendarEvent::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $event->type ?? 'meeting') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Location</label>
        <input type="text" name="location" maxlength="255"
            class="form-control" style="border-color:var(--n-200);font-size:13px;border-radius:8px;"
            placeholder="e.g. PESO Conference Room"
            value="{{ old('location', $event->location ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Start date *</label>
        <input type="date" name="start_date" required
            class="form-control" style="border-color:var(--n-200);font-size:13px;border-radius:8px;"
            value="{{ old('start_date', optional($event->start_date ?? null)->toDateString() ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">End date</label>
        <input type="date" name="end_date"
            class="form-control" style="border-color:var(--n-200);font-size:13px;border-radius:8px;"
            value="{{ old('end_date', optional($event->end_date ?? null)->toDateString() ?? '') }}">
        <div style="font-size:10.5px;color:var(--n-500);margin-top:3px;">
            Leave blank for a one-day schedule. Fill it for a training that runs several days —
            every day in between is blocked.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Start time</label>
        <input type="time" name="start_time"
            class="form-control" style="border-color:var(--n-200);font-size:13px;border-radius:8px;"
            value="{{ old('start_time', isset($event->start_time) && $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('H:i') : '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">End time</label>
        <input type="time" name="end_time"
            class="form-control" style="border-color:var(--n-200);font-size:13px;border-radius:8px;"
            value="{{ old('end_time', isset($event->end_time) && $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('H:i') : '') }}">
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Notes</label>
        <textarea name="notes" rows="2" maxlength="2000"
            class="form-control" style="border-color:var(--n-200);font-size:13px;border-radius:8px;"
            placeholder="Anything the staff should know...">{{ old('notes', $event->notes ?? '') }}</textarea>
    </div>
</div>
