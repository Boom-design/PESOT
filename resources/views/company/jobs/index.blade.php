@extends('company.layouts.app')

@section('page-title', ' ')

@section('content')

{{-- ── HEADER ── --}}
<div class="d-flex align-items-center justify-content-between mb-4 fade-in">
    <div>
        <h5 class="fw-bold mb-1" style="color: #2d7a5f;">List of Job Vacancy Request</h5>
        <p class="mb-0" style="font-size: 13px; color: #888;">Manage all your job postings</p>
    </div>
    <a href="#" class="btn btn-peso" data-bs-toggle="modal" data-bs-target="#requestJobModal">
        <i class="bi bi-plus-lg me-1"></i> Request Job Posting
    </a>
</div>

{{-- ── INITIAL VACANCY CONFIRMATION MODAL (auto-popup gikan sa registration) ── --}}
@if($showInitialVacancy ?? false)
<div class="modal fade" id="initialVacancyModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" style="max-height:90vh;margin-top:5vh;margin-bottom:5vh;">
        <div class="modal-content" style="border-radius:16px;border:none;max-height:90vh;display:flex;flex-direction:column;">
            <div class="modal-header" style="background:linear-gradient(90deg,#90d870,#4dd9c0);flex-shrink:0;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-briefcase me-2"></i>Confirm Your Job Vacancy Posting
                </h5>
            </div>
            <form id="confirmVacancyForm" action="{{ route('company.jobs.confirmInitial') }}" method="POST"
                style="display:flex;flex-direction:column;overflow:hidden;flex:1;">
                @csrf
                <div class="modal-body p-4" style="overflow-y:auto;flex:1;">

                    <div class="alert alert-info" style="font-size:13px;border-radius:10px;">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        The following details were entered during your registration.
                        Review them, then click <strong>"Confirm"</strong> if everything is correct,
                        or click <strong>"Update"</strong> if you want to make changes first.
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:13px;">Schedule Type *</label>
                        <div class="row g-2">
                            @foreach([
                                'office_based' => ['Office Based', 'bi-building'],
                                'inhouse'      => ['In-house', 'bi-calendar-check'],
                                'job_fair'     => ['Job Fair', 'bi-calendar-event'],
                            ] as $val => $opt)
                            <div class="col-md-4">
                                <div class="form-check p-3" style="border:1.5px solid #a8e6cf;border-radius:10px;">
                                    <input class="form-check-input initial-schedule-type-radio" type="radio"
                                        name="schedule_type" value="{{ $val }}" id="initial_sched_{{ $val }}"
                                        {{ $val === 'office_based' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="initial_sched_{{ $val }}"
                                        style="font-size:12px;color:#2d7a5f;font-weight:600;">
                                        <i class="{{ $opt[1] }} me-1"></i>{{ $opt[0] }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div id="initialDateFields" class="row g-3 mb-4" style="display:flex;">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Preferred Date *</label>
                            <input type="date" name="preferred_date" id="initialPreferredDateInput" class="form-control"
                                min="{{ now()->format('Y-m-d') }}"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                            <small id="initialPreferredDateHint" style="font-size:11px;color:#888;display:none;margin-top:4px;">
                                Interview time will be scheduled between 8:00 AM – 5:00 PM.
                            </small>
                            <div id="initialDateAvailabilityNote" style="font-size:11px;margin-top:4px;"></div>
                        </div>
                        <div class="col-md-6" id="initialVenueFields" style="display:none;">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Venue *</label>
                            <select name="venue_type" id="initialVenueTypeSelect" class="form-select"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                                <option value="peso_office">PESO Office</option>
                                <option value="other">Other Venue</option>
                            </select>
                        </div>
                        <div class="col-12" id="initialVenueAddressWrap" style="display:none;">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Venue Address *</label>
                            <input type="text" name="venue_address" id="initialVenueAddressInput" class="form-control"
                                placeholder="e.g. SM CDO Downtown Premier, 3rd Floor"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                    </div>

                    <div id="vacancyPositionsContainer">
                        @foreach(($employerNsrp->initial_vacancy_data ?? []) as $i => $pos)
                        <div class="position-row mb-3 p-3" style="border:1.5px solid #a8e6cf;border-radius:12px;background:#f8fdfc;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span style="font-size:12px;font-weight:700;color:#2d7a5f;">Position #{{ $i + 1 }}</span>
                                @if($i > 0)
                                <button type="button" class="btn btn-sm remove-position-btn"
                                    style="background:#fff5f5;color:#e05252;border:1px solid #ffcdd2;border-radius:8px;font-size:11px;padding:2px 10px;">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                                @endif
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Position Title *</label>
                                    <input type="text" name="positions[{{ $i }}][title]" class="form-control vacancy-field"
                                        value="{{ $pos['title'] ?? '' }}" readonly required
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Nature of Work *</label>
                                    <select name="positions[{{ $i }}][type]" class="form-select vacancy-field vacancy-locked" required
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;pointer-events:none;" tabindex="-1">
                                        @foreach(['permanent'=>'Permanent','contractual'=>'Contractual','project_based'=>'Project-based','internship'=>'Internship / OJT','part_time'=>'Part-time','work_from_home'=>'Work from home / online job'] as $val => $label)
                                        <option value="{{ $val }}" {{ ($pos['type'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Job Description *</label>
                                    <textarea name="positions[{{ $i }}][description]" rows="2" readonly required
                                        class="form-control vacancy-field"
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">{{ $pos['description'] ?? '' }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Place of Work *</label>
                                    <input type="text" name="positions[{{ $i }}][location]" class="form-control vacancy-field"
                                        value="{{ $pos['location'] ?? '' }}" readonly required
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Salary</label>
                                    <input type="text" name="positions[{{ $i }}][salary]" class="form-control vacancy-field"
                                        value="{{ $pos['salary'] ?? '' }}" readonly
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Vacancy Count *</label>
                                    <input type="number" name="positions[{{ $i }}][slots]" class="form-control vacancy-field"
                                        value="{{ $pos['slots'] ?? '' }}" readonly required min="1"
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">
                                </div>
                                <div class="col-md-2 deadline-field-wrap">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Deadline</label>
                                    <input type="date" name="positions[{{ $i }}][deadline]" class="form-control vacancy-field"
                                        value="{{ $pos['deadline'] ?? '' }}" readonly
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">
                                </div>

                                <div class="col-12 mt-2 pt-2" style="border-top:1px dashed #a8e6cf;">
                                    <div style="font-size:11px;font-weight:700;color:#2d7a5f;margin-bottom:6px;">IV. Qualification Requirements</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Work Experience (months)</label>
                                    <input type="number" name="positions[{{ $i }}][experience_months]" class="form-control vacancy-field"
                                        value="{{ $pos['experience_months'] ?? '' }}" readonly
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Religion</label>
                                    <input type="text" name="positions[{{ $i }}][religion]" class="form-control vacancy-field"
                                        value="{{ $pos['religion'] ?? '' }}" readonly
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Sex</label>
                                    <select name="positions[{{ $i }}][sex_preference]" class="form-select vacancy-field vacancy-locked"
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;pointer-events:none;" tabindex="-1">
                                        @foreach(['Any'=>'No Preference','Male'=>'Male','Female'=>'Female'] as $val=>$label)
                                        <option value="{{ $val }}" {{ ($pos['sex_preference'] ?? 'Any') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Civil Status</label>
                                    <select name="positions[{{ $i }}][civil_status]" class="form-select vacancy-field vacancy-locked"
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;pointer-events:none;" tabindex="-1">
                                        @foreach(['Any'=>'No Preference','Single'=>'Single','Married'=>'Married'] as $val=>$label)
                                        <option value="{{ $val }}" {{ ($pos['civil_status'] ?? 'Any') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Other Qualifications</label>
                                    <textarea name="positions[{{ $i }}][other_qualifications]" rows="2" readonly
                                        class="form-control vacancy-field"
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">{{ $pos['other_qualifications'] ?? '' }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Educational Level</label>
                                    <input type="text" name="positions[{{ $i }}][education_required]" class="form-control vacancy-field"
                                        value="{{ $pos['education_required'] ?? '' }}" readonly
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Course/Major</label>
                                    <input type="text" name="positions[{{ $i }}][course_major]" class="form-control vacancy-field"
                                        value="{{ $pos['course_major'] ?? '' }}" readonly
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">License</label>
                                    <input type="text" name="positions[{{ $i }}][license]" class="form-control vacancy-field"
                                        value="{{ $pos['license'] ?? '' }}" readonly
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Eligibility</label>
                                    <input type="text" name="positions[{{ $i }}][eligibility]" class="form-control vacancy-field"
                                        value="{{ $pos['eligibility'] ?? '' }}" readonly
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Certification</label>
                                    <input type="text" name="positions[{{ $i }}][certification]" class="form-control vacancy-field"
                                        value="{{ $pos['certification'] ?? '' }}" readonly
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Language/Dialect</label>
                                    <input type="text" name="positions[{{ $i }}][language]" class="form-control vacancy-field"
                                        value="{{ $pos['language'] ?? '' }}" readonly
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Preferred Residence</label>
                                    <input type="text" name="positions[{{ $i }}][preferred_residence]" class="form-control vacancy-field"
                                        value="{{ $pos['preferred_residence'] ?? '' }}" readonly
                                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;background:#f0f9f6;">
                                </div>
                                @if(!empty($pos['job_image']))
                                <div class="col-12 mt-2 pt-2" style="border-top:1px dashed #a8e6cf;">
                                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">
                                        <i class="bi bi-image me-1" style="color:#4dd9c0;"></i>Hiring Poster / Image
                                    </label>
                                    <div class="mt-1">
                                        <img src="{{ Storage::url($pos['job_image']) }}" alt="Job Image"
                                             style="max-width:200px;max-height:150px;border-radius:8px;border:1.5px solid #a8e6cf;object-fit:cover;">
                                    </div>
                                    <input type="hidden" name="positions[{{ $i }}][job_image]" value="{{ $pos['job_image'] }}">
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <button type="button" id="addVacancyPositionBtn"
                        class="btn btn-sm fw-semibold mb-2"
                        style="display:none;border:1.5px dashed #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;font-size:12px;padding:6px 16px;">
                        <i class="bi bi-plus-circle me-1"></i> Add Position
                    </button>

                </div>
                <div class="modal-footer" style="border-top:1px solid #e8f5f0;flex-shrink:0;">
                    <button type="button" id="updateVacancyBtn" class="btn btn-sm fw-semibold"
                        style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;padding:8px 20px;">
                        <i class="bi bi-pencil-square me-1"></i> Update
                    </button>
                    <button type="submit" id="confirmVacancyBtn" class="btn btn-sm fw-semibold"
                        style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;padding:8px 24px;">
                        <i class="bi bi-check-circle-fill me-1"></i> Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ── SEARCH ── --}}
<div class="peso-card mb-3 fade-in">
    <div class="peso-card-body py-2">
        <input
            type="text"
            id="searchInput"
            class="peso-input w-100"
            placeholder="🔍  Search job title or location..."
        >
    </div>
</div>

{{-- ── TABLE ── --}}
<div class="peso-card fade-in-1">
    @if($jobs->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-briefcase"></i>
            </div>
            <h6>No job posts yet</h6>
            <p>Click "Post a Job" to get started!</p>
            <a href="#" class="btn btn-peso mt-3" data-bs-toggle="modal" data-bs-target="#requestJobModal">
                <i class="bi bi-plus-lg me-1"></i> Request Job Posting
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table peso-table mb-0" id="jobsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Job Title</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Slots</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobs as $i => $job)
                    <tr class="job-row">
                        <td style="color:#888;">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $job->title }}</td>
                        <td><i class="bi bi-geo-alt me-1" style="color:#4dd9c0;"></i>{{ $job->location }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $job->type)) }}</td>
                        <td>{{ $job->slots }}</td>
                        <td>{{ $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('M d, Y') : '—' }}</td>
                        <td>
                            @if($job->is_expired)
                                <span class="badge-closed" title="Deadline has passed">Closed (Expired)</span>
                            @else
                                <span class="badge-{{ $job->status }}">{{ ucfirst($job->status) }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($job->status === 'closed')
                                <a href="{{ route('company.jobs.edit', $job->id) }}"
                                   class="btn btn-sm btn-peso-outline py-1 px-2" style="font-size:11px;"
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @else
                                <button type="button" class="btn btn-sm py-1 px-2" disabled
                                    style="font-size:11px;background:#f5f5f5;color:#bbb;border:1px solid #e0e0e0;border-radius:8px;cursor:not-allowed;"
                                    title="Cannot edit — already approved and open">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- ── REQUEST JOB MODAL ── --}}
@php
    $requirement = \App\Models\EmployerRequirement::where('user_id', optional($company->employerNsrp)->id ?? 0)->first();
    $reqStatus = $requirement?->status ?? 'none';
@endphp

@if($reqStatus === 'approved')
<div class="modal fade" id="requestJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg" style="max-height:90vh;margin-top:5vh;margin-bottom:5vh;">
        <div class="modal-content" style="border-radius:16px;border:none;max-height:90vh;display:flex;flex-direction:column;">
            <div class="modal-header" style="background:linear-gradient(90deg,#90d870,#4dd9c0);flex-shrink:0;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="bi bi-briefcase me-2"></i>Request Job Posting
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('company.jobs.request') }}" method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;overflow:hidden;flex:1;">
                @csrf
                <div class="modal-body p-4" style="overflow-y:auto;flex:1;">

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:13px;">Schedule Type *</label>
                        <div class="row g-2">
                            @foreach([
                                'office_based' => ['Office Based', 'bi-building'],
                                'inhouse'      => ['In-house', 'bi-calendar-check'],
                                'job_fair'     => ['Job Fair', 'bi-calendar-event'],
                            ] as $val => $opt)
                            <div class="col-md-4">
                                <div class="form-check p-3" style="border:1.5px solid #a8e6cf;border-radius:10px;">
                                    <input class="form-check-input schedule-type-radio" type="radio"
                                        name="schedule_type" value="{{ $val }}" id="sched_{{ $val }}"
                                        {{ $val === 'office_based' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="sched_{{ $val }}"
                                        style="font-size:12px;color:#2d7a5f;font-weight:600;">
                                        <i class="{{ $opt[1] }} me-1"></i>{{ $opt[0] }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div id="dateFields" class="row g-3 mb-4" style="display:flex;">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Preferred Date *</label>
                            <input type="date" name="preferred_date" id="preferredDateInput" class="form-control"
                                min="{{ now()->format('Y-m-d') }}"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                            <small id="preferredDateHint" style="font-size:11px;color:#888;display:none;margin-top:4px;">
                                Interview time will be scheduled between 8:00 AM – 5:00 PM.
                            </small>
                            <div id="dateAvailabilityNote" style="font-size:11px;margin-top:4px;"></div>
                        </div>
                        <div class="col-md-6" id="venueFields" style="display:none;">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Venue *</label>
                            <select name="venue_type" id="venueTypeSelect" class="form-select"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                                <option value="peso_office">PESO Office</option>
                                <option value="other">Other Venue</option>
                            </select>
                        </div>
                        <div class="col-12" id="venueAddressWrap" style="display:none;">
                            <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Venue Address *</label>
                            <input type="text" name="venue_address" id="venueAddressInput" class="form-control"
                                placeholder="e.g. SM CDO Downtown Premier, 3rd Floor"
                                style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">
                            <i class="bi bi-image me-1" style="color:#4dd9c0;"></i>Hiring Poster / Image <span style="font-weight:400;color:#888;">(optional)</span>
                        </label>
                        <div style="font-size:11px;color:#888;margin-bottom:6px;">
                            Upload a hiring poster or flyer image for this posting (e.g. "Hiring Waiter" with a photo) — this will be visible to jobseekers.
                        </div>
                        <input type="file" name="poster_image" class="form-control" accept=".jpg,.jpeg,.png"
                            style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                    </div>

                    <div class="mb-3 pb-2" style="border-bottom:2px solid #e8f5f0;">
                        <span class="fw-bold" style="color:#2d7a5f;font-size:13px;">
                            <i class="bi bi-briefcase me-1" style="color:#4dd9c0;"></i> III-IV. Vacancy Details & Qualification Requirements
                        </span>
                    </div>
                    <div style="font-size:12px;color:#888;margin-bottom:12px;">
                        You may add more than one position under this request — each can have its own description and qualifications.
                    </div>

                    <div id="requestPositionsContainer"></div>

                    <button type="button" id="addRequestPositionBtn" class="btn btn-sm fw-semibold mb-4"
                        style="border:1.5px solid #4dd9c0;color:#2d7a5f;background:#e8f8f3;border-radius:8px;font-size:12px;padding:8px 18px;">
                        <i class="bi bi-plus-circle-fill me-1"></i> Add Position
                    </button>

                    </div>
                <div class="modal-footer" style="border-top:1px solid #e8f5f0;flex-shrink:0;">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-peso btn-sm px-4">
                        <i class="bi bi-send me-1"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ── DELETE MODAL ── --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none; box-shadow: 0 8px 32px rgba(0,0,0,0.12);">
            <div class="modal-body text-center p-4">
                <div style="width:60px;height:60px;background:#fff5f5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="bi bi-trash" style="font-size:24px;color:#e53935;"></i>
                </div>
                <h6 class="fw-bold mb-2" style="color:#2d7a5f;">Delete Job Post?</h6>
                <p style="font-size:13px;color:#888;" id="deleteJobName">Are you sure you want to delete this job?</p>
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <button class="btn btn-peso-outline px-4" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm px-4 py-2"
                            style="background:#e53935;color:#fff;border:none;border-radius:10px;font-weight:600;">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const COMPANY_INDUSTRY_GROUP = @json($company->employerNsrp->industry_group ?? '');

    // Search
    document.getElementById('searchInput').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.job-row').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // Delete confirm
    function confirmDelete(id, title) {
        document.getElementById('deleteJobName').textContent = 'Delete "' + title + '"? This cannot be undone.';
        document.getElementById('deleteForm').action = '/company/jobs/' + id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // ── Schedule Type toggle ── (Preferred Date: office_based + inhouse; Venue: inhouse ra; Deadline field per-position: itago kung job_fair)
    function toggleDeadlineFieldsForScheduleType(scheduleType) {
        document.querySelectorAll('.deadline-field-wrap').forEach(function(wrap) {
            wrap.style.display = scheduleType === 'job_fair' ? 'none' : 'block';
        });
    }

    document.querySelectorAll('.schedule-type-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const dateFields  = document.getElementById('dateFields');
            const dateInput   = document.getElementById('preferredDateInput');
            const dateHint    = document.getElementById('preferredDateHint');
            const venueFields = document.getElementById('venueFields');
            const venueSelect = document.getElementById('venueTypeSelect');

            toggleDeadlineFieldsForScheduleType(this.value);

            if (this.value === 'job_fair') {
                dateFields.style.display = 'none';
                dateInput.removeAttribute('required');
                venueSelect.removeAttribute('required');
                document.getElementById('venueAddressInput').removeAttribute('required');
                document.getElementById('venueAddressWrap').style.display = 'none';
            } else {
                dateFields.style.display = 'flex';
                dateInput.setAttribute('required', 'required');

                if (this.value === 'inhouse') {
                    venueFields.style.display = 'block';
                    dateHint.style.display = 'block';
                    venueSelect.setAttribute('required', 'required');
                } else {    
                    venueFields.style.display = 'none';
                    dateHint.style.display = 'none';
                    venueSelect.removeAttribute('required');
                    document.getElementById('venueAddressInput').removeAttribute('required');
                    document.getElementById('venueAddressWrap').style.display = 'none';
                }
            }
        });
    });

    // ── Venue Type toggle (PESO Office vs Other) ──
    const venueTypeSelect = document.getElementById('venueTypeSelect');
    if (venueTypeSelect) {
        venueTypeSelect.addEventListener('change', function() {
            const wrap = document.getElementById('venueAddressWrap');
            const addressInput = document.getElementById('venueAddressInput');
            if (this.value === 'other') {
                wrap.style.display = 'block';
                addressInput.setAttribute('required', 'required');
            } else {
                wrap.style.display = 'none';
                addressInput.removeAttribute('required');
            }
            if (preferredDateInputRef && preferredDateInputRef.value) {
                checkDateAvailability(preferredDateInputRef.value, this.value);
            }
        });
    }

    // ── Request Job Posting — Dynamic Add Position ──
    let requestPositionCount = 0;

    function buildRequestPositionRow(idx) {
        return `
        <div class="request-position-row mb-3 p-3" style="border:1.5px solid #a8e6cf;border-radius:12px;background:#f8fdfc;position:relative;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span style="font-size:12px;font-weight:700;color:#2d7a5f;">Position #<span class="req-pos-num">${idx + 1}</span></span>
                <button type="button" class="btn btn-sm remove-request-position-btn"
                    style="background:#fff5f5;color:#e05252;border:1px solid #ffcdd2;border-radius:8px;font-size:11px;padding:2px 10px;">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Position Title *</label>
                    <input type="text" name="positions[${idx}][title]" class="form-control" required
                        placeholder="e.g. Sales Associate" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Job Description *</label>
                    <textarea name="positions[${idx}][description]" class="form-control" rows="3" required
                        placeholder="Describe the job responsibilities..."
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"></textarea>
                </div>
                <input type="hidden" name="positions[${idx}][industry_group]" value="${COMPANY_INDUSTRY_GROUP}">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Nature of Work *</label>
                    <select name="positions[${idx}][type]" class="form-select" required
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        <option value="" disabled selected>Select type</option>
                        <option value="permanent">Permanent</option>
                        <option value="contractual">Contractual</option>
                        <option value="project_based">Project-based</option>
                        <option value="internship">Internship / OJT</option>
                        <option value="part_time">Part-time</option>
                        <option value="work_from_home">Work from home / online job</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Place of Work *</label>
                    <input type="text" name="positions[${idx}][location]" class="form-control" required
                        placeholder="e.g. Cagayan de Oro City" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Salary</label>
                    <input type="text" name="positions[${idx}][salary]" class="form-control"
                        placeholder="e.g. 15,000 / Negotiable" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Vacancy Count *</label>
                    <input type="number" name="positions[${idx}][slots]" class="form-control" required min="1"
                        placeholder="e.g. 3" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-12 deadline-field-wrap">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Deadline</label>
                    <input type="date" name="positions[${idx}][deadline]" class="form-control"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>

                <div class="col-12 mt-2 pt-2" style="border-top:1px dashed #a8e6cf;">
                    <div style="font-size:11px;font-weight:700;color:#2d7a5f;margin-bottom:8px;">IV. Qualification Requirements</div>
                   </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Work Experience (months)</label>
                    <input type="number" name="positions[${idx}][experience_months]" class="form-control" min="0"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Religion</label>
                    <input type="text" name="positions[${idx}][religion]" class="form-control" placeholder="e.g. Any"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Sex</label>
                    <select name="positions[${idx}][sex_preference]" class="form-select" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        <option value="Any" selected>No Preference</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Civil Status</label>
                    <select name="positions[${idx}][civil_status]" class="form-select" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        <option value="Any" selected>No Preference</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Other Qualifications</label>
                    <textarea name="positions[${idx}][other_qualifications]" class="form-control" rows="2"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Educational Level</label>
                    <select name="positions[${idx}][education_required]" class="form-select" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        <option value="">Any</option>
                        <option value="Elementary">Elementary</option>
                        <option value="High School">High School</option>
                        <option value="Senior High">Senior High</option>
                        <option value="Tertiary / College">Tertiary / College</option>
                        <option value="Graduate Studies">Graduate Studies</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Course / Major</label>
                    <input type="text" name="positions[${idx}][course_major]" class="form-control"
                        placeholder="e.g. Business Administration" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">License</label>
                    <input type="text" name="positions[${idx}][license]" class="form-control"
                        placeholder="e.g. Driver's License" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Eligibility</label>
                    <input type="text" name="positions[${idx}][eligibility]" class="form-control"
                        placeholder="e.g. Civil Service Eligible" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Certification</label>
                    <input type="text" name="positions[${idx}][certification]" class="form-control"
                        placeholder="e.g. TESDA NC II" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Language / Dialect Spoken</label>
                    <input type="text" name="positions[${idx}][language]" class="form-control"
                        placeholder="e.g. Filipino, English, Bisaya" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Preferred Residence</label>
                    <input type="text" name="positions[${idx}][preferred_residence]" class="form-control"
                        placeholder="e.g. Cagayan de Oro City" style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Accepts Disability?</label>
                    <div class="d-flex gap-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input req-disability-yes" type="radio" name="positions[${idx}][accepts_disability]" value="yes">
                            <label class="form-check-label" style="font-size:12px;color:#555;">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input req-disability-no" type="radio" name="positions[${idx}][accepts_disability]" value="no" checked>
                            <label class="form-check-label" style="font-size:12px;color:#555;">No</label>
                        </div>
                    </div>
                    <div class="req-disability-types-wrap d-none gap-3 flex-wrap">
                        ${['Visual','Hearing','Speech','Physical','Others'].map(t => `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="positions[${idx}][disability_types][]" value="${t}">
                            <label class="form-check-label" style="font-size:12px;color:#555;">${t}</label>
                        </div>`).join('')}
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Accepts</label>
                    <div class="d-flex gap-3 flex-wrap">
                        ${['PESO','SPES','GIP','JobStart Philippines','K-12 AMP','TraBAJO'].map(p => `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="positions[${idx}][accepts_programs][]" value="${p}">
                            <label class="form-check-label" style="font-size:12px;color:#555;">${p}</label>
                        </div>`).join('')}
                    </div>
                </div>
            </div>
        </div>`;
    }

    function addRequestPosition() {
        document.getElementById('requestPositionsContainer').insertAdjacentHTML('beforeend', buildRequestPositionRow(requestPositionCount));
        requestPositionCount++;
        updateRequestRemoveButtons();
    }

    function updateRequestRemoveButtons() {
        const rows = document.querySelectorAll('.request-position-row');
        document.querySelectorAll('.remove-request-position-btn').forEach(btn => {
            btn.style.display = rows.length > 1 ? 'inline-block' : 'none';
        });
    }

    function renumberRequestPositions() {
        document.querySelectorAll('.req-pos-num').forEach((el, i) => { el.textContent = i + 1; });
    }

    document.getElementById('addRequestPositionBtn').addEventListener('click', addRequestPosition);

    document.getElementById('requestPositionsContainer').addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-request-position-btn');
        if (btn && !btn.disabled) {
            btn.closest('.request-position-row').remove();
            renumberRequestPositions();
            updateRequestRemoveButtons();
        }
    });

    document.getElementById('requestPositionsContainer').addEventListener('change', function(e) {
        if (e.target.classList.contains('req-disability-yes') || e.target.classList.contains('req-disability-no')) {
            const wrap = e.target.closest('.request-position-row').querySelector('.req-disability-types-wrap');
            if (e.target.classList.contains('req-disability-yes') && e.target.checked) {
                wrap.classList.remove('d-none'); wrap.classList.add('d-flex');
            } else if (e.target.classList.contains('req-disability-no') && e.target.checked) {
                wrap.classList.remove('d-flex'); wrap.classList.add('d-none');
            }
        }
    });

    // ── Siguraduhon nga naa'y at least usa ka position sa pag-open sa modal ──
    document.getElementById('requestJobModal').addEventListener('show.bs.modal', function() {
        if (document.querySelectorAll('.request-position-row').length === 0) {
            addRequestPosition();
        }
    });

    // ── Check date availability (AJAX) — venue-aware, max 3 companies sa PESO Office ──
    const preferredDateInputRef = document.getElementById('preferredDateInput');

    function checkDateAvailability(date, venueType) {
        const note = document.getElementById('dateAvailabilityNote');
        if (venueType === 'other') {
            note.textContent = 'No limit for other venues.';
            note.style.color = '#2d7a5f';
            return;
        }
        note.textContent = 'Checking availability...';
        note.style.color = '#888';

        fetch(`{{ route('company.inhouse.checkDate') }}?date=${date}&venue_type=${venueType}`)
            .then(res => res.json())
            .then(data => {
                if (data.occupied) {
                    note.textContent = 'This date at PESO Office is fully booked (3/3 companies). Please choose another date or venue.';
                    note.style.color = '#e53935';
                } else {
                    note.textContent = `This date is available at PESO Office (${data.count}/3).`;
                    note.style.color = '#2d7a5f';
                }
            })
            .catch(() => { note.textContent = ''; });
    }

    if (preferredDateInputRef) {
        preferredDateInputRef.addEventListener('change', function() {
            const venueType = document.getElementById('venueTypeSelect')?.value || 'peso_office';
            checkDateAvailability(this.value, venueType);
        });
    }

    @if($showInitialVacancy ?? false)
    // ── Auto-show ang Initial Vacancy Modal pagka-load sa page ──
    document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('initialVacancyModal')).show();
    });

    let vacancyPositionCount = {{ count($employerNsrp->initial_vacancy_data ?? []) }};

    // ── Initial Vacancy — Schedule Type toggle (Preferred Date: office_based + inhouse; Venue: inhouse ra; Deadline: itago sa job_fair) ──
    document.querySelectorAll('.initial-schedule-type-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const dateFields  = document.getElementById('initialDateFields');
            const dateInput   = document.getElementById('initialPreferredDateInput');
            const dateHint    = document.getElementById('initialPreferredDateHint');
            const venueFields = document.getElementById('initialVenueFields');
            const venueSelect = document.getElementById('initialVenueTypeSelect');

            toggleDeadlineFieldsForScheduleType(this.value);

            if (this.value === 'job_fair') {
                dateFields.style.display = 'none';
                dateInput.removeAttribute('required');
                venueSelect.removeAttribute('required');
                document.getElementById('initialVenueAddressInput').removeAttribute('required');
                document.getElementById('initialVenueAddressWrap').style.display = 'none';
            } else {
                dateFields.style.display = 'flex';
                dateInput.setAttribute('required', 'required');

                if (this.value === 'inhouse') {
                    venueFields.style.display = 'block';
                    dateHint.style.display = 'block';
                    venueSelect.setAttribute('required', 'required');
                } else {
                    venueFields.style.display = 'none';
                    dateHint.style.display = 'none';
                    venueSelect.removeAttribute('required');
                    document.getElementById('initialVenueAddressInput').removeAttribute('required');
                    document.getElementById('initialVenueAddressWrap').style.display = 'none';
                }
            }
        });
    });

    const initialVenueTypeSelect = document.getElementById('initialVenueTypeSelect');
    if (initialVenueTypeSelect) {
        initialVenueTypeSelect.addEventListener('change', function() {
            const wrap = document.getElementById('initialVenueAddressWrap');
            const addressInput = document.getElementById('initialVenueAddressInput');
            if (this.value === 'other') {
                wrap.style.display = 'block';
                addressInput.setAttribute('required', 'required');
            } else {
                wrap.style.display = 'none';
                addressInput.removeAttribute('required');
            }
            if (initialDateInputRef && initialDateInputRef.value) {
                checkInitialDateAvailability(initialDateInputRef.value, this.value);
            }
        });
    }

    const initialDateInputRef = document.getElementById('initialPreferredDateInput');
    function checkInitialDateAvailability(date, venueType) {
        const note = document.getElementById('initialDateAvailabilityNote');
        if (venueType === 'other') {
            note.textContent = 'No limit for other venues.';
            note.style.color = '#2d7a5f';
            return;
        }
        note.textContent = 'Checking availability...';
        note.style.color = '#888';

        fetch(`{{ route('company.inhouse.checkDate') }}?date=${date}&venue_type=${venueType}`)
            .then(res => res.json())
            .then(data => {
                if (data.occupied) {
                    note.textContent = 'This date at PESO Office is fully booked (3/3 companies). Please choose another date or venue.';
                    note.style.color = '#e53935';
                } else {
                    note.textContent = `This date is available at PESO Office (${data.count}/3).`;
                    note.style.color = '#2d7a5f';
                }
            })
            .catch(() => { note.textContent = ''; });
    }

    if (initialDateInputRef) {
        initialDateInputRef.addEventListener('change', function() {
            const venueType = document.getElementById('initialVenueTypeSelect')?.value || 'peso_office';
            checkInitialDateAvailability(this.value, venueType);
        });
    }

    // ── "Update" button — i-enable ang tanan fields para ma-edit ──
    document.getElementById('updateVacancyBtn').addEventListener('click', function() {
        document.querySelectorAll('.vacancy-field').forEach(field => {
            field.removeAttribute('readonly');
            field.removeAttribute('disabled');
            field.style.background = '#fff';
            field.style.pointerEvents = 'auto';
            field.removeAttribute('tabindex');
        });
        document.querySelectorAll('.remove-position-btn').forEach(btn => btn.style.display = 'inline-block');
        document.getElementById('addVacancyPositionBtn').style.display = 'inline-block';
        this.style.display = 'none';
    });

    // ── Dynamic Add Position ──
    function buildVacancyPositionRow(idx) {
        return `
        <div class="position-row mb-3 p-3" style="border:1.5px solid #a8e6cf;border-radius:12px;background:#fff;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span style="font-size:12px;font-weight:700;color:#2d7a5f;">Position #<span class="pos-num">${idx + 1}</span></span>
                <button type="button" class="btn btn-sm remove-position-btn"
                    style="background:#fff5f5;color:#e05252;border:1px solid #ffcdd2;border-radius:8px;font-size:11px;padding:2px 10px;">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Position Title *</label>
                    <input type="text" name="positions[${idx}][title]" class="form-control vacancy-field" required
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Nature of Work *</label>
                    <select name="positions[${idx}][type]" class="form-select vacancy-field" required
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                        <option value="" disabled selected>Select type</option>
                        <option value="permanent">Permanent</option>
                        <option value="contractual">Contractual</option>
                        <option value="project_based">Project-based</option>
                        <option value="internship">Internship / OJT</option>
                        <option value="part_time">Part-time</option>
                        <option value="work_from_home">Work from home / online job</option>
                    </select>
                </div>
                <input type="hidden" name="positions[${idx}][industry_group]" value="${COMPANY_INDUSTRY_GROUP}">
                <div class="col-12">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Job Description *</label>
                    <textarea name="positions[${idx}][description]" rows="2" required class="form-control vacancy-field"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;"></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Place of Work *</label>
                    <input type="text" name="positions[${idx}][location]" class="form-control vacancy-field" required
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Salary</label>
                    <input type="text" name="positions[${idx}][salary]" class="form-control vacancy-field"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Vacancy Count *</label>
                    <input type="number" name="positions[${idx}][slots]" class="form-control vacancy-field" min="1" required
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-2 deadline-field-wrap">
                    <label class="form-label fw-semibold" style="color:#2d7a5f;font-size:12px;">Deadline</label>
                    <input type="date" name="positions[${idx}][deadline]" class="form-control vacancy-field"
                        style="border-color:#a8e6cf;font-size:13px;border-radius:8px;">
                </div>
            </div>
        </div>`;
    }

    document.getElementById('addVacancyPositionBtn').addEventListener('click', function() {
        document.getElementById('vacancyPositionsContainer').insertAdjacentHTML('beforeend', buildVacancyPositionRow(vacancyPositionCount));
        vacancyPositionCount++;
    });

    document.getElementById('vacancyPositionsContainer').addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-position-btn');
        if (btn) {
            btn.closest('.position-row').remove();
            document.querySelectorAll('.pos-num').forEach((el, i) => { el.textContent = i + 1; });
        }
    });

    // ── Confirm button SweetAlert confirmation ──
    document.getElementById('confirmVacancyBtn').addEventListener('click', function(e) {
        e.preventDefault();
        const form = document.getElementById('confirmVacancyForm');
        Swal.fire({
            title: 'Confirm Job Vacancies?',
            html: 'Are you sure you want to submit these job vacancy postings? Please review all details before confirming.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4dd9c0',
            cancelButtonColor: '#e53935',
            confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Yes, Confirm',
            cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
    @endif
</script>
@endsection