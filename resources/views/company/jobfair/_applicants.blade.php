{{--
    Job Fair applicants — the "Job Fair Applicants" tab of Active Job Postings.

    Its own search parameter (jf_search) and page parameter (jf_page): the
    Active Vacancies tab on the same screen already uses search/vac_page, and
    the two lists must not move each other.

    Expects: $applicants, $jobFairByJobId, $isConfirmed, $jfSearch.
--}}

@if(!$isConfirmed)
    <div class="card border-0 shadow-sm rounded-3 p-4 text-center">
        <i class="ph ph-users-three" style="font-size:40px;color:var(--n-300);"></i>
        <div class="mt-2 fw-semibold" style="color:var(--g-700);font-size:13px;">No applicants to show yet</div>
        <div class="text-muted small">Confirm a job fair invitation first — applicants will appear here once you're participating.</div>
    </div>
@else
<div>
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h6 class="fw-bold mb-1" style="color:var(--g-700);">
                <i class="ph-fill ph-users-three me-2" style="color:var(--g-600);"></i>Job Fair Applicants
            </h6>
            <p class="mb-0" style="font-size:13px;color:var(--n-500);">
                Mark applicants as Hired, Waiting, or Rejected during the job fair interview.
            </p>
        </div>

        <form method="GET" action="{{ route('company.jobseekers') }}">
            <input type="hidden" name="tab" value="applicants">
            <div class="input-group" style="max-width:260px;">
                <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
                    <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
                </span>
                <input type="text" name="jf_search" class="form-control"
                    placeholder="Search name or position..."
                    style="border-color:var(--n-200);font-size:13px;"
                    value="{{ $jfSearch }}">
                <button type="submit" class="btn btn-peso">Search</button>
                @if($jfSearch)
                    <a href="{{ route('company.jobseekers', ['tab' => 'applicants']) }}" class="btn btn-peso-outline">Clear</a>
                @endif
            </div>
        </form>
    </div>

    @if($applicants->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-4 text-center">
            <i class="ph ph-users-three" style="font-size:40px;color:var(--n-300);"></i>
            <div class="mt-2 fw-semibold" style="color:var(--g-700);font-size:13px;">No applicants yet</div>
            <div class="text-muted small">Applicants who applied to your job fair postings will appear here.</div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Applicant</th>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Job Position</th>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Job Fair Event</th>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Job Match</th>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Qualification</th>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Applicant Status</th>
                            <th style="background:var(--g-100);color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applicants as $i => $app)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:var(--n-500);">{{ $applicants->firstItem() + $i }}</td>
                            <td style="padding:12px 16px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:32px;height:32px;background:var(--g-600);
                                                border-radius:50%;display:flex;align-items:center;justify-content:center;
                                                color:#fff;font-size:13px;font-weight:700;flex-shrink:0;">
                                        {{ strtoupper(substr($app->jobseeker->first_name ?? $app->jobseeker->name ?? 'J', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="color:var(--g-700);">
                                            {{ trim(($app->jobseeker->first_name ?? '') . ' ' . ($app->jobseeker->surname ?? '')) ?: 'None' }}
                                        </div>
                                        <div style="font-size:11px;color:var(--n-500);">{{ $app->jobseeker->reg_email ?? $app->jobseeker->user->email ?? 'None' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">
                                {{ $app->job->title ?? 'None' }}
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);font-size:12px;">
                                @php $jfEvent = $jobFairByJobId[$app->job_id] ?? null; @endphp
                                @if($jfEvent)
                                    <div class="fw-semibold" style="color:var(--g-700);">{{ $jfEvent->title }}</div>
                                    <div style="font-size:11px;color:var(--n-500);">{{ $jfEvent->event_date?->format('M d, Y') ?? 'None' }}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php $match = $app->match_percentage ?? 0; @endphp
                                <span class="fw-semibold"
                                    style="color:{{ $match >= 75 ? 'var(--g-700)' : ($match >= 50 ? 'var(--warn)' : 'var(--danger)') }}">
                                    {{ $match }}%
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php
                                    if ($match >= 75) {
                                        $qualLabel = 'Highly Qualified';
                                        $qualBg = 'var(--g-50)';
                                        $qualColor = 'var(--g-700)';
                                    } elseif ($match >= 50) {
                                        $qualLabel = 'Qualified';
                                        $qualBg = 'var(--warn-bg)';
                                        $qualColor = 'var(--warn)';
                                    } else {
                                        $qualLabel = 'Not Qualified';
                                        $qualBg = 'var(--danger-bg)';
                                        $qualColor = 'var(--danger)';
                                    }
                                @endphp
                                <span class="fw-semibold" style="color:{{ $qualColor }};font-size:11px;">
                                    {{ $qualLabel }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php
                                    $statusColors = [
                                        'hired'    => 'var(--g-700)',
                                        'waiting'  => 'var(--warn)',
                                        'rejected' => 'var(--danger)',
                                        'pending'  => 'var(--n-500)',
                                        'reviewed' => 'var(--g-600)',
                                        'qualified'=> 'var(--info)',
                                    ];
                                    $color = $statusColors[$app->status] ?? 'var(--n-500)';
                                @endphp
                                <span style="color:{{ $color }};font-weight:600;font-size:12px;">
                                    {{ ucfirst($app->status) }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php
                                    $rowUnlocked = !$jfEvent || now()->toDateString() >= \Carbon\Carbon::parse($jfEvent->event_date)->toDateString();
                                @endphp
                                {{-- Hired and rejected stay editable here too. Someone
                                     hired at the fair can still fail to report, and the
                                     employer is the one who finds that out. --}}
                                @if(!$rowUnlocked)
                                    <span style="font-size:11px;color:var(--n-500);"><i class="ph ph-clock me-1"></i>Locked until {{ $jfEvent->event_date->format('M d, Y') }}</span>
                                @else
                                <div class="d-flex gap-1 justify-content-center">
                                    {{-- Hired --}}
                                    <form method="POST" action="{{ route('company.applicants.status', $app->job_matching_id) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="hired">
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="font-size:11px;border-radius:8px;padding:4px 10px;
                                            {{ $app->status === 'hired'
                                                ? 'background:var(--g-600);color:#fff;border:none;'
                                                : 'background:var(--g-50);color:var(--g-700);border:1px solid var(--n-200);' }}">
                                            <i class="ph-fill ph-check-circle me-1"></i>Hired
                                        </button>
                                    </form>
                                    {{-- Waiting --}}
                                    <form method="POST" action="{{ route('company.applicants.status', $app->job_matching_id) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="waiting">
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="font-size:11px;border-radius:8px;padding:4px 10px;
                                            {{ $app->status === 'waiting'
                                                ? 'background:var(--warn);color:#fff;border:none;'
                                                : 'background:var(--warn-bg);color:var(--warn);border:1px solid var(--warn-br);' }}">
                                            <i class="ph ph-hourglass-medium me-1"></i>Waiting
                                        </button>
                                    </form>
                                    {{-- Rejected --}}
                                    <form method="POST" action="{{ route('company.applicants.status', $app->job_matching_id) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-sm fw-semibold"
                                            style="font-size:11px;border-radius:8px;padding:4px 10px;
                                            {{ $app->status === 'rejected'
                                                ? 'background:var(--danger);color:#fff;border:none;'
                                                : 'background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger-br);' }}">
                                            <i class="ph-fill ph-x-circle me-1"></i>Rejected
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

            @if($applicants->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3"
                style="border-top:1px solid var(--n-50);">
                <div style="font-size:12px;color:var(--n-500);">
                    Showing {{ $applicants->firstItem() }}–{{ $applicants->lastItem() }}
                    of {{ $applicants->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $applicants->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                               href="{{ $applicants->previousPageUrl() }}">
                                <i class="ph ph-caret-left"></i>
                            </a>
                        </li>
                        @foreach($applicants->getUrlRange(1, $applicants->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $applicants->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $applicants->currentPage()
                                    ? 'background:var(--g-600);border-color:transparent;color:#fff;'
                                    : 'border-color:var(--n-200);color:var(--g-700);' }}"
                               href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$applicants->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                               href="{{ $applicants->nextPageUrl() }}">
                                <i class="ph ph-caret-right"></i>
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
