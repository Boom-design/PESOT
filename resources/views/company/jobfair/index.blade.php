@extends('company.layouts.app')

@section('page-title', '')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
            <i class="bi bi-calendar-event-fill me-2" style="color:#4dd9c0;"></i>Job Fair Invitations
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">View and respond to job fair invitations from PESO</p>
    </div>
</div>

@if($invitations->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-4 text-center">
        <i class="bi bi-calendar-x" style="font-size:36px;color:#c0e8dc;"></i>
        <div class="mt-2 fw-semibold" style="color:#2d7a5f;font-size:13px;">No job fair invitations yet</div>
        <div class="text-muted small">PESO will send you invitations for upcoming job fair events</div>
    </div>
@else
    <div class="d-flex flex-column gap-2 mb-2">
        @foreach($invitations as $i => $invitation)
        <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;background:linear-gradient(135deg,#90d870,#4dd9c0);
                                border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-calendar-event-fill text-white"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:#2d7a5f;font-size:14px;">
                            {{ $invitation->jobFair->title ?? '—' }}
                        </div>
                        <div style="font-size:12px;color:#888;">
                            <i class="bi bi-calendar3 me-1"></i>{{ $invitation->jobFair->event_date?->format('M d, Y') ?? '—' }}
                            &nbsp;•&nbsp;
                            <i class="bi bi-geo-alt me-1"></i>{{ $invitation->jobFair->venue ?? '—' }}
                        </div>
                    </div>
                </div>
                <div>
                    @if($invitation->confirmation_status === 'pending')
                        <form action="{{ route('company.jobfair.respond', $invitation->id) }}"
                              method="POST" class="d-inline" id="confirmForm{{ $invitation->id }}">
                            @csrf
                            <input type="hidden" name="response" id="responseInput{{ $invitation->id }}">
                            <button type="button"
                                class="btn btn-sm fw-semibold me-1"
                                style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;font-size:11px;"
                                onclick="confirmResponse({{ $invitation->id }}, 'confirmed')">
                                <i class="bi bi-check-lg me-1"></i>Confirm
                            </button>
                            <button type="button"
                                class="btn btn-sm fw-semibold"
                                style="background:#e05252;color:#fff;border:none;border-radius:8px;font-size:11px;"
                                onclick="confirmResponse({{ $invitation->id }}, 'declined')">
                                <i class="bi bi-x-lg me-1"></i>Decline
                            </button>
                        </form>
                    @elseif($invitation->confirmation_status === 'confirmed')
                        <span class="badge fw-semibold" style="background:#e8f8f3;color:#2d7a5f;font-size:11px;padding:6px 12px;border-radius:20px;">
                            <i class="bi bi-check-circle-fill me-1"></i>Confirmed
                        </span>
                    @else
                        <span class="badge fw-semibold" style="background:#fff5f5;color:#e05252;font-size:11px;padding:6px 12px;border-radius:20px;">
                            <i class="bi bi-x-circle-fill me-1"></i>Declined
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif

{{-- ── INTERVIEWED APPLICANTS ── --}}
@if($isConfirmed)
<div class="mt-4">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h6 class="fw-bold mb-1" style="color:#2d7a5f;">
                <i class="bi bi-people-fill me-2" style="color:#4dd9c0;"></i>Interviewed Applicants
            </h6>
            <p class="mb-0" style="font-size:13px;color:#888;">
                Mark applicants as Hired, Waiting, or Rejected during the job fair interview.
            </p>
        </div>
        <div class="input-group" style="max-width:260px;">
            <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
                <i class="bi bi-search" style="color:#4dd9c0;"></i>
            </span>
            <input type="text" id="applicantSearch" class="form-control"
                placeholder="Search name or position..."
                style="border-color:#a8e6cf;font-size:13px;"
                value="{{ $search }}">
        </div>
    </div>

    @if($applicants->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-4 text-center">
            <i class="bi bi-people" style="font-size:40px;color:#c0e8dc;"></i>
            <div class="mt-2 fw-semibold" style="color:#2d7a5f;font-size:13px;">No applicants yet</div>
            <div class="text-muted small">Applicants who applied to your job postings will appear here.</div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Applicant</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;">Job Position</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Match %</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Qualification</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                            <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applicants as $i => $app)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:#888;">{{ $i + 1 }}</td>
                            <td style="padding:12px 16px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:32px;height:32px;background:linear-gradient(135deg,#90d870,#4dd9c0);
                                                border-radius:50%;display:flex;align-items:center;justify-content:center;
                                                color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                        {{ strtoupper(substr($app->jobseeker->first_name ?? $app->jobseeker->name ?? 'J', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="color:#2d7a5f;">
                                            {{ trim(($app->jobseeker->first_name ?? '') . ' ' . ($app->jobseeker->surname ?? '')) ?: '—' }}
                                        </div>
                                        <div style="font-size:11px;color:#888;">{{ $app->jobseeker->reg_email ?? $app->jobseeker->user->email ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:12px 16px;color:#555;">
                                {{ $app->job->title ?? '—' }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php $match = $app->match_percentage ?? 0; @endphp
                                <span class="fw-semibold"
                                    style="color:{{ $match >= 75 ? '#2d7a5f' : ($match >= 50 ? '#f59e0b' : '#e05252') }}">
                                    {{ $match }}%
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php
                                    if ($match >= 75) {
                                        $qualLabel = 'Highly Qualified';
                                        $qualBg = '#e8f8f3';
                                        $qualColor = '#2d7a5f';
                                    } elseif ($match >= 50) {
                                        $qualLabel = 'Qualified';
                                        $qualBg = '#fff8e1';
                                        $qualColor = '#f59e0b';
                                    } else {
                                        $qualLabel = 'Not Qualified';
                                        $qualBg = '#fff5f5';
                                        $qualColor = '#e05252';
                                    }
                                @endphp
                                <span class="fw-semibold" style="font-size:11px;padding:4px 10px;border-radius:20px;
                                    background:{{ $qualBg }};color:{{ $qualColor }};">
                                    {{ $qualLabel }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php
                                    $statusColors = [
                                        'hired'    => '#2d7a5f',
                                        'waiting'  => '#f59e0b',
                                        'rejected' => '#e05252',
                                        'pending'  => '#888',
                                        'reviewed' => '#4dd9c0',
                                        'qualified'=> '#3949ab',
                                    ];
                                    $color = $statusColors[$app->status] ?? '#888';
                                @endphp
                                <span style="color:{{ $color }};font-weight:600;font-size:12px;">
                                    {{ ucfirst($app->status) }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                <div class="d-flex gap-1 justify-content-center">
                                    {{-- Hired --}}
                                    <form method="POST" action="{{ route('company.applicants.status', $app->id) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="hired">
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="font-size:11px;border-radius:8px;padding:4px 10px;
                                            {{ $app->status === 'hired'
                                                ? 'background:#2d7a5f;color:#fff;border:none;'
                                                : 'background:#e8f8f3;color:#2d7a5f;border:1px solid #a8e6cf;' }}">
                                            <i class="bi bi-check-circle-fill me-1"></i>Hired
                                        </button>
                                    </form>
                                    {{-- Waiting --}}
                                    <form method="POST" action="{{ route('company.applicants.status', $app->id) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="waiting">
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="font-size:11px;border-radius:8px;padding:4px 10px;
                                            {{ $app->status === 'waiting'
                                                ? 'background:#f59e0b;color:#fff;border:none;'
                                                : 'background:#fff8e1;color:#f59e0b;border:1px solid #fcd34d;' }}">
                                            <i class="bi bi-hourglass-split me-1"></i>Waiting
                                        </button>
                                    </form>
                                    {{-- Rejected --}}
                                    <form method="POST" action="{{ route('company.applicants.status', $app->id) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="font-size:11px;border-radius:8px;padding:4px 10px;
                                            {{ $app->status === 'rejected'
                                                ? 'background:#e05252;color:#fff;border:none;'
                                                : 'background:#fff5f5;color:#e05252;border:1px solid #ffcdd2;' }}">
                                            <i class="bi bi-x-circle-fill me-1"></i>Rejected
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($applicants->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3"
                style="border-top:1px solid #f0f9f6;">
                <div style="font-size:12px;color:#888;">
                    Showing {{ $applicants->firstItem() }}–{{ $applicants->lastItem() }}
                    of {{ $applicants->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $applicants->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                               href="{{ $applicants->previousPageUrl() }}">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        @foreach($applicants->getUrlRange(1, $applicants->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $applicants->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $applicants->currentPage()
                                    ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;'
                                    : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$applicants->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                               href="{{ $applicants->nextPageUrl() }}">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    @endif
</div>
@endif

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let applicantSearchTimer;
document.getElementById('applicantSearch')?.addEventListener('input', function() {
    clearTimeout(applicantSearchTimer);
    applicantSearchTimer = setTimeout(() => {
        const url = new URL(window.location.href);
        url.searchParams.set('search', this.value.trim());
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }, 500);
});

function confirmResponse(id, response) {
    const isConfirm = response === 'confirmed';
    Swal.fire({
        title: isConfirm ? 'Confirm Participation?' : 'Decline Invitation?',
        text: isConfirm
            ? 'All jobseekers who applied to your company will be notified about this job fair.'
            : 'Are you sure you want to decline this job fair invitation?',
        icon: isConfirm ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonColor: isConfirm ? '#2d7a5f' : '#e05252',
        cancelButtonColor: '#888',
        confirmButtonText: isConfirm ? 'Yes, Confirm!' : 'Yes, Decline!',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('responseInput' + id).value = response;
            document.getElementById('confirmForm' + id).submit();
        }
    });
}
</script>
@endsection

@endsection