{{-- ── EMPLOYER REPORTS — who each employer actually took on ──

     The Registered Employer list states a number and stops there. This is the
     same number with the names behind it: the desk is asked "which of our
     jobseekers did that agency hire", and until now the only way to answer was
     to read the placement report and pick the company out by eye.

     One row per employer, and the View button opens the names. No date range —
     the number on the employer list counts every hire ever made, and a report
     that quietly filtered by month would disagree with the number that was
     clicked to get here. --}}

@php
    $rows = $employerHires ?? null;
@endphp

@if($employerFocusId ?? null)
<div class="d-flex align-items-center gap-2 mb-3">
    <span style="font-size:12px;color:var(--n-500);">Showing one employer.</span>
    <a href="{{ route('staff.reports', ['tab' => 'employer_hires']) }}"
       class="btn btn-sm fw-semibold"
       style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;
              border-radius:8px;font-size:12px;padding:4px 14px;">
        <i class="ph ph-list me-1"></i> Show all employers
    </a>
</div>
@endif

<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Company Name</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Company Email</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Jobs Offered</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Total Hired</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows ?? [] as $company)
                @php
                    // The hired applications this company's postings produced.
                    // Already narrowed to status = hired when they were loaded,
                    // so nothing is filtered twice.
                    $hires = $company->jobs->flatMap->applications;
                @endphp
                <tr style="font-size:13px;">
                    <td style="padding:12px 16px;color:var(--n-500);">
                        {{ $rows->firstItem() + $loop->index }}
                    </td>
                    <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                        {{ $company->company_name ?? 'None' }}
                    </td>
                    <td style="padding:12px 16px;color:var(--n-700);">
                        {{ $company->employer->email ?? 'None' }}
                    </td>
                    <td style="padding:12px 16px;text-align:center;">
                        {{-- How many vacancies this employer has put through PESO,
                             of every kind. This is the number the Total Jobs card
                             on the activity tabs used to state for the whole desk
                             at once, which told nobody which employer it came
                             from. --}}
                        <span class="fw-bold" style="color:var(--g-700);font-size:14px;">{{ $company->jobs->count() }}</span>
                        <span style="font-size:11px;color:var(--n-500);"> job(s)</span>
                    </td>
                    <td style="padding:12px 16px;text-align:center;">
                        <span class="fw-bold" style="color:var(--g-700);font-size:14px;">{{ $hires->count() }}</span>
                        <span style="font-size:11px;color:var(--n-500);"> hired</span>
                    </td>
                    <td style="padding:12px 16px;text-align:center;">
                        <button type="button" class="btn btn-sm fw-semibold"
                            data-bs-toggle="modal"
                            data-bs-target="#hiresModal{{ $company->employer_nsrp_registrations_id }}"
                            style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;">
                            <i class="ph ph-eye me-1"></i> View
                        </button>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="6" class="text-center py-5" style="border:none;">
                        <i class="ph ph-x-circle" style="font-size:48px;color:var(--n-300);"></i>
                        <div class="mt-3 fw-semibold" style="color:var(--g-700);">No employers found</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($rows && $rows->hasPages())
    <div class="p-3 border-top" style="border-color:var(--n-200)!important;">
        {{ $rows->links() }}
    </div>
    @endif
</div>

{{-- The modals live outside the table. A <div> between two <tr> elements is not
     valid table markup and the browser hoists it out anyway, which is a change
     to the page nobody wrote. --}}
@foreach($rows ?? [] as $company)
@php $hires = $company->jobs->flatMap->applications; @endphp
{{-- The names, one modal per company. --}}
<div class="modal fade" id="hiresModal{{ $company->employer_nsrp_registrations_id }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-3">
            <div class="modal-header border-0" style="background:var(--g-600);">
                <h6 class="modal-title fw-bold text-white">
                    <i class="ph-fill ph-buildings me-2"></i>{{ $company->company_name ?? 'None' }}
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex flex-wrap align-items-baseline gap-3 mb-3">
                    <span>
                        <span class="fw-bold" style="color:var(--g-700);font-size:26px;">{{ $company->jobs->count() }}</span>
                        <span style="font-size:12px;color:var(--n-500);">job(s) offered</span>
                    </span>
                    <span>
                        <span class="fw-bold" style="color:var(--g-700);font-size:26px;">{{ $hires->count() }}</span>
                        <span style="font-size:12px;color:var(--n-500);">jobseeker(s) hired through PESO</span>
                    </span>
                </div>

                @if($hires->isEmpty())
                <div style="font-size:13px;color:var(--n-500);">
                    No jobseeker has been marked hired by this employer yet.
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="font-size:12px;">
                        <thead>
                            <tr style="background:var(--n-50);">
                                <th style="color:var(--g-700);">#</th>
                                <th style="color:var(--g-700);">Jobseeker</th>
                                <th style="color:var(--g-700);">Position</th>
                                <th style="color:var(--g-700);">Date Hired</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hireNumber = 0; @endphp
                            @foreach($company->jobs as $job)
                                @foreach($job->applications as $hire)
                                @php $hireNumber++; @endphp
                                <tr>
                                    <td style="color:var(--n-500);">{{ $hireNumber }}</td>
                                    <td style="font-weight:600;color:var(--g-700);">
                                        {{ trim(($hire->jobseeker->first_name ?? '') . ' ' . ($hire->jobseeker->surname ?? '')) ?: 'None' }}
                                    </td>
                                    <td>{{ $job->title ?? 'None' }}</td>
                                    <td>{{ $hire->hired_at?->format('M d, Y') ?? 'Not recorded' }}</td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach
