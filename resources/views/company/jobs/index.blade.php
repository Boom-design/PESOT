@extends('company.layouts.app')

@section('page-title', ' ')

@section('content')

{{-- ── HEADER ── --}}
<div class="d-flex align-items-center justify-content-between mb-4 fade-in">
    <div>
        <h5 class="fw-bold mb-1" style="color: var(--g-700);">Removed Postings</h5>
        <p class="mb-0" style="font-size: 13px; color: var(--n-500);">
            Jobs PESO took down, and why. Fix what they asked for and post the job again.
        </p>
    </div>
    <a href="#" class="btn btn-peso" data-bs-toggle="modal" data-bs-target="#requestJobModal">
        <i class="ph ph-plus me-1"></i> Post a Job
    </a>
</div>

{{-- ── INITIAL VACANCY CONFIRMATION MODAL (auto-popup gikan sa registration) ── --}}
@if($showInitialVacancy ?? false)
<div class="modal fade" id="initialVacancyModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" style="max-height:90vh;margin-top:5vh;margin-bottom:5vh;">
        <div class="modal-content" style="border-radius:16px;border:none;max-height:90vh;display:flex;flex-direction:column;">
            <div class="modal-header" style="background:var(--g-600);flex-shrink:0;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="ph ph-briefcase me-2"></i>Confirm Your Job Vacancy Posting
                </h5>
            </div>
            <form id="confirmVacancyForm" action="{{ route('company.jobs.confirmInitial') }}" method="POST"
                style="display:flex;flex-direction:column;overflow:hidden;flex:1;">
                @csrf
                <div class="modal-body p-4" style="overflow-y:auto;flex:1;">

                    <div class="alert alert-info" style="font-size:13px;border-radius:10px;">
                        <i class="ph-fill ph-info me-1"></i>
                        The following details were entered during your registration.
                        Review them, then click <strong>"Confirm"</strong> if everything is correct,
                        or click <strong>"Update"</strong> if you want to make changes first.
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color:var(--g-700);font-size:13px;">Schedule Type *</label>
                        <div class="row g-2">
                            @foreach([
                                'company_interview' => ['Company Interview', 'ph ph-buildings'],
                                'inhouse'      => ['In-house', 'ph ph-calendar-check'],
                                'job_fair'     => ['Job Fair', 'ph ph-calendar-dots'],
                            ] as $val => $opt)
                            <div class="col-md-4">
                                <div class="form-check p-3" style="border:1px solid var(--n-200);border-radius:10px;">
                                    <input class="form-check-input initial-schedule-type-radio" type="radio"
                                        name="schedule_type" value="{{ $val }}" id="initial_sched_{{ $val }}"
                                        {{ $val === 'company_interview' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="initial_sched_{{ $val }}"
                                        style="font-size:12px;color:var(--g-700);font-weight:600;">
                                        <i class="{{ $opt[1] }} me-1"></i>{{ $opt[0] }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div id="initialDateFields" class="row g-3 mb-4" style="display:flex;">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;" id="initialPreferredDateLabel">Preferred Date *</label>
                            {{-- Ang makita nga input kay pang-display ra; ang gipadala kay
                                 ang duha ka hidden. Sa in-house, range ni — ang employer
                                 mo-reserba sa kada adlaw sulod niini. --}}
                            <input type="text" id="initialPreferredDateInput" class="form-control" autocomplete="off"
                                placeholder="Select a date"
                                style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                            <input type="hidden" name="preferred_date"     id="initialPreferredDateValue">
                            <input type="hidden" name="preferred_date_end" id="initialPreferredDateEndValue">
                            <small id="initialPreferredDateHint" style="font-size:11px;color:var(--n-500);display:none;margin-top:4px;">
                                Pick the days you are available. All of them are held for you —
                                no other company can book them, and you choose which one to
                                interview on. Interview time is between 8:00 AM – 5:00 PM.
                            </small>
                            <div id="initialDateAvailabilityNote" style="font-size:11px;margin-top:4px;"></div>
                        </div>
                        <div class="col-md-6" id="initialVenueFields" style="display:none;">
                            <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Venue *</label>
                            <select name="venue_type" id="initialVenueTypeSelect" class="form-select"
                                style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                                <option value="peso_office">PESO Office</option>
                                <option value="other">Other Venue</option>
                            </select>
                            <small style="font-size:11px;color:var(--n-500);display:block;margin-top:4px;">
                                The PESO Office takes {{ config('peso.schedule.inhouse_daily_companies') }} companies a day. Other Venue is your own place —
                                no limit, so a date that is full here is still yours to use there.
                            </small>
                        </div>
                        <div class="col-12" id="initialVenueAddressWrap" style="display:none;">
                            <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Venue Address *</label>
                            <input type="text" name="venue_address" id="initialVenueAddressInput" class="form-control"
                                placeholder="e.g. SM CDO Downtown Premier, 3rd Floor"
                                style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                        </div>
                    </div>

                    <div id="vacancyPositionsContainer">
                        @foreach(($employerNsrp->initial_vacancy_data ?? []) as $i => $pos)
                        <div class="position-row mb-3 p-3" style="border:1px solid var(--n-200);border-radius:12px;background:var(--n-50);">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span style="font-size:12px;font-weight:700;color:var(--g-700);">Position #{{ $i + 1 }}</span>
                                @if($i > 0)
                                <button type="button" class="btn btn-sm remove-position-btn"
                                    style="background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger-br);border-radius:8px;font-size:11px;padding:2px 10px;">
                                    <i class="ph-fill ph-trash"></i>
                                </button>
                                @endif
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Position Title *</label>
                                    <input type="text" name="positions[{{ $i }}][title]" class="form-control vacancy-field"
                                        value="{{ $pos['title'] ?? '' }}" readonly required
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Nature of Work *</label>
                                    <select name="positions[{{ $i }}][type]" class="form-select vacancy-field vacancy-locked" required
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);pointer-events:none;" tabindex="-1">
                                        @foreach(['permanent'=>'Permanent','contractual'=>'Contractual','project_based'=>'Project-based','internship'=>'Internship / OJT','part_time'=>'Part-time','work_from_home'=>'Work from home / online job'] as $val => $label)
                                        <option value="{{ $val }}" {{ ($pos['type'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Job Description *</label>
                                    <textarea name="positions[{{ $i }}][description]" rows="2" readonly required
                                        class="form-control vacancy-field"
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">{{ $pos['description'] ?? '' }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Place of Work *</label>
                                    <input type="text" name="positions[{{ $i }}][location]" class="form-control vacancy-field"
                                        value="{{ $pos['location'] ?? '' }}" readonly required
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Salary</label>
                                    <input type="text" name="positions[{{ $i }}][salary]" class="form-control vacancy-field"
                                        value="{{ $pos['salary'] ?? '' }}" readonly
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Vacancy Count *</label>
                                    <input type="number" name="positions[{{ $i }}][slots]" class="form-control vacancy-field"
                                        value="{{ $pos['slots'] ?? '' }}" readonly required min="1"
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">
                                </div>
                                <div class="col-md-2 deadline-field-wrap">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Deadline</label>
                                    <input type="date" name="positions[{{ $i }}][deadline]" class="form-control vacancy-field"
                                        value="{{ $pos['deadline'] ?? '' }}" readonly
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">
                                </div>

                                <div class="col-12 mt-2 pt-2" style="border-top:1px dashed var(--n-200);">
                                    <div style="font-size:11px;font-weight:700;color:var(--g-700);margin-bottom:6px;">IV. Qualification Requirements</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Work Experience (months)</label>
                                    <input type="number" name="positions[{{ $i }}][experience_months]" class="form-control vacancy-field"
                                        value="{{ $pos['experience_months'] ?? '' }}" readonly
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Religion</label>
                                    <input type="text" name="positions[{{ $i }}][religion]" class="form-control vacancy-field"
                                        value="{{ $pos['religion'] ?? '' }}" readonly
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Sex</label>
                                    <select name="positions[{{ $i }}][sex_preference]" class="form-select vacancy-field vacancy-locked"
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);pointer-events:none;" tabindex="-1">
                                        @foreach(['Any'=>'No Preference','Male'=>'Male','Female'=>'Female'] as $val=>$label)
                                        <option value="{{ $val }}" {{ ($pos['sex_preference'] ?? 'Any') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Civil Status</label>
                                    <select name="positions[{{ $i }}][civil_status]" class="form-select vacancy-field vacancy-locked"
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);pointer-events:none;" tabindex="-1">
                                        @foreach(['Any'=>'No Preference','Single'=>'Single','Married'=>'Married'] as $val=>$label)
                                        <option value="{{ $val }}" {{ ($pos['civil_status'] ?? 'Any') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Other Qualifications</label>
                                    <textarea name="positions[{{ $i }}][other_qualifications]" rows="2" readonly
                                        class="form-control vacancy-field"
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">{{ $pos['other_qualifications'] ?? '' }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Educational Level</label>
                                    <input type="text" name="positions[{{ $i }}][education_required]" class="form-control vacancy-field"
                                        value="{{ $pos['education_required'] ?? '' }}" readonly
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Course/Major</label>
                                    <input type="text" name="positions[{{ $i }}][course_major]" class="form-control vacancy-field"
                                        value="{{ $pos['course_major'] ?? '' }}" readonly
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">License</label>
                                    <input type="text" name="positions[{{ $i }}][license]" class="form-control vacancy-field"
                                        value="{{ $pos['license'] ?? '' }}" readonly
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Eligibility</label>
                                    <input type="text" name="positions[{{ $i }}][eligibility]" class="form-control vacancy-field"
                                        value="{{ $pos['eligibility'] ?? '' }}" readonly
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Certification</label>
                                    <input type="text" name="positions[{{ $i }}][certification]" class="form-control vacancy-field"
                                        value="{{ $pos['certification'] ?? '' }}" readonly
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Language/Dialect</label>
                                    <input type="text" name="positions[{{ $i }}][language]" class="form-control vacancy-field"
                                        value="{{ $pos['language'] ?? '' }}" readonly
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Preferred Residence</label>
                                    <input type="text" name="positions[{{ $i }}][preferred_residence]" class="form-control vacancy-field"
                                        value="{{ $pos['preferred_residence'] ?? '' }}" readonly
                                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;background:var(--n-50);">
                                </div>
                                @if(!empty($pos['job_image']))
                                <div class="col-12 mt-2 pt-2" style="border-top:1px dashed var(--n-200);">
                                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">
                                        <i class="ph ph-image me-1" style="color:var(--g-600);"></i>Hiring Poster / Image
                                    </label>
                                    <div class="mt-1">
                                        <img src="{{ Storage::url($pos['job_image']) }}" alt="Job Image"
                                             style="max-width:200px;max-height:150px;border-radius:8px;border:1px solid var(--n-200);object-fit:cover;">
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
                        style="display:none;border:1px dashed var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;font-size:12px;padding:6px 16px;">
                        <i class="ph ph-plus-circle me-1"></i> Add Position
                    </button>

                </div>
                <div class="modal-footer" style="border-top:1px solid var(--n-200);flex-shrink:0;">
                    <button type="button" id="updateVacancyBtn" class="btn btn-sm fw-semibold"
                        style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;padding:8px 20px;">
                        <i class="ph ph-note-pencil me-1"></i> Update
                    </button>
                    <button type="submit" id="confirmVacancyBtn" class="btn btn-sm fw-semibold"
                        style="background:var(--g-600);color:#fff;border:none;border-radius:8px;padding:8px 24px;">
                        <i class="ph-fill ph-check-circle me-1"></i> Confirm
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
                <i class="ph ph-briefcase"></i>
            </div>
            <h6>Nothing was removed</h6>
            <p>None of your jobs have been taken down. You will see them here if that ever happens.</p>
            <a href="{{ route('company.jobseekers') }}" class="btn btn-peso mt-3">
                <i class="ph ph-briefcase me-1"></i> Go to Active Job Postings
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
                        <td style="color:var(--n-500);">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $job->title }}</td>
                        <td><i class="ph ph-map-pin me-1" style="color:var(--g-600);"></i>{{ $job->location }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $job->type)) }}</td>
                        <td>{{ $job->slots }}</td>
                        <td>{{ $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('M d, Y') : 'None' }}</td>
                        <td>@include('partials.job-status-badge', ['job' => $job])</td>
                        <td>
                            <div class="d-flex gap-1">
                                {{-- PESO interview 2026-08-13: ma-edit samtang buhi pa ang
                                     posting — apil ang active nga posting, kay naa may
                                     employer nga mo-hupay sa qualification kung walay
                                     mo-apply (pananglit licensed CPA → accounting graduate).
                                     Ang nahuman na (filled/expired/closed) dili na. --}}
                                @if($job->is_editable)
                                <a href="{{ route('company.jobs.edit', $job->job_qualifications_id) }}"
                                   class="btn btn-sm btn-peso-outline py-1 px-2" style="font-size:11px;"
                                   title="Edit">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>
                                @else
                                <button type="button" class="btn btn-sm py-1 px-2" disabled
                                    style="font-size:11px;background:var(--n-100);color:var(--n-400);border:1px solid var(--n-200);border-radius:8px;cursor:not-allowed;"
                                    title="{{ $job->lifecycle_block_reason }}">
                                    <i class="ph ph-pencil-simple"></i>
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

@include('company.partials.request-job-modal')

{{-- ── DELETE MODAL ── --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none; box-shadow: 0 8px 32px rgba(0,0,0,0.12);">
            <div class="modal-body text-center p-4">
                <div style="width:60px;height:60px;background:var(--danger-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="ph ph-trash" style="font-size:24px;color:var(--danger);"></i>
                </div>
                <h6 class="fw-bold mb-2" style="color:var(--g-700);">Delete Job Post?</h6>
                <p style="font-size:13px;color:var(--n-500);" id="deleteJobName">Are you sure you want to delete this job?</p>
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <button class="btn btn-peso-outline px-4" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm px-4 py-2"
                            style="background:var(--danger);color:#fff;border:none;border-radius:10px;font-weight:600;">
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
@include('company.partials.request-job-modal-scripts')
<script>

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


    @if($showInitialVacancy ?? false)
    // ── Auto-show ang Initial Vacancy Modal pagka-load sa page ──
    document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('initialVacancyModal')).show();
    });

    let vacancyPositionCount = {{ count($employerNsrp->initial_vacancy_data ?? []) }};

    // ── Initial Vacancy — Schedule Type toggle (Preferred Date: company_interview + inhouse; Venue: inhouse ra; Deadline: itago sa job_fair) ──
    document.querySelectorAll('.initial-schedule-type-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const dateFields  = document.getElementById('initialDateFields');
            const dateInput   = document.getElementById('initialPreferredDateInput');
            const dateHint    = document.getElementById('initialPreferredDateHint');
            const venueFields = document.getElementById('initialVenueFields');
            const venueSelect = document.getElementById('initialVenueTypeSelect');

            // Kaugalingon nga toggle, gi-scope sa initial-vacancy modal ra: ang
            // Post a Job nga modal daghan nang schedule type nga mapili,
            // mao nga lahi na ang rule niya sa Deadline field.
            document.querySelectorAll('#initialVacancyModal .deadline-field-wrap').forEach(function(wrap) {
                wrap.style.display = this.value === 'job_fair' ? 'none' : 'block';
            }, this);

            if (this.value === 'job_fair') {
                dateFields.style.display = 'none';
                dateInput.removeAttribute('required');
                venueSelect.removeAttribute('required');
                document.getElementById('initialVenueAddressInput').removeAttribute('required');
                document.getElementById('initialVenueAddressWrap').style.display = 'none';
            } else {
                dateFields.style.display = 'flex';
                dateInput.setAttribute('required', 'required');

                // In-house kay window — mahimong daghang adlaw ang itanyag ug
                // ang PESO mopili. Ang company interview kay usa ra ka adlaw.
                if (window.pesoSetDateMode) {
                    window.pesoSetDateMode('initialPreferredDateInput', this.value === 'inhouse' ? 'range' : 'single');
                }
                document.getElementById('initialPreferredDateLabel').textContent =
                    this.value === 'inhouse' ? 'Available Dates *' : 'Preferred Date *';

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
            // Ang puno nga adlaw naka-disable sa PESO Office ra — i-redraw
            // aron mapili siya balik kung Other Venue na ang gipili.
            if (window.pesoPickers && window.pesoPickers.initialPreferredDateInput) {
                window.pesoPickers.initialPreferredDateInput.redraw();
            }
            checkInitialDateAvailability(this.value);
        });
    }

    const initialDateInputRef = document.getElementById('initialPreferredDateInput');

    // Ang tinuod nga petsa naa sa hidden nga field, dili sa makita nga input:
    // ang gipakita nga teksto sa range kay "2026-08-13 to 2026-08-17".
    function initialPickedDates() {
        return {
            start: document.getElementById('initialPreferredDateValue')?.value || '',
            end:   document.getElementById('initialPreferredDateEndValue')?.value || '',
        };
    }

    function checkInitialDateAvailability(venueType) {
        const note = document.getElementById('initialDateAvailabilityNote');
        const { start, end } = initialPickedDates();
        if (!start) { note.textContent = ''; return; }

        note.textContent = 'Checking availability...';
        note.style.color = 'var(--n-500)';

        const query = `date=${start}&date_end=${end || start}&venue_type=${venueType}`;

        fetch(`{{ route('company.inhouse.checkDate') }}?${query}`)
            .then(res => res.json())
            .then(data => {
                // Ang miting sa PESO mo-una: kung wala nay libre nga adlaw
                // sulod sa window, wala nay pulos ang bilang sa venue.
                if (data.office_full) {
                    // Bisan usa ka adlaw nga puliki mobabag sa tibuok range:
                    // ang employer mo-reserba man sa kada adlaw sulod niini.
                    note.innerHTML = (data.office_note || 'PESO is not available on those dates.')
                        + ' Please pick dates that do not include it.';
                    note.style.color = 'var(--danger)';
                    return;
                }

                const officeNote = data.office_note ? ' ' + data.office_note : '';

                if (venueType === 'other') {
                    note.textContent = 'No limit for other venues.' + officeNote;
                    note.style.color = officeNote ? 'var(--warn)' : 'var(--g-700)';
                    return;
                }

                if (data.occupied) {
                    // Ang limit kay sa lawak, dili sa petsa — Other Venue ang
                    // solusyon kung ang petsa ang gusto gyud sa employer.
                    const cap = data.limit ?? INHOUSE_DAILY_COMPANIES;
                    note.innerHTML = `PESO Office is full (${cap}/${cap} companies) on <strong>`
                        + (data.full_days || []).join(', ') + '</strong>. '
                        + 'Switch the venue to <strong>Other Venue</strong> to keep these dates — '
                        + 'there is no company limit outside the PESO Office.';
                    note.style.color = 'var(--danger)';
                } else {
                    const cap = data.limit ?? INHOUSE_DAILY_COMPANIES;
                    note.textContent = `Available at PESO Office (${data.count}/${cap} at the busiest day).` + officeNote;
                    note.style.color = officeNote ? 'var(--warn)' : 'var(--g-700)';
                }
            })
            .catch(() => { note.textContent = ''; });
    }

    if (initialDateInputRef) {
        initialDateInputRef.addEventListener('change', function() {
            const venueType = document.getElementById('initialVenueTypeSelect')?.value || 'peso_office';
            checkInitialDateAvailability(venueType);
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
        <div class="position-row mb-3 p-3" style="border:1px solid var(--n-200);border-radius:12px;background:#fff;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span style="font-size:12px;font-weight:700;color:var(--g-700);">Position #<span class="pos-num">${idx + 1}</span></span>
                <button type="button" class="btn btn-sm remove-position-btn"
                    style="background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger-br);border-radius:8px;font-size:11px;padding:2px 10px;">
                    <i class="ph-fill ph-trash"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Position Title *</label>
                    <input type="text" name="positions[${idx}][title]" class="form-control vacancy-field" required
                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Nature of Work *</label>
                    <select name="positions[${idx}][type]" class="form-select vacancy-field" required
                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
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
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Job Description *</label>
                    <textarea name="positions[${idx}][description]" rows="2" required class="form-control vacancy-field"
                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;"></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Place of Work *</label>
                    <input type="text" name="positions[${idx}][location]" class="form-control vacancy-field" required
                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Salary</label>
                    <input type="text" name="positions[${idx}][salary]" class="form-control vacancy-field"
                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Vacancy Count *</label>
                    <input type="number" name="positions[${idx}][slots]" class="form-control vacancy-field" min="1" required
                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
                </div>
                <div class="col-md-2 deadline-field-wrap">
                    <label class="form-label fw-semibold" style="color:var(--g-700);font-size:12px;">Deadline</label>
                    <input type="date" name="positions[${idx}][deadline]" class="form-control vacancy-field"
                        style="border-color:var(--n-200);font-size:13px;border-radius:8px;">
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
            confirmButtonColor: '#28812F',
            cancelButtonColor: 'var(--danger)',
            confirmButtonText: '<i class="ph-fill ph-check-circle me-1"></i> Yes, Confirm',
            cancelButtonText: '<i class="ph ph-x-circle me-1"></i> Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
    @endif
</script>
@endsection