@extends('company.layouts.app')

@section('page-title', 'Post a Job')

@section('content')

<div class="mb-4 fade-in">
    <a href="{{ route('company.jobseekers') }}" style="font-size:13px; color:var(--g-600); text-decoration:none;">
        <i class="ph ph-arrow-left me-1"></i> Back to Active Job Postings
    </a>
    <h5 class="fw-bold mt-2 mb-0" style="color:var(--g-700);">Post a New Job</h5>
</div>

<div class="peso-card fade-in-1" style="max-width: 720px;">
    <div class="peso-card-header">
        <h6><i class="ph ph-briefcase me-2" style="color:var(--g-600);"></i>Job Details</h6>
    </div>
    <div class="peso-card-body">
        <form method="POST" action="{{ route('company.jobs.store') }}">
            @csrf

            <div class="row g-3">

                {{-- Job Title --}}
                <div class="col-12">
                    <label class="peso-label">Job Title *</label>
                    <input type="text" name="title" class="peso-input"
                        placeholder="e.g. Cashier, Encoder, Delivery Rider"
                        value="{{ old('title') }}" required>
                </div>

                {{-- Description --}}
                <div class="col-12">
                    <label class="peso-label">Job Description *</label>
                    <textarea name="description" class="peso-input" rows="4"
                        placeholder="Describe the job responsibilities, qualifications, and requirements..."
                        required style="resize:vertical;">{{ old('description') }}</textarea>
                </div>

                {{-- Location --}}
                <div class="col-md-6">
                    <label class="peso-label">Location *</label>
                    <input type="text" name="location" class="peso-input"
                        placeholder="e.g. Cagayan de Oro City"
                        value="{{ old('location') }}" required>
                </div>

                {{-- Job Type --}}
                <div class="col-md-6">
                    <label class="peso-label">Job Type *</label>
                    <select name="type" class="peso-input" required>
                        <option value="" disabled {{ old('type') ? '' : 'selected' }}>Select type</option>
                        <option value="full_time" {{ old('type') == 'full_time' ? 'selected' : '' }}>Full-time</option>
                        <option value="part_time" {{ old('type') == 'part_time' ? 'selected' : '' }}>Part-time</option>
                        <option value="contractual" {{ old('type') == 'contractual' ? 'selected' : '' }}>Contractual</option>
                    </select>
                </div>

                {{-- Industry Group --}}
                <div class="col-12">
                    <label class="peso-label">Major Industry Group *</label>
                    <select name="industry_group" class="peso-input" required>
                        <option value="" disabled {{ old('industry_group') ? '' : 'selected' }}>Select industry group</option>
                        @foreach(\App\Models\EmployerNsrpRegistration::INDUSTRY_GROUPS as $group)
                        <option value="{{ $group }}" {{ old('industry_group') === $group ? 'selected' : '' }}>{{ $group }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Slots --}}
                <div class="col-md-6">
                    <label class="peso-label">Number of Slots *</label>
                    <input type="number" name="slots" class="peso-input"
                        placeholder="e.g. 3"
                        value="{{ old('slots', 1) }}" min="1" required>
                </div>

                {{-- Deadline --}}
                <div class="col-md-6">
                    <label class="peso-label">Application Deadline</label>
                    <input type="date" name="deadline" class="peso-input"
                        value="{{ old('deadline') }}"
                        min="{{ date('Y-m-d') }}">
                    <small style="font-size:11px;color:var(--n-500);">Leave blank if no deadline</small>
                </div>

                {{-- Buttons --}}
                <div class="col-12 d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-peso px-4">
                        <i class="ph ph-check me-1"></i> Post Job
                    </button>
                    <a href="{{ route('company.jobseekers') }}" class="btn btn-peso-outline px-4">
                        Cancel
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection