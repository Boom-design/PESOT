@extends('company.layouts.app')

@section('page-title', 'Applicants')

@section('content')

<div class="mb-4 fade-in">
    <a href="{{ route('company.jobseekers') }}" style="font-size:13px; color:var(--g-600); text-decoration:none;">
        <i class="ph ph-arrow-left me-1"></i> Back to Active Job Postings
    </a>
    <h5 class="fw-bold mt-2 mb-1" style="color:var(--g-700);">{{ $job->title }}</h5>
    <div class="d-flex gap-2 align-items-center">
        <span style="font-size:12px;color:var(--n-500);">
            <i class="ph ph-map-pin me-1" style="color:var(--g-600);"></i>{{ $job->location }}
        </span>
        <span class="badge-{{ $job->status }}">{{ ucfirst($job->status) }}</span>
        <span style="font-size:12px;color:var(--n-500);">
            <i class="ph ph-users-three me-1" style="color:var(--g-600);"></i>{{ $applicants->count() }} applicant(s)
        </span>
    </div>
</div>

{{-- ── SEARCH ── --}}
<div class="peso-card mb-3 fade-in">
    <div class="peso-card-body py-2">
        <input type="text" id="searchInput" class="peso-input w-100"
            placeholder="🔍  Search applicant name or email...">
    </div>
</div>

@if(!$actionsUnlocked)
<div class="alert alert-info mb-3" style="font-size:12px;border-radius:10px;">
    <i class="ph-fill ph-info me-1"></i>
    Hire, Reject, and Waiting actions will be available once the
    {{ $job->schedule_type === 'inhouse' ? 'in-house' : 'company' }} interview date
    ({{ $job->interview_date ? $job->interview_date->format('M d, Y') : 'N/A' }}) arrives.
</div>
@endif

