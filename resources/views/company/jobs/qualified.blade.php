@extends('company.layouts.app')

@section('page-title', 'Jobseekers')

@section('content')

<div class="mb-4 fade-in d-flex align-items-end justify-content-between flex-wrap gap-3">
    <div>
        <a href="{{ route('company.jobs') }}" style="font-size:13px;color:#4dd9c0;text-decoration:none;">
            <i class="bi bi-arrow-left me-1"></i> Back to Job Posts
        </a>
        <h5 class="fw-bold mt-2 mb-1" style="color:#2d7a5f;">
            <i class="bi bi-person-check-fill me-2" style="color:#4dd9c0;"></i>
            Jobseekers
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">
            {{ $job->title }}
            @if($isInhouse)
                <span style="color:#888;">
                    — In-house Date: {{ $job->preferred_date ? \Carbon\Carbon::parse($job->preferred_date)->format('M d, Y') : 'Not set' }}
                </span>
            @endif
        </p>
    </div>
    <input type="text" id="searchInput" class="peso-input"
        style="width:260px;"
        placeholder="🔍  Search jobseeker name...">
</div>

@if($isInhouse && !$actionsUnlocked)
<div class="alert alert-info mb-3" style="font-size:12px;border-radius:10px;">
    <i class="bi bi-info-circle-fill me-1"></i>
    Hire, Reject, and Waiting actions will be available once the in-house interview date
    ({{ $job->preferred_date ? \Carbon\Carbon::parse($job->preferred_date)->format('M d, Y') : 'N/A' }}) arrives.
</div>
@endif

