{{--
    Job Fair invitations — the "Job Fair Invitations" tab of Active Job Postings.

    Split out of the old standalone Job Fair page when the two were merged into
    one screen, so the tab markup stays readable.

    Expects: $pendingInvitations, $pastInvitations, $confirmedCountsPerEvent.
--}}

@if($pendingInvitations->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-4 text-center">
        <i class="ph ph-calendar-x" style="font-size:36px;color:var(--n-300);"></i>
        <div class="mt-2 fw-semibold" style="color:var(--g-700);font-size:13px;">No pending invitations</div>
        <div class="text-muted small">PESO will send you invitations for upcoming job fair events</div>
    </div>
@else
    <div class="d-flex flex-column gap-2 mb-2">
        @foreach($pendingInvitations as $invitation)
        <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;background:var(--g-600);
                                border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="ph-fill ph-calendar-dots text-white"></i>
                    </div>
                    <div>
                        <div class="fw-semibold" style="color:var(--g-700);font-size:14px;">
                            {{ $invitation->jobFair->title ?? 'None' }}
                            @if($invitation->isLapsed())
                            <span class="fw-semibold ms-1" style="color:var(--n-500);font-size:10px;">
                                Lapsed
                            </span>
                            @endif
                        </div>
                        <div style="font-size:12px;color:var(--n-500);">
                            <i class="ph ph-calendar-blank me-1"></i>{{ $invitation->jobFair->event_date?->format('M d, Y') ?? 'None' }}
                            &nbsp;•&nbsp;
                            <i class="ph ph-map-pin me-1"></i>{{ $invitation->jobFair->venue ?? 'None' }}
                        </div>

                        {{-- Ang deadline. Kaniadto walay gisulti ang card kung
                             hangtod kanus-a — ug ang employer nga wala mitubag
                             sulod sa usa ka semana walay paagi nga masayod nga
                             naa diay orasan. --}}
                        @php $daysLeft = $invitation->daysToRespond(); @endphp
                        @if($invitation->isLapsed())
                            <div style="font-size:11.5px;color:var(--warn);margin-top:3px;">
                                <i class="ph-fill ph-clock-countdown me-1"></i>
                                This invitation lapsed on {{ $invitation->expiresAt()?->format('M d, Y') ?? 'an earlier date' }},
                                so PESO is inviting other employers. You may still confirm below if you want to join.
                            </div>
                        @elseif($invitation->awaitingSelection())
                            <div style="font-size:11.5px;color:var(--warn);margin-top:3px;">
                                <i class="ph-fill ph-hourglass-medium me-1"></i>
                                You accepted this invitation on
                                {{ $invitation->responded_at?->format('M d, Y') ?? 'an earlier date' }}.
                                PESO is confirming which agencies this fair can hold — you will be
                                notified once your slot is confirmed.
                            </div>
                        @elseif($daysLeft !== null)
                            <div style="font-size:11.5px;color:{{ $daysLeft <= 2 ? 'var(--warn)' : 'var(--n-500)' }};margin-top:3px;">
                                <i class="ph ph-clock me-1"></i>
                                Please confirm by {{ $invitation->expiresAt()->format('M d, Y') }}
                                ({{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left).
                            </div>
                        @endif

                        {{-- Something to weigh the invitation against.

                             How many jobseekers registered today line up with
                             the vacancies this company would bring. A count, not
                             a list: no names are shown, and none of it is a
                             promise — the real applicants only exist once the
                             vacancy is live at the fair. Written plainly as a
                             suggestion so it is not read as a booking. --}}
                        @php $potential = $potentialApplicants[$invitation->job_fair_id] ?? null; @endphp
                        @if($potential && $potential['vacancies'] > 0)
                            <div class="mt-2 p-2 rounded-3"
                                 style="background:var(--n-50);border:1px dashed var(--n-200);max-width:460px;">
                                <div style="font-size:11.5px;color:var(--g-700);font-weight:600;">
                                    <i class="ph-fill ph-user-list me-1" style="color:var(--g-600);"></i>
                                    @if($potential['highly'] + $potential['qualified'] > 0)
                                        Around {{ $potential['highly'] + $potential['qualified'] }} jobseeker(s)
                                        may suit your {{ $potential['vacancies'] }} vacancy(s)
                                        — {{ $potential['highly'] }} highly qualified,
                                        {{ $potential['qualified'] }} qualified.
                                    @else
                                        No registered jobseeker matches your
                                        {{ $potential['vacancies'] }} vacancy(s) yet.
                                    @endif
                                </div>
                                <div style="font-size:10.5px;color:var(--n-500);margin-top:2px;">
                                    A suggestion based on jobseekers registered today, not a guaranteed
                                    turnout. Names are not shown here — you see your applicants once the
                                    vacancy is live at the fair.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div>
                    {{-- Walay "Job Fair Full" nga badge dinhi.
                         PESO Job Fair staff, 2026-08-23: walay maximum ang job
                         fair — ang sponsor ang nagsulti kung pila ang mahaom,
                         ug dili kana mahibaw-an sa sistema. Ang gipakita karon
                         kay ang gidaghanon nga ni-confirm, kasayoran ra, ug ang
                         Confirm nga buton bukas gihapon. --}}
                    @php
                        $eventConfirmedCount = $confirmedCountsPerEvent[$invitation->job_fair_id] ?? 0;
                    @endphp
                    @if($eventConfirmedCount > 0)
                        <div class="mb-1" style="font-size:11px;color:var(--n-500);">
                            {{ $eventConfirmedCount }} employer(s) confirmed so far
                        </div>
                    @endif

                    {{-- Mitubag na siya. Ang Confirm ug Decline nga buton dinhi
                         mao untay pagtanyag ug pagpili nga wala na — ang sunod
                         nga pagpili sa opisina na. --}}
                    @if($invitation->awaitingSelection())
                        <span class="fw-semibold" style="color:var(--warn);font-size:11px;">
                            <i class="ph-fill ph-hourglass-medium me-1"></i>Waiting for PESO
                        </span>
                    @else
                    <form action="{{ route('company.jobfair.respond', $invitation->job_fair_participants_id) }}"
                          method="POST" class="d-inline" id="confirmForm{{ $invitation->job_fair_participants_id }}">
                        @csrf
                        <input type="hidden" name="response" id="responseInput{{ $invitation->job_fair_participants_id }}">
                        <button type="button"
                            class="btn btn-sm fw-semibold me-1"
                            style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:11px;"
                            onclick="confirmResponse({{ $invitation->job_fair_participants_id }}, 'confirmed')">
                            <i class="ph ph-check me-1"></i>Confirm
                        </button>
                        <button type="button"
                            class="btn btn-sm fw-semibold"
                            style="background:var(--danger);color:#fff;border:none;border-radius:8px;font-size:11px;"
                            onclick="confirmResponse({{ $invitation->job_fair_participants_id }}, 'declined')">
                            <i class="ph ph-x me-1"></i>Decline
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($pendingInvitations->hasPages())
    <div class="d-flex justify-content-center mb-2">
        <ul class="pagination pagination-sm mb-0 gap-1">
            <li class="page-item {{ $pendingInvitations->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $pendingInvitations->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
            </li>
            @foreach($pendingInvitations->getUrlRange(1, $pendingInvitations->lastPage()) as $page => $url)
            <li class="page-item {{ $page == $pendingInvitations->currentPage() ? 'active' : '' }}">
                <a class="page-link rounded-2" style="{{ $page == $pendingInvitations->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}" href="{{ $url }}">{{ $page }}</a>
            </li>
            @endforeach
            <li class="page-item {{ !$pendingInvitations->hasMorePages() ? 'disabled' : '' }}">
                <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $pendingInvitations->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
            </li>
        </ul>
    </div>
    @endif
@endif

{{-- ── PAST INVITATIONS — collapsed by default, aron dili taason ang tab bisan daghan na nga na-resolve ── --}}
<div class="mt-3">
    <button class="btn btn-sm fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#pastInvitationsCollapse"
        style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;font-size:12px;">
        <i class="ph ph-clock-counter-clockwise me-1"></i> Past Invitations ({{ $pastInvitations->total() }})
        <i class="ph ph-caret-down ms-1"></i>
    </button>
    <div class="collapse mt-2" id="pastInvitationsCollapse">
        @if($pastInvitations->isEmpty())
            <div class="text-muted small p-2">No past invitations yet.</div>
        @else
            <div class="d-flex flex-column gap-2">
                @foreach($pastInvitations as $invitation)
                <div class="card border-0 shadow-sm rounded-3 p-2 px-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div style="font-size:12.5px;">
                            <span class="fw-semibold" style="color:var(--g-700);">{{ $invitation->jobFair->title ?? 'None' }}</span>
                            <span style="color:var(--n-500);"> — {{ $invitation->jobFair->event_date?->format('M d, Y') ?? 'None' }}</span>
                        </div>
                        @if($invitation->confirmation_status === 'confirmed')
                            <span class="fw-semibold" style="color:var(--g-700);font-size:10.5px;">
                                <i class="ph-fill ph-check-circle me-1"></i>Confirmed
                            </span>
                        @elseif($invitation->wasNotSelected())
                            {{-- Dili "Declined". Mitubag siyag oo; ang opisina
                                 ang wala midala kaniya, ug ang duha managlahi. --}}
                            <span class="fw-semibold" style="color:var(--n-500);font-size:10.5px;">
                                <i class="ph-fill ph-minus-circle me-1"></i>Not selected by PESO
                            </span>
                        @elseif($invitation->confirmation_status === 'expired')
                            <span class="fw-semibold" style="color:var(--n-500);font-size:10.5px;">
                                <i class="ph-fill ph-clock-countdown me-1"></i>Lapsed
                            </span>
                        @else
                            <span class="fw-semibold" style="color:var(--danger);font-size:10.5px;">
                                <i class="ph-fill ph-x-circle me-1"></i>Declined
                            </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            @if($pastInvitations->hasPages())
            <div class="d-flex justify-content-center mt-2">
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item {{ $pastInvitations->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $pastInvitations->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                    </li>
                    @foreach($pastInvitations->getUrlRange(1, $pastInvitations->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $pastInvitations->currentPage() ? 'active' : '' }}">
                        <a class="page-link rounded-2" style="{{ $page == $pastInvitations->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}" href="{{ $url }}">{{ $page }}</a>
                    </li>
                    @endforeach
                    <li class="page-item {{ !$pastInvitations->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $pastInvitations->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                    </li>
                </ul>
            </div>
            @endif
        @endif
    </div>
</div>
