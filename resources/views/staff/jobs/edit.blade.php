@extends('staff.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-pencil-fill me-2" style="color:#4dd9c0;"></i>Edit Job Vacancy
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">Update job vacancy details</p>
</div>

<div class="card border-0 shadow-sm rounded-3" style="max-width:650px;">
    <div class="card-body p-4">
        <form action="{{ route('staff.jobs.update', $job->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Employer / Company <span class="text-danger">*</span>
                </label>
                <select name="company_id" class="form-select"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;" required>
                    <option value="">— Select Employer —</option>
                    @foreach($employers as $employer)
                    <option value="{{ $employer->id }}"
                        {{ old('company_id', $job->company_id) == $employer->id ? 'selected' : '' }}>
                        {{ $employer->company_name ?? $employer->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Job Title <span class="text-danger">*</span>
                </label>
                <input type="text" name="title" class="form-control"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                    value="{{ old('title', $job->title) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Description <span class="text-danger">*</span>
                </label>
                <textarea name="description" class="form-control" rows="4"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                    required>{{ old('description', $job->description) }}</textarea>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small" style="color:#2d7a5f;">Location</label>
                    <input type="text" name="location" class="form-control"
                        style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                        value="{{ old('location', $job->location) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small" style="color:#2d7a5f;">Job Type</label>
                    <select name="type" class="form-select"
                        style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;" required>
                        @foreach(['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contractual' => 'Contractual', 'casual' => 'Casual'] as $val => $label)
                        <option value="{{ $val }}"
                            {{ old('type', $job->type ?? $job->job_type) === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small" style="color:#2d7a5f;">Slots</label>
                    <input type="number" name="slots" class="form-control"
                        style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                        value="{{ old('slots', $job->slots) }}" min="1" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small" style="color:#2d7a5f;">Deadline</label>
                    <input type="date" name="deadline" class="form-control"
                        style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                        value="{{ old('deadline', $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('Y-m-d') : '') }}">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">Status</label>
                <select name="status" class="form-select"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;">
                    <option value="open"   {{ old('status', $job->status) === 'open'   ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ old('status', $job->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn fw-semibold"
                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                           color:#fff;border:none;border-radius:10px;
                           padding:10px 24px;font-size:14px;">
                    <i class="bi bi-check-circle-fill me-2"></i>Update Vacancy
                </button>
                <a href="{{ route('staff.jobs') }}"
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