@extends('staff.layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
            <i class="bi bi-building me-2" style="color:#4dd9c0;"></i>
            @if(isset($staffRole) && $staffRole === 'lra')
                Approved Local Employers
            @elseif(isset($staffRole) && $staffRole === 'sra')
                Overseas Employers
            @else
                Employer Requirements
            @endif
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">
            @if(isset($staffRole) && $staffRole === 'lra')
                List of approved local employers and their hired jobseekers
            @elseif(isset($staffRole) && $staffRole === 'sra')
                Manage overseas employer requirements and view hired jobseekers
            @else
                Review and manage employer submitted requirements
            @endif
        </p>
    </div>
</div>

{{-- FILTER TABS --}}
@if(isset($staffRole) && $staffRole === 'sra')
<div class="d-flex gap-2 mb-3 flex-wrap">
    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $val => $label)
    <a href="{{ route('staff.requirements', array_merge(request()->query(), ['status' => $val, 'page' => 1])) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('status','approved') === $val
           ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;'
           : 'border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;' }}
           border-radius:8px;font-size:12px;padding:5px 16px;">
        {{ $label }}
    </a>
    @endforeach
</div>
@elseif(!isset($staffRole) || $staffRole === 'job_vacancy')
<div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="{{ route('staff.requirements', array_merge(request()->query(), ['status' => 'pending', 'page' => 1])) }}"
       class="btn btn-sm fw-semibold"
       style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;
              border-radius:8px;font-size:12px;padding:5px 16px;">
        Pending
    </a>
</div>
@endif

{{-- SEARCH --}}
<div class="d-flex justify-content-end mb-3">
    <div class="input-group" style="max-width:260px;">
        <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
            <i class="bi bi-search" style="color:#4dd9c0;"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search company..."
            style="border-color:#a8e6cf;font-size:13px;outline:none;box-shadow:none;"
            autocomplete="off"
            value="{{ request('search') }}">
    </div>
</div>

{{-- TABLE --}}
@if($requirements->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-inbox" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No requirements found</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:linear-gradient(90deg,#90d870,#4dd9c0);">
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Company</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;">Email</th>
                        @if(isset($staffRole) && in_array($staffRole, ['lra','sra']))
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Total Hired</th>
                        @if($staffRole === 'sra')
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        @endif
                        @else
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        @endif
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Submitted</th>
                        <th style="color:#2d7a5f;font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requirements as $i => $req)
                    @php
                        $totalHired = isset($staffRole) && $staffRole === 'lra'
                            ? ($req->employer->jobs->flatMap->applications->where('status', 'hired')->count() ?? 0)
                            : 0;
                    @endphp
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:#888;">{{ $requirements->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:#2d7a5f;">
                            {{ $req->employer->company_name ?? $req->employer->name ?? '—' }}
                        </td>
                        <td style="padding:12px 16px;color:#555;">
                            {{ $req->employer->employer->email ?? '—' }}
                        </td>
                        @if(isset($staffRole) && in_array($staffRole, ['lra','sra']))
                        <td style="padding:12px 16px;text-align:center;">
                            <span class="fw-bold" style="color:#2d7a5f;font-size:14px;">{{ $totalHired }}</span>
                            <span style="font-size:11px;color:#888;"> hired</span>
                        </td>
                        @if($staffRole === 'sra')
                        <td style="padding:12px 16px;text-align:center;">
                            @php $colors = ['pending'=>'#f59e0b','approved'=>'#2d7a5f','rejected'=>'#e05252']; @endphp
                            <span style="font-size:12px;font-weight:600;color:{{ $colors[$req->status] ?? '#888' }}">
                                {{ ucfirst($req->status) }}
                            </span>
                        </td>
                        @endif
                        @else
                        <td style="padding:12px 16px;text-align:center;">
                            @php $colors = ['pending'=>'#f59e0b','approved'=>'#2d7a5f','rejected'=>'#e05252']; @endphp
                            <span style="font-size:12px;font-weight:600;color:{{ $colors[$req->status] ?? '#888' }}">
                                {{ ucfirst($req->status) }}
                            </span>
                        </td>
                        @endif
                        <td style="padding:12px 16px;color:#888;text-align:center;">
                            {{ $req->created_at->format('M d, Y') }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if(isset($staffRole) && in_array($staffRole, ['lra','sra']))
                            <button class="btn btn-sm fw-semibold"
                                style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                       color:#fff;border:none;border-radius:8px;font-size:12px;"
                                data-bs-toggle="modal"
                                data-bs-target="#employerModal{{ $req->id }}">
                                <i class="bi bi-eye-fill me-1"></i>View
                            </button>
                            @else
                            <a href="{{ route('staff.requirements.view', $req->id) }}"
                               class="btn btn-sm fw-semibold"
                               style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                      color:#fff;border:none;border-radius:8px;font-size:12px;">
                                <i class="bi bi-eye-fill me-1"></i>View
                            </a>
                            @endif
                        </td>
                    </tr>

                    {{-- MODAL per employer (LRA + SRA) --}}
                    @if(isset($staffRole) && in_array($staffRole, ['lra','sra']))
                    <div class="modal fade" id="employerModal{{ $req->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content" style="border-radius:16px;border:none;">
                                <div class="modal-header" style="background:linear-gradient(90deg,#90d870,#4dd9c0);border-radius:16px 16px 0 0;">
                                    <h6 class="modal-title text-white fw-bold">
                                        <i class="bi bi-building me-2"></i>
                                        {{ $req->employer->company_name ?? $req->employer->name ?? '—' }}
                                    </h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    {{-- Employer Info --}}
                                    <div class="mb-3 p-3 rounded-3" style="background:#f0f9f6;">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:#888;">Company Name</div>
                                                <div class="fw-semibold" style="color:#2d7a5f;">{{ $req->employer->company_name ?? '—' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:#888;">Email</div>
                                                <div class="fw-semibold" style="color:#2d7a5f;">{{ $req->employer->employer->email ?? '—' }}</div>
                                            </div>
                                            <div class="col-md-6">
    <div style="font-size:12px;color:#888;">Employer Type</div>
    <div class="fw-semibold" style="color:#2d7a5f;">{{ $req->employer->employerNsrp->employer_type ?? '—' }}</div>
</div>
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:#888;">Total Hired</div>
                                                <div class="fw-bold" style="color:#2d7a5f;font-size:18px;">{{ $totalHired }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Hired Jobseekers List --}}
                                    <h6 class="fw-bold mb-2" style="color:#2d7a5f;font-size:13px;">
                                        <i class="bi bi-people-fill me-1" style="color:#4dd9c0;"></i>Hired Jobseekers
                                    </h6>
                                    @php
                                        $hiredApps = $req->employer->jobs->flatMap->applications->where('status', 'hired');
                                    @endphp
                                    @if($hiredApps->isEmpty())
                                        <div class="text-center py-3" style="color:#888;font-size:13px;">
                                            <i class="bi bi-inbox" style="font-size:32px;color:#c0e8dc;"></i>
                                            <div class="mt-2">No hired jobseekers yet</div>
                                        </div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0" style="font-size:13px;">
                                                <thead>
                                                    <tr style="background:#f0f9f6;">
                                                        <th style="color:#2d7a5f;font-size:11px;padding:8px 12px;">#</th>
                                                        <th style="color:#2d7a5f;font-size:11px;padding:8px 12px;">Name</th>
                                                        <th style="color:#2d7a5f;font-size:11px;padding:8px 12px;">Job Title</th>
                                                        <th style="color:#2d7a5f;font-size:11px;padding:8px 12px;text-align:center;">Match %</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($hiredApps as $idx => $app)
                                                    <tr>
                                                        <td style="padding:8px 12px;color:#888;">{{ $idx + 1 }}</td>
                                                        <td style="padding:8px 12px;font-weight:600;color:#2d7a5f;">
                                                            {{ $app->jobseeker->name ?? '—' }}
                                                        </td>
                                                        <td style="padding:8px 12px;color:#555;">
                                                            {{ $app->job->title ?? '—' }}
                                                        </td>
                                                        <td style="padding:8px 12px;text-align:center;">
                                                            @php $match = $app->match_percentage ?? 0; @endphp
                                                            <span class="fw-semibold"
                                                                style="color:{{ $match >= 75 ? '#2d7a5f' : ($match >= 50 ? '#f59e0b' : '#e05252') }}">
                                                                {{ $match }}%
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    @if(isset($staffRole) && $staffRole === 'sra' && $req->status === 'pending')
                                    <form action="{{ route('staff.requirements.approve', $req->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="background:#2d7a5f;color:#fff;border:none;border-radius:8px;">
                                            <i class="bi bi-check-lg me-1"></i>Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-sm fw-semibold"
                                        style="background:#e05252;color:#fff;border:none;border-radius:8px;"
                                        onclick="openSraRejectModal({{ $req->id }})">
                                        <i class="bi bi-x-lg me-1"></i>Reject
                                    </button>
                                    @endif
                                    <button type="button" class="btn btn-sm fw-semibold"
                                        data-bs-dismiss="modal"
                                        style="border:1.5px solid #a8e6cf;color:#2d7a5f;background:#fff;border-radius:8px;">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @endforeach
                </tbody>
            </table>
        </div>

        @if($requirements->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top:1px solid #f0f9f6;">
            <div style="font-size:12px;color:#888;">
                Showing {{ $requirements->firstItem() }}–{{ $requirements->lastItem() }}
                of {{ $requirements->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $requirements->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $requirements->previousPageUrl() }}&status={{ request('status','pending') }}&search={{ request('search') }}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    @foreach($requirements->getUrlRange(1, $requirements->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $requirements->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $requirements->currentPage()
                                ? 'background:linear-gradient(90deg,#90d870,#4dd9c0);border-color:transparent;color:#fff;'
                                : 'border-color:#a8e6cf;color:#2d7a5f;' }}"
                           href="{{ $url }}&status={{ request('status','pending') }}&search={{ request('search') }}">
                            {{ $page }}
                        </a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$requirements->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:#a8e6cf;color:#2d7a5f;"
                           href="{{ $requirements->nextPageUrl() }}&status={{ request('status','pending') }}&search={{ request('search') }}">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
@endif

{{-- SRA REJECT MODAL --}}
@if(isset($staffRole) && $staffRole === 'sra')
<div class="modal fade" id="sraRejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:#e05252;">
                <h6 class="modal-title text-white fw-bold">
                    <i class="bi bi-x-circle me-2"></i>Reject Requirements
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="sraRejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <label class="fw-semibold mb-1" style="font-size:13px;color:#2d7a5f;">Reason for Rejection *</label>
                    <textarea name="remarks" class="form-control" rows="3"
                        placeholder="State the reason for rejection..."
                        style="border-color:#a8e6cf;font-size:13px;" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm fw-semibold"
                        style="background:#e05252;color:#fff;border:none;border-radius:8px;">
                        <i class="bi bi-x-lg me-1"></i>Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    function openSraRejectModal(id) {
        document.getElementById('sraRejectForm').action = '/staff/requirements/' + id + '/reject';
        new bootstrap.Modal(document.getElementById('sraRejectModal')).show();
    }

    let searchTimer;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.set('search', this.value.trim());
            url.searchParams.set('page', 1);
            window.location.href = url.toString();
        }, 500);
    });
</script>
@endpush

@endsection