@extends('staff.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-plus-circle-fill me-2" style="color:#4dd9c0;"></i>Post Job Vacancy
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">Create a new job vacancy for an approved employer</p>
</div>

<div class="card border-0 shadow-sm rounded-3" style="max-width:650px;">
    <div class="card-body p-4">
        <form action="{{ route('staff.jobs.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Employer / Company <span class="text-danger">*</span>
                </label>
                <select name="company_id" class="form-select"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;" required>
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
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Job Title <span class="text-danger">*</span>
                </label>
                <input type="text" name="title" class="form-control"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                    placeholder="e.g. Sales Associate"
                    value="{{ old('title') }}" required>
                @error('title')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Description <span class="text-danger">*</span>
                </label>
                <textarea name="description" class="form-control" rows="4"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                    placeholder="Describe the job responsibilities and requirements..."
                    required>{{ old('description') }}</textarea>
                @error('description')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                        Location <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="location" class="form-control"
                        style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                        placeholder="e.g. Cagayan de Oro"
                        value="{{ old('location') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                        Job Type <span class="text-danger">*</span>
                    </label>
                    <select name="type" class="form-select"
                        style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;" required>
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
                <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                    Major Industry Group <span class="text-danger">*</span>
                </label>
                <select name="industry_group" class="form-select"
                    style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;" required>
                    <option value="">— Select Industry Group —</option>
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

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                        No. of Slots <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="slots" class="form-control"
                        style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                        placeholder="1" min="1"
                        value="{{ old('slots') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small" style="color:#2d7a5f;">
                        Deadline
                    </label>
                    <input type="date" name="deadline" class="form-control"
                        style="border:1.5px solid #a8e6cf;border-radius:10px;font-size:13px;"
                        value="{{ old('deadline') }}">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn fw-semibold"
                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                           color:#fff;border:none;border-radius:10px;
                           padding:10px 24px;font-size:14px;">
                    <i class="bi bi-briefcase-fill me-2"></i>Post Vacancy
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