{{-- ── TABS ── --}}
<ul class="nav nav-tabs mb-3" role="tablist" style="border-bottom:2px solid var(--n-200);">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#allApplicantsPane"
            type="button" role="tab" style="color:var(--g-700);font-size:13px;">
            All Applicants
            <span class="badge rounded-pill ms-1" style="background:var(--n-50);color:var(--g-700);">{{ $applicants->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#qualifiedPane"
            type="button" role="tab" style="color:var(--n-500);font-size:13px;">
            Qualified Applicants
            <span class="badge rounded-pill ms-1" style="background:var(--warn-bg);color:var(--warn);">{{ $qualifiedApplicants->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#highlyQualifiedPane"
            type="button" role="tab" style="color:var(--n-500);font-size:13px;">
            Highly Qualified Applicants
            <span class="badge rounded-pill ms-1" style="background:var(--g-50);color:var(--g-700);">{{ $highlyQualifiedApplicants->count() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content">
    {{-- ── ALL APPLICANTS TAB (existing Hire/Reject/Waiting) ── --}}
    <div class="tab-pane fade show active" id="allApplicantsPane" role="tabpanel">
        <div class="peso-card fade-in-1">
                <div class="table-responsive">
                    <table class="table peso-table mb-0" id="applicantsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Applicant Name</th>
                                <th>Contact</th>
                                <th>Applied On</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applicants as $i => $application)
                            <tr class="applicant-row">
                                <td style="color:var(--n-500);">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;background:var(--g-600);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                            {{ strtoupper(substr($application->jobseeker->first_name ?? 'J', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:13px;">
                                                {{ $application->jobseeker->first_name ?? '' }} {{ $application->jobseeker->surname ?? 'None' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="color:var(--n-500);">
                                    <div>{{ $application->jobseeker->contact_number ?? 'None' }}</div>
                                    <div style="font-size:11px;">{{ $application->jobseeker->reg_email ?? 'None' }}</div>
                                </td>
                                <td style="color:var(--n-500);">{{ $application->created_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge-{{ $application->status }}">
                                        {{ ucfirst($application->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{-- Hired and rejected keep their buttons. An
                                         applicant who never reports for work has to
                                         be moved back off "hired", or the slot stays
                                         spent on someone who is not there. --}}
                                    @if(!$actionsUnlocked)
                                        <span style="font-size:12px;color:var(--n-500);">
                                            <i class="ph ph-clock me-1"></i>Locked until interview date
                                        </span>
                                    @else
                                    <div class="d-flex gap-1">
                                        {{-- Hired --}}
                                        <form method="POST" action="{{ route('company.applicants.status', $application->job_matching_id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="hired">
                                            <button type="submit" class="btn btn-sm fw-semibold"
                                                style="font-size:11px;border-radius:8px;padding:4px 10px;
                                                {{ $application->status === 'hired'
                                                    ? 'background:var(--g-600);color:#fff;border:none;'
                                                    : 'background:var(--g-50);color:var(--g-700);border:1px solid var(--n-200);' }}"
                                                onclick="return confirm('Mark this applicant as HIRED? They will be notified. You can change this later if they do not report for work.')">
                                                <i class="ph-fill ph-check-circle me-1"></i>Hired
                                            </button>
                                        </form>
                                        {{-- Waiting --}}
                                        <form method="POST" action="{{ route('company.applicants.status', $application->job_matching_id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="waiting">
                                            <button type="submit" class="btn btn-sm fw-semibold"
                                                style="font-size:11px;border-radius:8px;padding:4px 10px;
                                                {{ $application->status === 'waiting'
                                                    ? 'background:var(--warn);color:#fff;border:none;'
                                                    : 'background:var(--warn-bg);color:var(--warn);border:1px solid var(--warn-br);' }}"
                                                title="Mark as Waiting">
                                                <i class="ph ph-hourglass-medium me-1"></i>Waiting
                                            </button>
                                        </form>
                                        {{-- Rejected --}}
                                        <form method="POST" action="{{ route('company.applicants.status', $application->job_matching_id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm fw-semibold"
                                                style="font-size:11px;border-radius:8px;padding:4px 10px;
                                                {{ $application->status === 'rejected'
                                                    ? 'background:var(--danger);color:#fff;border:none;'
                                                    : 'background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger-br);' }}"
                                                onclick="return confirm('Mark this applicant as REJECTED? They will be notified. You can change this later.')">
                                                <i class="ph-fill ph-x-circle me-1"></i>Rejected
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                                 gihapon ug ang pahina dili mag-usab ug porma. --}}
                            <tr>
                                <td colspan="6" class="text-center"
                                    style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                    <i class="ph ph-users-three me-1"
                                       style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                                    Applicants will appear here once jobseekers apply.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
        </div>
    </div>

    {{-- ── QUALIFIED APPLICANTS TAB (50–74%) ── --}}
    <div class="tab-pane fade" id="qualifiedPane" role="tabpanel">
        <div class="peso-card fade-in-1">
                <div class="table-responsive">
                    <table class="table peso-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Applicant</th>
                                <th>Contact</th>
                                <th style="text-align:center;">Match %</th>
                                <th style="text-align:center;">Date Applied</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($qualifiedApplicants as $i => $app)
                            <tr>
                                <td style="color:var(--n-500);">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $app->jobseeker->first_name ?? '' }} {{ $app->jobseeker->surname ?? 'None' }}</td>
                                <td style="color:var(--n-500);">
                                    <div>{{ $app->jobseeker->contact_number ?? 'None' }}</div>
                                    <div style="font-size:11px;">{{ $app->jobseeker->reg_email ?? 'None' }}</div>
                                </td>
                                <td style="text-align:center;font-weight:700;color:var(--warn);">{{ $app->match_percentage }}%</td>
                                <td style="text-align:center;color:var(--n-500);">{{ $app->created_at->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                                 gihapon ug ang pahina dili mag-usab ug porma. --}}
                            <tr>
                                <td colspan="5" class="text-center"
                                    style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                    <i class="ph ph-user-minus me-1"
                                       style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                                    Applicants with 50–74% match will appear here.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
        </div>
    </div>

    {{-- ── HIGHLY QUALIFIED APPLICANTS TAB (75–100%) ── --}}
    <div class="tab-pane fade" id="highlyQualifiedPane" role="tabpanel">
        <div class="peso-card fade-in-1">
                <div class="table-responsive">
                    <table class="table peso-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Applicant</th>
                                <th>Contact</th>
                                <th style="text-align:center;">Match %</th>
                                <th style="text-align:center;">Date Applied</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($highlyQualifiedApplicants as $i => $app)
                            <tr>
                                <td style="color:var(--n-500);">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $app->jobseeker->first_name ?? '' }} {{ $app->jobseeker->surname ?? 'None' }}</td>
                                <td style="color:var(--n-500);">
                                    <div>{{ $app->jobseeker->contact_number ?? 'None' }}</div>
                                    <div style="font-size:11px;">{{ $app->jobseeker->reg_email ?? 'None' }}</div>
                                </td>
                                <td style="text-align:center;font-weight:700;color:var(--g-700);">{{ $app->match_percentage }}%</td>
                                <td style="text-align:center;color:var(--n-500);">{{ $app->created_at->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                                 gihapon ug ang pahina dili mag-usab ug porma. --}}
                            <tr>
                                <td colspan="5" class="text-center"
                                    style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                    <i class="ph ph-user-minus me-1"
                                       style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                                    Applicants with 75–100% match will appear here.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.applicant-row').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
@endsection