{{-- TABS --}}
<ul class="nav nav-tabs mb-3" id="qualificationTabs" role="tablist" style="border-bottom:2px solid #e8f5f0;">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" data-bs-toggle="tab"
            data-bs-target="#allPane" type="button" role="tab"
            style="color:#2d7a5f;font-size:13px;">
            All Jobseekers
            <span class="badge rounded-pill ms-1" style="background:#f0f9f6;color:#2d7a5f;">{{ $totalAll }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" data-bs-toggle="tab"
            data-bs-target="#highlyQualifiedPane" type="button" role="tab"
            style="color:#888;font-size:13px;">
            Highly Qualified Jobseekers
            <span class="badge rounded-pill ms-1" style="background:#e8f8f3;color:#2d7a5f;">{{ $totalHighly }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" data-bs-toggle="tab"
            data-bs-target="#qualifiedPane" type="button" role="tab"
            style="color:#888;font-size:13px;">
            Qualified Jobseekers
            <span class="badge rounded-pill ms-1" style="background:#fff8e1;color:#f59e0b;">{{ $totalQualified }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" data-bs-toggle="tab"
            data-bs-target="#notQualifiedPane" type="button" role="tab"
            style="color:#888;font-size:13px;">
            Not Qualified Jobseekers
            <span class="badge rounded-pill ms-1" style="background:#fff5f5;color:#e05252;">{{ $totalNotQualified }}</span>
        </button>
    </li>
</ul>

@php
    
    $renderTable = function ($list, $showActions) use ($actionsUnlocked, $isInhouse) {
        // wala ni magamit — gina-render diretso sa ubos, kini na lang stub para klaro sa purpose
    };
@endphp

<div class="tab-content">
    {{-- ── ALL JOBSEEKERS TAB ── --}}
    <div class="tab-pane fade show active" id="allPane" role="tabpanel">
        <div class="peso-card fade-in-1">
            @if($applicants->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-person-x"></i></div>
                    <h6>No jobseekers yet</h6>
                    <p>Jobseekers who applied for this job will appear here.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table peso-table mb-0 qualified-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Jobseeker</th>
                                <th>Contact</th>
                                <th style="text-align:center;">Match %</th>
                                @if($isInhouse || $isOfficeBased)<th style="text-align:center;">Participation</th>@endif
                                <th style="text-align:center;">Status</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applicants as $i => $app)
                            @php
                                $participationValue = $isInhouse ? $app->inhouse_participation : ($isOfficeBased ? $app->office_participation : null);
                                $isDeclined = in_array($participationValue, ['declined']);
                            @endphp
                            <tr class="qualified-row">
                                <td style="color:#888;">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;background:linear-gradient(135deg,#90d870,#4dd9c0);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                            {{ strtoupper(substr($app->jobseeker->first_name ?? 'J', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:13px;color:#2d7a5f;">
                                                {{ $app->jobseeker->first_name ?? '' }} {{ $app->jobseeker->surname ?? '—' }}
                                            </div>
                                            <div style="font-size:11px;color:#888;">{{ $app->jobseeker->reg_email ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:12px;color:#555;"><i class="bi bi-telephone me-1" style="color:#4dd9c0;"></i>{{ $app->jobseeker->contact_number ?? '—' }}</div>
                                    <div style="font-size:12px;color:#888;"><i class="bi bi-envelope me-1" style="color:#4dd9c0;"></i>{{ $app->jobseeker->reg_email ?? '—' }}</div>
                                </td>
                                <td style="text-align:center;">
                                    <span class="fw-bold" style="font-size:15px;color:{{ ($app->match_percentage ?? 0) >= 75 ? '#2d7a5f' : (($app->match_percentage ?? 0) >= 50 ? '#f59e0b' : '#e05252') }};">
                                        {{ $app->match_percentage }}%
                                    </span>
                                </td>
                                @if($isInhouse || $isOfficeBased)
                                <td style="text-align:center;">
                                    @if($participationValue === 'accepted')
                                        <span class="badge fw-semibold" style="background:#e8f8f3;color:#2d7a5f;font-size:10px;padding:3px 8px;border-radius:20px;">Accepted</span>
                                    @elseif($participationValue === 'declined')
                                        <span class="badge fw-semibold" style="background:#fff5f5;color:#e05252;font-size:10px;padding:3px 8px;border-radius:20px;">Declined</span>
                                    @else
                                        <span class="badge fw-semibold" style="background:#fff8e1;color:#f59e0b;font-size:10px;padding:3px 8px;border-radius:20px;">Pending</span>
                                    @endif
                                </td>
                                @endif
                                <td style="text-align:center;">
                                    @if($app->status === 'rejected' || $app->status === 'hired')
                                        <span class="badge-{{ $app->status }}">{{ ucfirst($app->status) }}</span>
                                    @endif
                                </td>
                                <td style="text-align:center;">
                                    @if($isDeclined)
                                        <span style="font-size:11px;color:#888;">— No action —</span>
                                    @else
                                        @include('company.jobs.partials.applicant-actions', ['app' => $app])
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ── HIGHLY QUALIFIED TAB ── --}}
    <div class="tab-pane fade" id="highlyQualifiedPane" role="tabpanel">
        <div class="peso-card fade-in-1">
            @if($highlyQualified->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-person-x"></i></div>
                    <h6>No highly qualified jobseekers yet</h6>
                    <p>@if($isInhouse) Jobseekers who accepted the in-house interview with 75–100% match will appear here. @else Jobseekers with 75–100% match will appear here. @endif</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table peso-table mb-0 qualified-table">
                        <thead>
                            <tr>
                                <th>#</th><th>Jobseeker</th><th>Contact</th>
                                <th style="text-align:center;">Match %</th>
                                <th style="text-align:center;">Status</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($highlyQualified as $i => $app)
                            <tr class="qualified-row">
                                <td style="color:#888;">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;background:linear-gradient(135deg,#90d870,#4dd9c0);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                            {{ strtoupper(substr($app->jobseeker->first_name ?? 'J', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:13px;color:#2d7a5f;">{{ $app->jobseeker->first_name ?? '' }} {{ $app->jobseeker->surname ?? '—' }}</div>
                                            <div style="font-size:11px;color:#888;">{{ $app->jobseeker->reg_email ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:12px;color:#555;"><i class="bi bi-telephone me-1" style="color:#4dd9c0;"></i>{{ $app->jobseeker->contact_number ?? '—' }}</div>
                                    <div style="font-size:12px;color:#888;"><i class="bi bi-envelope me-1" style="color:#4dd9c0;"></i>{{ $app->jobseeker->reg_email ?? '—' }}</div>
                                </td>
                                <td style="text-align:center;"><span class="fw-bold" style="font-size:15px;color:#2d7a5f;">{{ $app->match_percentage }}%</span></td>
                                <td style="text-align:center;"><span class="badge-{{ $app->status }}">{{ ucfirst($app->status) }}</span></td>
                                <td style="text-align:center;">
                                    @include('company.jobs.partials.applicant-actions', ['app' => $app])
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ── QUALIFIED TAB ── --}}
    <div class="tab-pane fade" id="qualifiedPane" role="tabpanel">
        <div class="peso-card fade-in-1">
            @if($qualified->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-person-x"></i></div>
                    <h6>No qualified jobseekers yet</h6>
                    <p>@if($isInhouse) Jobseekers who accepted the in-house interview with 50–74% match will appear here. @else Jobseekers with 50–74% match will appear here. @endif</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table peso-table mb-0 qualified-table">
                        <thead>
                            <tr>
                                <th>#</th><th>Jobseeker</th><th>Contact</th>
                                <th style="text-align:center;">Match %</th>
                                <th style="text-align:center;">Status</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($qualified as $i => $app)
                            <tr class="qualified-row">
                                <td style="color:#888;">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;background:linear-gradient(135deg,#90d870,#4dd9c0);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                            {{ strtoupper(substr($app->jobseeker->first_name ?? 'J', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:13px;color:#2d7a5f;">{{ $app->jobseeker->first_name ?? '' }} {{ $app->jobseeker->surname ?? '—' }}</div>
                                            <div style="font-size:11px;color:#888;">{{ $app->jobseeker->reg_email ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:12px;color:#555;"><i class="bi bi-telephone me-1" style="color:#4dd9c0;"></i>{{ $app->jobseeker->contact_number ?? '—' }}</div>
                                    <div style="font-size:12px;color:#888;"><i class="bi bi-envelope me-1" style="color:#4dd9c0;"></i>{{ $app->jobseeker->reg_email ?? '—' }}</div>
                                </td>
                                <td style="text-align:center;"><span class="fw-bold" style="font-size:15px;color:#f59e0b;">{{ $app->match_percentage }}%</span></td>
                                <td style="text-align:center;"><span class="badge-{{ $app->status }}">{{ ucfirst($app->status) }}</span></td>
                                <td style="text-align:center;">
                                    @include('company.jobs.partials.applicant-actions', ['app' => $app])
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ── NOT QUALIFIED TAB ── --}}
    <div class="tab-pane fade" id="notQualifiedPane" role="tabpanel">
        <div class="peso-card fade-in-1">
            @if($notQualified->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-person-x"></i></div>
                    <h6>No not-qualified jobseekers yet</h6>
                    <p>@if($isInhouse) Jobseekers who accepted the in-house interview with below 50% match will appear here. @else Jobseekers with below 50% match will appear here. @endif</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table peso-table mb-0 qualified-table">
                        <thead>
                            <tr>
                                <th>#</th><th>Jobseeker</th><th>Contact</th>
                                <th style="text-align:center;">Match %</th>
                                <th style="text-align:center;">Status</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notQualified as $i => $app)
                            <tr class="qualified-row">
                                <td style="color:#888;">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;background:linear-gradient(135deg,#90d870,#4dd9c0);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                            {{ strtoupper(substr($app->jobseeker->first_name ?? 'J', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:13px;color:#2d7a5f;">{{ $app->jobseeker->first_name ?? '' }} {{ $app->jobseeker->surname ?? '—' }}</div>
                                            <div style="font-size:11px;color:#888;">{{ $app->jobseeker->reg_email ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:12px;color:#555;"><i class="bi bi-telephone me-1" style="color:#4dd9c0;"></i>{{ $app->jobseeker->contact_number ?? '—' }}</div>
                                    <div style="font-size:12px;color:#888;"><i class="bi bi-envelope me-1" style="color:#4dd9c0;"></i>{{ $app->jobseeker->reg_email ?? '—' }}</div>
                                </td>
                                <td style="text-align:center;"><span class="fw-bold" style="font-size:15px;color:#e05252;">{{ $app->match_percentage }}%</span></td>
                                <td style="text-align:center;"><span class="badge-{{ $app->status }}">{{ ucfirst($app->status) }}</span></td>
                                <td style="text-align:center;">
                                    @include('company.jobs.partials.applicant-actions', ['app' => $app])
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.qualified-row').forEach(row => {
            const nameCell = row.querySelector('td:nth-child(2)');
            const name = nameCell ? nameCell.innerText.toLowerCase() : '';
            row.style.display = name.includes(q) ? '' : 'none';
        });
    });

    document.querySelectorAll('.confirm-hired').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const form = btn.closest('form');
            Swal.fire({
                title: 'Mark as Hired?',
                text: 'This jobseeker will be marked as HIRED. This cannot be undone.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4dd9c0',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Yes, mark as hired',
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    });

    document.querySelectorAll('.confirm-rejected').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const form = btn.closest('form');
            Swal.fire({
                title: 'Mark as Rejected?',
                text: 'This jobseeker will be marked as REJECTED. This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e05252',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Yes, mark as rejected',
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    });
</script>
@endsection