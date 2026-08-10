@extends('company.layouts.app')

@section('page-title', 'Edit Job')

@section('content')

<div class="mb-4 fade-in">
    <a href="{{ route('company.jobs') }}" style="font-size:13px; color:#4dd9c0; text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i> Back to Job Posts
    </a>
    <h5 class="fw-bold mt-2 mb-0" style="color:#2d7a5f;">Edit Job Post</h5>
</div>

<div class="card border-0 shadow-sm rounded-3 fade-in-1" style="max-width: 820px;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="fw-bold mb-0" style="color:#2d7a5f;">
                <i class="bi bi-pencil me-2" style="color:#4dd9c0;"></i>{{ $job->title }}
            </h6>
            @php
                $statusBadge = [
                    'open'   => ['bg' => '#2d7a5f', 'label' => 'Open'],
                    'closed' => ['bg' => '#888',    'label' => 'Closed'],
                ][$job->status] ?? ['bg' => '#888', 'label' => ucfirst($job->status)];
                $postingBadge = [
                    'pending'  => ['bg' => '#f59e0b', 'label' => 'Pending Approval'],
                    'approved' => ['bg' => '#2d7a5f', 'label' => 'Approved'],
                    'rejected' => ['bg' => '#e05252', 'label' => 'Rejected'],
                ][$job->posting_status] ?? null;
            @endphp
            <div class="d-flex gap-2">
                <span class="badge fw-semibold" style="background:{{ $statusBadge['bg'] }};font-size:11px;padding:5px 12px;border-radius:20px;">
                    {{ $statusBadge['label'] }}
                </span>
                @if($postingBadge)
                <span class="badge fw-semibold" style="background:{{ $postingBadge['bg'] }};font-size:11px;padding:5px 12px;border-radius:20px;">
                    {{ $postingBadge['label'] }}
                </span>
                @endif
            </div>
        </div>

        @if($job->posting_status === 'pending')
        <div class="mb-4 p-3 rounded-3" style="background:#fff8e1;border:1px solid #f9a825;">
            <div style="font-size:12.5px;color:#a16207;">
                <i class="bi bi-info-circle-fill me-1"></i>
                This job posting is still pending review by PESO staff. Status (Open/Closed) is controlled by PESO staff and cannot be edited here.
            </div>
        </div>
        @endif

        @if($job->schedule_type)
        <div class="mb-4 p-3 rounded-3" style="background:#f0f9f6;border:1px solid #a8e6cf;">
            <div style="font-size:11px;color:#888;margin-bottom:4px;">Schedule Type</div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge fw-semibold" style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;padding:5px 12px;">
                    {{ $job->schedule_type === 'inhouse' ? 'In-house' : ($job->schedule_type === 'job_fair' ? 'Job Fair' : 'Office Based') }}
                </span>
                @if($job->schedule_type === 'inhouse')
                    <span style="font-size:12.5px;color:#2d7a5f;">
                        {{ $job->preferred_date ? \Carbon\Carbon::parse($job->preferred_date)->format('M d, Y') : '—' }}
                        · 8:00 AM – 5:00 PM
                        · {{ $job->venue_type === 'other' ? $job->venue_address : 'PESO Office' }}
                    </span>
                @endif
            </div>
            <div style="font-size:11px;color:#888;margin-top:6px;">
                Schedule details were set when this job was requested and cannot be changed here.
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('company.jobs.update', $job->job_qualifications_id) }}">
            @csrf
            @method('PUT')

            {{-- III. VACANCY DETAILS --}}
            <div class="mb-3 pb-2" style="border-bottom:2px solid #e8f5f0;">
                <span class="fw-bold" style="color:#2d7a5f;font-size:13px;">
                    <i class="bi bi-briefcase me-1" style="color:#4dd9c0;"></i> III. Vacancy Details
                </span>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Position Title *</label>
                    <input type="text" name="title" class="form-control"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"
                        value="{{ old('title', $job->title) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Job Description *</label>
                    <textarea name="description" class="form-control" rows="3" required
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;resize:vertical;">{{ old('description', $job->description) }}</textarea>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Nature of Work *</label>
                <div class="row g-2">
                    @foreach([
                        'permanent'      => 'Permanent',
                        'contractual'    => 'Contractual',
                        'project_based'  => 'Project-based',
                        'internship'     => 'Internship / OJT',
                        'part_time'      => 'Part-time',
                        'work_from_home' => 'Work from home / online job',
                    ] as $val => $label)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                name="type" value="{{ $val }}" id="type_{{ $val }}"
                                {{ old('type', $job->type) === $val ? 'checked' : '' }} required>
                            <label class="form-check-label" for="type_{{ $val }}"
                                style="font-size:12px;color:#555;">{{ $label }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Place of Work *</label>
                    <input type="text" name="location" class="form-control"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"
                        value="{{ old('location', $job->location) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Salary</label>
                    <input type="text" name="salary" class="form-control"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"
                        value="{{ old('salary', $job->salary) }}" placeholder="e.g. 15,000 / Negotiable">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Vacancy Count *</label>
                    <input type="number" name="slots" class="form-control"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"
                        value="{{ old('slots', $job->slots) }}" min="1" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Application Deadline</label>
                    <input type="date" name="deadline" class="form-control"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"
                        value="{{ old('deadline', $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('Y-m-d') : '') }}">
                </div>
            </div>

            {{-- IV. QUALIFICATION REQUIREMENTS --}}
            <div class="mb-3 pb-2 mt-4" style="border-bottom:2px solid #e8f5f0;">
                <span class="fw-bold" style="color:#2d7a5f;font-size:13px;">
                    <i class="bi bi-clipboard-check me-1" style="color:#4dd9c0;"></i> IV. Qualification Requirements
                </span>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Work Experience (months)</label>
                    <input type="number" name="experience_months" class="form-control" min="0"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"
                        value="{{ old('experience_months', $job->experience_months) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Religion</label>
                    <input type="text" name="religion" class="form-control"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"
                        value="{{ old('religion', $job->religion) }}" placeholder="e.g. Any">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Major Industry Group *</label>
                    <select name="industry_group" class="form-select" required
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        <option value="" disabled {{ old('industry_group', $job->industry_group) ? '' : 'selected' }}>Select industry group</option>
                        @foreach([
                            'Agriculture, Hunting and Forestry, Fishing',
                            'Mining and Quarrying',
                            'Manufacturing',
                            'Construction',
                            'Wholesale, Retail Trade, Repair of Motor Vehicles, Motorcycles, & Personal and Household Goods',
                            'Hotel and Restaurants',
                            'Transport, Storage and Communications',
                            'Financial Intermediation',
                            'Real Estate, Renting and Business Activities',
                            'Public Administration and Defense, Compulsory Social Security',
                            'Education',
                            'Health and Social Work',
                            'Other Community, Social and Personal Activities',
                            'Extra-territorial Organization and Bodies',
                            'Overseas Manpower Services',
                        ] as $group)
                        <option value="{{ $group }}" {{ old('industry_group', $job->industry_group) === $group ? 'selected' : '' }}>{{ $group }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Sex</label>
                    <div class="d-flex gap-3 flex-wrap">
                        @foreach(['Male' => 'Male', 'Female' => 'Female', 'Any' => 'No Preference'] as $val => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                name="sex_preference" value="{{ $val }}"
                                id="sex_{{ $val }}" {{ old('sex_preference', $job->sex_preference ?? 'Any') === $val ? 'checked' : '' }}>
                            <label class="form-check-label" for="sex_{{ $val }}"
                                style="font-size:12px;color:#555;">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Civil Status</label>
                    <div class="d-flex gap-3 flex-wrap">
                        @foreach(['Single' => 'Single', 'Married' => 'Married', 'Any' => 'No Preference'] as $val => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                name="civil_status" value="{{ $val }}"
                                id="civil_{{ $val }}" {{ old('civil_status', $job->civil_status ?? 'Any') === $val ? 'checked' : '' }}>
                            <label class="form-check-label" for="civil_{{ $val }}"
                                style="font-size:12px;color:#555;">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Other Qualifications</label>
                    <textarea name="other_qualifications" class="form-control" rows="2"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">{{ old('other_qualifications', $job->other_qualifications) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Educational Level</label>
                    <select name="education_required" class="form-select"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        <option value="">Any</option>
                        @foreach(['Elementary','High School','Senior High','Tertiary / College','Graduate Studies'] as $level)
                        <option value="{{ $level }}" {{ old('education_required', $job->education_required) === $level ? 'selected' : '' }}>{{ $level }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Course / Major</label>
                    <input type="text" name="course_major" class="form-control"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"
                        value="{{ old('course_major', $job->course_major) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">License</label>
                    <input type="text" name="license" class="form-control"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"
                        value="{{ old('license', $job->license) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Eligibility</label>
                    <input type="text" name="eligibility" class="form-control"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"
                        value="{{ old('eligibility', $job->eligibility) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Certification</label>
                    <input type="text" name="certification" class="form-control"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"
                        value="{{ old('certification', $job->certification) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Language / Dialect Spoken</label>
                    <input type="text" name="language" class="form-control"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"
                        value="{{ old('language', $job->language) }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Preferred Residence</label>
                    <input type="text" name="preferred_residence" class="form-control"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"
                        value="{{ old('preferred_residence', $job->preferred_residence) }}">
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn fw-semibold px-4"
                    style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:13px;padding:10px 24px;">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
                <a href="{{ route('company.jobs') }}" class="btn fw-semibold px-4"
                    style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:13px;padding:10px 24px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection