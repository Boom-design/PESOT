{{--
    Ang inactive nga employer — gipatay man sa inactivity sweep o sa staff mismo
    pinaagi sa Update.

    Lamesa, parehas sa Pre/Approved, aron usa ra ka porma ang tan-awon sa staff.
    Ang rason mahimong taas ug daghang laray, mao nga ang lamesa magpakita sa
    unang pipila ka pulong ug ang tibuok naa sa modal — kung dili, ang usa ka
    hataas nga tubag mo-inat sa laray ug mawala ang kaanindot sa lamesa.
--}}

<div class="card border-0 shadow-sm rounded-3 p-3 mb-3"
     style="border-left:3px solid var(--warn) !important;">
    <div style="font-size:12px;color:var(--n-700);">
        <i class="ph-fill ph-info me-1" style="color:var(--warn);"></i>
        <strong style="color:var(--g-700);">These accounts are inactive.</strong>
        Either they stopped posting vacancies and did not answer the status email,
        or a staff member switched them off. Their postings are hidden, and nothing
        was deleted. Read the reason and switch the account back on — the postings
        come back with it.
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr style="background:var(--g-600);">
                    <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                    <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Company Name</th>
                    <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Company Email</th>
                    <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Inactive Since</th>
                    <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Their Answer</th>
                    <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @if($employers->isEmpty())
                <tr>
                    <td colspan="6" class="text-center py-5" style="border:none;">
                        <i class="ph ph-check-circle" style="font-size:48px;color:var(--n-300);"></i>
                        <div class="mt-3 fw-semibold" style="color:var(--g-700);">No inactive employers</div>
                        <div style="font-size:12px;color:var(--n-500);">
                            Every employer has posted a vacancy recently or answered the status email.
                        </div>
                    </td>
                </tr>
                @endif
                @foreach($employers as $employer)
                @php
                    $companyRow = $employer;
                    $employer   = $companyRow->employer;
                    $nsrp       = $companyRow;
                    $labels   = ['still_hiring' => 'Still hiring', 'paused' => 'Paused for now', 'closed' => 'Closed down'];
                    $colors   = ['still_hiring' => 'var(--g-700)', 'paused' => 'var(--warn)', 'closed' => 'var(--danger)'];
                    $answered = (bool) $nsrp?->inactivity_responded_at;
                @endphp
                <tr style="font-size:13px;">
                    <td style="padding:12px 16px;color:var(--n-500);">
                        {{ $employers->firstItem() + $loop->index }}
                    </td>
                    <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                        {{ $nsrp->company_name ?? 'None' }}
                        @if($nsrp?->is_walk_in)
                            <span style="color:var(--warn);font-size:9px;font-weight:700;margin-left:4px;">WALK-IN</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px;color:var(--n-700);">
                        {{ $employer->email ?? 'None' }}
                    </td>
                    <td style="padding:12px 16px;color:var(--n-500);text-align:center;white-space:nowrap;">
                        {{ $nsrp?->dormant_at ? $nsrp->dormant_at->format('M d, Y') : 'None' }}
                    </td>
                    <td style="padding:12px 16px;max-width:260px;">
                        @if($answered)
                            @if($nsrp->inactivity_status)
                                <div class="fw-bold" style="font-size:11px;
                                     color:{{ $colors[$nsrp->inactivity_status] ?? 'var(--g-700)' }};">
                                    {{ $labels[$nsrp->inactivity_status] ?? $nsrp->inactivity_status }}
                                </div>
                            @endif
                            <div style="font-size:12px;color:var(--n-700);
                                        overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $nsrp->inactivity_response }}
                            </div>
                            <a href="#" style="font-size:11px;color:var(--g-600);text-decoration:none;"
                               data-bs-toggle="modal" data-bs-target="#answerModal{{ $companyRow->employer_nsrp_registrations_id }}">
                                Read full answer
                            </a>
                        @else
                            <span style="font-size:12px;color:var(--n-400);">
                                <i class="ph ph-clock me-1"></i>Not yet
                            </span>
                        @endif
                    </td>
                    <td style="padding:12px 16px;text-align:center;">
                        <form method="POST" action="{{ route('staff.employers.enable', $companyRow->employer_nsrp_registrations_id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm fw-semibold"
                                style="background:var(--g-600);color:#fff;border:none;
                                       border-radius:8px;font-size:12px;white-space:nowrap;">
                                <i class="ph-fill ph-lock-open me-1"></i>Enable
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($employers->hasPages())
    <div class="d-flex justify-content-between align-items-center px-3 py-3"
        style="border-top:1px solid var(--n-50);">
        <div style="font-size:12px;color:var(--n-500);">
            Showing {{ $employers->firstItem() }}–{{ $employers->lastItem() }}
            of {{ $employers->total() }} results
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0 gap-1">
                @foreach($employers->getUrlRange(1, $employers->lastPage()) as $page => $url)
                <li class="page-item {{ $page == $employers->currentPage() ? 'active' : '' }}">
                    <a class="page-link rounded-2"
                       style="{{ $page == $employers->currentPage()
                            ? 'background:var(--g-600);border-color:transparent;color:#fff;'
                            : 'border-color:var(--n-200);color:var(--g-700);' }}"
                       href="{{ $url }}">{{ $page }}</a>
                </li>
                @endforeach
            </ul>
        </nav>
    </div>
    @endif
</div>

{{-- Gawas sa <table> gyud: ang modal sulod sa usa ka laray dili gyud mogawas
     nga tarong, kay ang lamesa nay nagbuot sa pagpakita sa iyang mga anak. --}}
@foreach($employers as $employer)
    @php
        $companyRow = $employer;
        $employer   = $companyRow->employer;
        $nsrp       = $companyRow;
    @endphp
    @if($nsrp?->inactivity_responded_at)
    <div class="modal fade" id="answerModal{{ $companyRow->employer_nsrp_registrations_id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px;border:none;">
                <div class="modal-header" style="background:var(--g-600);border-radius:16px 16px 0 0;">
                    <h6 class="modal-title text-white fw-bold">
                        <i class="ph-fill ph-chat-centered-text me-2"></i>{{ $nsrp->company_name ?? 'Employer' }}
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div style="font-size:11px;color:var(--n-500);" class="mb-2">
                        Answered {{ $nsrp->inactivity_responded_at->format('M d, Y g:i A') }}
                        @if($nsrp->inactivity_status)
                            &mdash;
                            <strong style="color:var(--g-700);">
                                {{ ['still_hiring' => 'Still hiring', 'paused' => 'Paused for now', 'closed' => 'Closed down'][$nsrp->inactivity_status] ?? $nsrp->inactivity_status }}
                            </strong>
                        @endif
                    </div>
                    <div class="p-3 rounded-3" style="background:var(--n-50);border:1px solid var(--n-200);
                                font-size:13px;color:var(--n-700);white-space:pre-line;">{{ $nsrp->inactivity_response }}</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm fw-semibold" data-bs-dismiss="modal"
                        style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;">
                        Close
                    </button>
                    <form method="POST" action="{{ route('staff.employers.enable', $companyRow->employer_nsrp_registrations_id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm fw-semibold"
                            style="background:var(--g-600);color:#fff;border:none;border-radius:8px;">
                            <i class="ph-fill ph-lock-open me-1"></i>Enable account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
