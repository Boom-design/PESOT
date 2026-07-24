@extends('company.layouts.app')

@section('page-title', 'Applicants')

@section('content')

<div class="mb-4 fade-in">
    <a href="{{ route('company.jobs') }}" style="font-size:13px; color:#4dd9c0; text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i> Back to Job Posts
    </a>
    <h5 class="fw-bold mt-2 mb-1" style="color:#2d7a5f;">{{ $job->title }}</h5>
    <div class="d-flex gap-2 align-items-center">
        <span style="font-size:12px;color:#888;">
            <i class="bi bi-geo-alt me-1" style="color:#4dd9c0;"></i>{{ $job->location }}
        </span>
        <span class="badge-{{ $job->status }}">{{ ucfirst($job->status) }}</span>
        <span style="font-size:12px;color:#888;">
            <i class="bi bi-people me-1" style="color:#4dd9c0;"></i>{{ $applicants->count() }} applicant(s)
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

{{-- ── TABLE ── --}}
<div class="peso-card fade-in-1">
    @if($applicants->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-people"></i>
            </div>
            <h6>No applicants yet</h6>
            <p>Applicants will appear here once jobseekers apply.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table peso-table mb-0" id="applicantsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Applicant Name</th>
                        <th>Email</th>
                        <th>Applied On</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applicants as $i => $application)
                    <tr class="applicant-row">
                        <td style="color:#888;">{{ $i + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;background:linear-gradient(135deg,#90d870,#4dd9c0);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                    {{ strtoupper(substr($application->jobseeker->first_name ?? $application->jobseeker->name ?? 'J', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="font-size:13px;">
                                        {{ $application->jobseeker->first_name ?? '' }}
                                        {{ $application->jobseeker->last_name ?? $application->jobseeker->name ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="color:#888;">{{ $application->jobseeker->email ?? '—' }}</td>
                        <td style="color:#888;">{{ $application->created_at->format('M d, Y') }}</td>
                        <td>
                            <span class="badge-{{ $application->status }}">
                                {{ ucfirst($application->status) }}
                            </span>
                        </td>
                        <td>
                            @if(in_array($application->status, ['hired', 'rejected']))
                                <span style="font-size:12px;color:#888;">
                                    <i class="bi bi-lock-fill me-1"></i>Final decision made
                                </span>
                            @else
                            <div class="d-flex gap-1">
                                {{-- Hired --}}
                                <form method="POST" action="{{ route('company.applicants.status', $application->id) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="hired">
                                    <button type="submit" class="btn btn-sm fw-semibold"
                                        style="font-size:11px;border-radius:8px;padding:4px 10px;
                                        background:#e8f8f3;color:#2d7a5f;border:1px solid #a8e6cf;"
                                        title="Mark as Hired"
                                        onclick="return confirm('Mark this applicant as HIRED? This cannot be undone.')">
                                        <i class="bi bi-check-circle-fill me-1"></i>Hired
                                    </button>
                                </form>
                                {{-- Waiting --}}
                                <form method="POST" action="{{ route('company.applicants.status', $application->id) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="waiting">
                                    <button type="submit" class="btn btn-sm fw-semibold"
                                        style="font-size:11px;border-radius:8px;padding:4px 10px;
                                        {{ $application->status === 'waiting'
                                            ? 'background:#f59e0b;color:#fff;border:none;'
                                            : 'background:#fff8e1;color:#f59e0b;border:1px solid #fcd34d;' }}"
                                        title="Mark as Waiting">
                                        <i class="bi bi-hourglass-split me-1"></i>Waiting
                                    </button>
                                </form>
                                {{-- Rejected --}}
                                <form method="POST" action="{{ route('company.applicants.status', $application->id) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-sm fw-semibold"
                                        style="font-size:11px;border-radius:8px;padding:4px 10px;
                                        background:#fff5f5;color:#e05252;border:1px solid #ffcdd2;"
                                        title="Mark as Rejected"
                                        onclick="return confirm('Mark this applicant as REJECTED? This cannot be undone.')">
                                        <i class="bi bi-x-circle-fill me-1"></i>Rejected
                                    </button>
                                </form>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
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