@extends('staff.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-plus-circle me-2" style="color:var(--g-600);"></i>Post Job Vacancy
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">Create a new job vacancy for an approved employer</p>
</div>

<div class="card border-0 shadow-sm rounded-3" style="max-width:650px;">
    <div class="card-body p-4">
        <form action="{{ route('staff.jobs.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:var(--g-700);">
                    Employer / Company <span class="text-danger">*</span>
                </label>
                <select name="company_id" class="form-select"
                    style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;" required>
                    <option value="">— Select Employer —</option>
                    @foreach($employers as $employer)
                    <option value="{{ $employer->users_id }}"
                        {{ old('company_id') == $employer->users_id ? 'selected' : '' }}>
                        {{ $employer->company_name ?? $employer->name }}
                    </option>
                    @endforeach
                </select>
                @error('company_id')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:var(--g-700);">
                    Job Title <span class="text-danger">*</span>
                </label>
                <input type="text" name="title" class="form-control"
                    style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                    placeholder="e.g. Sales Associate"
                    value="{{ old('title') }}" required>
                @error('title')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:var(--g-700);">
                    Description <span class="text-danger">*</span>
                </label>
                <textarea name="description" class="form-control" rows="4"
                    style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                    placeholder="Describe the job responsibilities and requirements..."
                    required>{{ old('description') }}</textarea>
                @error('description')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small" style="color:var(--g-700);">
                        Location <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="location" class="form-control"
                        style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                        placeholder="e.g. Cagayan de Oro"
                        value="{{ old('location') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small" style="color:var(--g-700);">
                        Job Type <span class="text-danger">*</span>
                    </label>
                    <select name="type" class="form-select"
                        style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;" required>
                        <option value="">— Select Type —</option>
                        @foreach(['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contractual' => 'Contractual', 'casual' => 'Casual'] as $val => $label)
                        <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:var(--g-700);">
                    Major Industry Group <span class="text-danger">*</span>
                </label>
                <select name="industry_group" class="form-select"
                    style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;" required>
                    <option value="">— Select Industry Group —</option>
                    @foreach(\App\Models\EmployerNsrpRegistration::INDUSTRY_GROUPS as $group)
                    <option value="{{ $group }}" {{ old('industry_group') === $group ? 'selected' : '' }}>{{ $group }}</option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small" style="color:var(--g-700);">
                        No. of Slots <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="slots" class="form-control"
                        style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                        placeholder="1" min="1"
                        value="{{ old('slots') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small" style="color:var(--g-700);">
                        Deadline
                    </label>
                    <input type="date" name="deadline" class="form-control"
                        style="border:1px solid var(--n-200);border-radius:10px;font-size:13px;"
                        value="{{ old('deadline') }}">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn fw-semibold"
                    style="background:var(--g-600);
                           color:#fff;border:none;border-radius:10px;
                           padding:10px 24px;font-size:14px;">
                    <i class="ph-fill ph-briefcase me-2"></i>Post Vacancy
                </button>
                <a href="{{ route('staff.jobs') }}"
                   class="btn fw-semibold"
                   style="border:1px solid var(--n-200);color:var(--g-700);
                          background:#fff;border-radius:10px;
                          padding:10px 24px;font-size:14px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection