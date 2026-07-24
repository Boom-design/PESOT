@extends('company.layouts.app')

@section('page-title', 'Post a Job')

@section('content')

<div class="mb-4 fade-in">
    <a href="{{ route('company.jobs') }}" style="font-size:13px; color:#4dd9c0; text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i> Back to Job Posts
    </a>
    <h5 class="fw-bold mt-2 mb-0" style="color:#2d7a5f;">Post a New Job</h5>
</div>

<div class="peso-card fade-in-1" style="max-width: 720px;">
    <div class="peso-card-header">
        <h6><i class="bi bi-briefcase me-2" style="color:#4dd9c0;"></i>Job Details</h6>
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
                        <option value="Agriculture, Hunting and Forestry, Fishing" {{ old('industry_group') === 'Agriculture, Hunting and Forestry, Fishing' ? 'selected' : '' }}>Agriculture, Hunting and Forestry, Fishing</option>
                        <option value="Mining and Quarrying" {{ old('industry_group') === 'Mining and Quarrying' ? 'selected' : '' }}>Mining and Quarrying</option>
                        <option value="Manufacturing" {{ old('industry_group') === 'Manufacturing' ? 'selected' : '' }}>Manufacturing</option>
                        <option value="Construction" {{ old('industry_group') === 'Construction' ? 'selected' : '' }}>Construction</option>
                        <option value="Wholesale, Retail Trade, Repair of Motor Vehicles, Motorcycles, & Personal and Household Goods" {{ old('industry_group') === 'Wholesale, Retail Trade, Repair of Motor Vehicles, Motorcycles, & Personal and Household Goods' ? 'selected' : '' }}>Wholesale, Retail Trade, Repair of Motor Vehicles, Motorcycles, & Personal and Household Goods</option>
                        <option value="Hotel and Restaurants" {{ old('industry_group') === 'Hotel and Restaurants' ? 'selected' : '' }}>Hotel and Restaurants</option>
                        <option value="Transport, Storage and Communications" {{ old('industry_group') === 'Transport, Storage and Communications' ? 'selected' : '' }}>Transport, Storage and Communications</option>
                        <option value="Financial Intermediation" {{ old('industry_group') === 'Financial Intermediation' ? 'selected' : '' }}>Financial Intermediation</option>
                        <option value="Real Estate, Renting and Business Activities" {{ old('industry_group') === 'Real Estate, Renting and Business Activities' ? 'selected' : '' }}>Real Estate, Renting and Business Activities</option>
                        <option value="Public Administration and Defense, Compulsory Social Security" {{ old('industry_group') === 'Public Administration and Defense, Compulsory Social Security' ? 'selected' : '' }}>Public Administration and Defense, Compulsory Social Security</option>
                        <option value="Education" {{ old('industry_group') === 'Education' ? 'selected' : '' }}>Education</option>
                        <option value="Health and Social Work" {{ old('industry_group') === 'Health and Social Work' ? 'selected' : '' }}>Health and Social Work</option>
                        <option value="Other Community, Social and Personal Activities" {{ old('industry_group') === 'Other Community, Social and Personal Activities' ? 'selected' : '' }}>Other Community, Social and Personal Activities</option>
                        <option value="Extra-territorial Organization and Bodies" {{ old('industry_group') === 'Extra-territorial Organization and Bodies' ? 'selected' : '' }}>Extra-territorial Organization and Bodies</option>
                        <option value="Overseas Manpower Services" {{ old('industry_group') === 'Overseas Manpower Services' ? 'selected' : '' }}>Overseas Manpower Services</option>
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
                    <small style="font-size:11px;color:#888;">Leave blank if no deadline</small>
                </div>

                {{-- Buttons --}}
                <div class="col-12 d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-peso px-4">
                        <i class="bi bi-check-lg me-1"></i> Post Job
                    </button>
                    <a href="{{ route('company.jobs') }}" class="btn btn-peso-outline px-4">
                        Cancel
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection