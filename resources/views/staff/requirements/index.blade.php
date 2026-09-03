@extends('staff.layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">
            <i class="ph ph-buildings me-2" style="color:var(--g-600);"></i>
            @if(isset($staffRole) && $staffRole === 'lra')
                Approved Local Employers
            @elseif(isset($staffRole) && $staffRole === 'sra')
                Overseas Employers
            @else
                Employer Requirements
            @endif
        </h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">
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
    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'expired' => 'Expired'] as $val => $label)
    <a href="{{ route('staff.requirements', array_merge(request()->query(), ['status' => $val, 'page' => 1])) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('status','approved') === $val
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:12px;padding:5px 16px;">
        {{ $label }}
    </a>
    @endforeach
</div>
@elseif(!isset($staffRole) || $staffRole === 'job_vacancy')
<div class="d-flex gap-2 mb-3 flex-wrap">
    @foreach(['pending' => 'Pending', 'expired' => 'Expired'] as $val => $label)
    <a href="{{ route('staff.requirements', array_merge(request()->query(), ['status' => $val, 'page' => 1])) }}"
       class="btn btn-sm fw-semibold"
       style="{{ request('status','pending') === $val
           ? 'background:var(--g-600);color:#fff;border:none;'
           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
           border-radius:8px;font-size:12px;padding:5px 16px;">
        {{ $label }}
    </a>
    @endforeach
</div>
@endif

{{-- SEARCH --}}
<div class="d-flex justify-content-end mb-3">
    <div class="input-group" style="max-width:260px;">
        <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
            <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search company..."
            style="border-color:var(--n-200);font-size:13px;outline:none;box-shadow:none;"
            autocomplete="off"
            value="{{ request('search') }}">
    </div>
</div>

{{-- TABLE --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background:var(--g-600);">
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Company</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Email</th>
                        @if(isset($staffRole) && in_array($staffRole, ['lra','sra']))
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Total Hired</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        @else
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                        @endif
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Date Submitted</th>
                        <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requirements as $i => $req)
                    @php
                        $totalHired = isset($staffRole) && $staffRole === 'lra'
                            ? ($req->employer->jobs->flatMap->applications->where('status', 'hired')->count() ?? 0)
                            : 0;
                    @endphp
                    <tr style="font-size:13px;">
                        <td style="padding:12px 16px;color:var(--n-500);">{{ $requirements->firstItem() + $loop->index }}</td>
                        <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                            {{ $req->employer->company_name ?? $req->employer->name ?? 'None' }}
                        </td>
                        <td style="padding:12px 16px;color:var(--n-700);">
                            {{ $req->employer->employer->email ?? 'None' }}
                        </td>
                        @if(isset($staffRole) && in_array($staffRole, ['lra','sra']))
                        <td style="padding:12px 16px;text-align:center;">
                            <span class="fw-bold" style="color:var(--g-700);font-size:14px;">{{ $totalHired }}</span>
                            <span style="font-size:11px;color:var(--n-500);"> hired</span>
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @php $colors = ['pending'=>'var(--warn)','approved'=>'var(--g-700)','rejected'=>'var(--danger)','expired'=>'var(--warn)']; @endphp
                            <span style="font-size:12px;font-weight:600;color:{{ $colors[$req->status] ?? 'var(--n-500)' }}">
                                {{ ucfirst($req->status) }}
                            </span>
                        </td>
                        @else
                        <td style="padding:12px 16px;text-align:center;">
                            @php $colors = ['pending'=>'var(--warn)','approved'=>'var(--g-700)','rejected'=>'var(--danger)','expired'=>'var(--warn)']; @endphp
                            <span style="font-size:12px;font-weight:600;color:{{ $colors[$req->status] ?? 'var(--n-500)' }}">
                                {{ ucfirst($req->status) }}
                            </span>
                        </td>
                        @endif
                        <td style="padding:12px 16px;color:var(--n-500);text-align:center;">
                            {{ $req->created_at->format('M d, Y') }}
                        </td>
                        <td style="padding:12px 16px;text-align:center;">
                            @if(isset($staffRole) && in_array($staffRole, ['lra','sra']))
                            <button class="btn btn-sm fw-semibold"
                                style="background:var(--g-600);
                                       color:#fff;border:none;border-radius:8px;font-size:12px;"
                                data-bs-toggle="modal"
                                data-bs-target="#employerModal{{ $req->employer_requirements_id }}">
                                <i class="ph-fill ph-eye me-1"></i>View
                            </button>
                            @else
                            <a href="{{ route('staff.requirements.view', $req->employer_requirements_id) }}"
                               class="btn btn-sm fw-semibold"
                               style="background:var(--g-600);
                                      color:#fff;border:none;border-radius:8px;font-size:12px;">
                                <i class="ph-fill ph-eye me-1"></i>View
                            </a>
                            @endif
                        </td>
                    </tr>

                    {{-- MODAL per employer (LRA + SRA) --}}
                    @if(isset($staffRole) && in_array($staffRole, ['lra','sra']))
                    <div class="modal fade" id="employerModal{{ $req->employer_requirements_id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content" style="border-radius:16px;border:none;">
                                <div class="modal-header" style="background:var(--g-600);border-radius:16px 16px 0 0;">
                                    <h6 class="modal-title text-white fw-bold">
                                        <i class="ph ph-buildings me-2"></i>
                                        {{ $req->employer->company_name ?? $req->employer->name ?? 'None' }}
                                    </h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    {{-- Employer Info --}}
                                    <div class="mb-3 p-3 rounded-3" style="background:var(--n-50);">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:var(--n-500);">Company Name</div>
                                                <div class="fw-semibold" style="color:var(--g-700);">{{ $req->employer->company_name ?? 'None' }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:var(--n-500);">Email</div>
                                                <div class="fw-semibold" style="color:var(--g-700);">{{ $req->employer->employer->email ?? 'None' }}</div>
                                            </div>
                                            <div class="col-md-6">
    <div style="font-size:12px;color:var(--n-500);">Employer Type</div>
    <div class="fw-semibold" style="color:var(--g-700);">{{ $req->employer->employer_type ?? 'None' }}</div>
</div>
                                            <div class="col-md-6">
                                                <div style="font-size:12px;color:var(--n-500);">Total Hired</div>
                                                <div class="fw-bold" style="color:var(--g-700);font-size:18px;">{{ $totalHired }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Hired Jobseekers List --}}
                                    <h6 class="fw-bold mb-2" style="color:var(--g-700);font-size:13px;">
                                        <i class="ph-fill ph-users-three me-1" style="color:var(--g-600);"></i>Hired Jobseekers
                                    </h6>
                                    @php
                                        $hiredApps = $req->employer->jobs->flatMap->applications->where('status', 'hired');
                                    @endphp
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0" style="font-size:13px;">
                                                <thead>
                                                    <tr style="background:var(--n-50);">
                                                        <th style="color:var(--g-700);font-size:11px;padding:8px 12px;">#</th>
                                                        <th style="color:var(--g-700);font-size:11px;padding:8px 12px;">Name</th>
                                                        <th style="color:var(--g-700);font-size:11px;padding:8px 12px;">Job Title</th>
                                                        <th style="color:var(--g-700);font-size:11px;padding:8px 12px;text-align:center;">Match %</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($hiredApps as $idx => $app)
                                                    <tr>
                                                        <td style="padding:8px 12px;color:var(--n-500);">{{ $idx + 1 }}</td>
                                                        <td style="padding:8px 12px;font-weight:600;color:var(--g-700);">
                                                            {{ $app->jobseeker->name ?? 'None' }}
                                                        </td>
                                                        <td style="padding:8px 12px;color:var(--n-700);">
                                                            {{ $app->job->title ?? 'None' }}
                                                        </td>
                                                        <td style="padding:8px 12px;text-align:center;">
                                                            @php $match = $app->match_percentage ?? 0; @endphp
                                                            <span class="fw-semibold"
                                                                style="color:{{ $match >= 75 ? 'var(--g-700)' : ($match >= 50 ? 'var(--warn)' : 'var(--danger)') }}">
                                                                {{ $match }}%
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                                                         gihapon ug ang pahina dili mag-usab ug porma. --}}
                                                    <tr>
                                                        <td colspan="4" class="text-center"
                                                            style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                                            <i class="ph ph-tray me-1"
                                                               style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                                                            No hired jobseekers yet
                                                        </td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                </div>
                                <div class="modal-footer">
                                    @if(isset($staffRole) && $staffRole === 'sra' && $req->status === 'pending')
                                    <form action="{{ route('staff.requirements.approve', $req->employer_requirements_id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="background:var(--g-700);color:#fff;border:none;border-radius:8px;">
                                            <i class="ph ph-check me-1"></i>Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-sm fw-semibold"
                                        style="background:var(--danger);color:#fff;border:none;border-radius:8px;"
                                        onclick="openSraRejectModal({{ $req->employer_requirements_id }})">
                                        <i class="ph ph-x me-1"></i>Decline
                                    </button>
                                    @endif
                                    <button type="button" class="btn btn-sm fw-semibold"
                                        data-bs-dismiss="modal"
                                        style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @empty
                    {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                         gihapon ug ang pahina dili mag-usab ug porma. --}}
                    <tr>
                        <td colspan="8" class="text-center"
                            style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                            <i class="ph ph-tray me-1"
                               style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                            No requirements found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requirements->hasPages())
        <div class="d-flex justify-content-between align-items-center px-3 py-3"
            style="border-top:1px solid var(--n-50);">
            <div style="font-size:12px;color:var(--n-500);">
                Showing {{ $requirements->firstItem() }}–{{ $requirements->lastItem() }}
                of {{ $requirements->total() }} results
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $requirements->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                           href="{{ $requirements->previousPageUrl() }}&status={{ request('status','pending') }}&search={{ request('search') }}">
                            <i class="ph ph-caret-left"></i>
                        </a>
                    </li>
                    @foreach($requirements->getUrlRange(1, $requirements->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $requirements->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2"
                           style="{{ $page == $requirements->currentPage()
                                ? 'background:var(--g-600);border-color:transparent;color:#fff;'
                                : 'border-color:var(--n-200);color:var(--g-700);' }}"
                           href="{{ $url }}&status={{ request('status','pending') }}&search={{ request('search') }}">
                            {{ $page }}
                        </a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$requirements->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                           href="{{ $requirements->nextPageUrl() }}&status={{ request('status','pending') }}&search={{ request('search') }}">
                            <i class="ph ph-caret-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>

{{-- SRA REJECT MODAL --}}
@if(isset($staffRole) && $staffRole === 'sra')
<div class="modal fade" id="sraRejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:var(--danger);">
                <h6 class="modal-title text-white fw-bold">
                    <i class="ph ph-x-circle me-2"></i>Decline Requirements
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="sraRejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <label class="fw-semibold mb-1" style="font-size:13px;color:var(--g-700);">Reason for Decline *</label>
                    <textarea name="remarks" class="form-control" rows="3"
                        placeholder="State the reason for declining..."
                        style="border-color:var(--n-200);font-size:13px;" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm fw-semibold"
                        style="background:var(--danger);color:#fff;border:none;border-radius:8px;">
                        <i class="ph ph-x me-1"></i>Decline
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