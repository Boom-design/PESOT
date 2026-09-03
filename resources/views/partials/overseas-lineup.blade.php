{{--
    Invite Overseas Agencies — which overseas agencies the SRA is taking to a fair.

    PESO Project Manager, 2026-08-26: an overseas agency does not walk into a
    job fair. The SRA clears it with the head of the office first, then adds it
    here — the permission itself is given in the room, and this line is all the
    system can hold of it, so it is required.

    This is the whole of the SRA's Job Fair tab. Pick the fair, pick the industry
    it is asking for, and one table lists every overseas agency that fits —
    invited already or not. One table, because the agency does not move: the
    invitation appears in its row.

    Needs: $lineupEvents, $lineupEvent, $lineupRows, $lineupOnTheList,
           $lineupAwaiting, $lineupIndustries, $lineupIndustry, $lineupUninvited.
--}}
<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-envelope-simple me-2" style="color:var(--g-600);"></i>
        Invite Overseas Agencies
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">
        An overseas agency is never invited to a job fair on its own. Clear each one with the head
        of the office first, then invite them here — your name and what you were told stay
        on the record.
    </p>
</div>

@if($lineupEvents->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="ph ph-calendar-x" style="font-size:48px;color:var(--n-300);"></i>
        <div class="mt-3 fw-semibold" style="color:var(--g-700);">No upcoming job fair takes overseas employers</div>
        <div class="text-muted small mt-1">
            A fair appears here once the Job Fair desk creates one that caters to overseas employers.
        </div>
    </div>
@else

    {{-- ── THE TWO PICKERS, SIDE BY SIDE ──

         Pick the fair, then pick the industry it is asking for. Two selects on
         one line, because they are one question: which agencies am I looking at.

         The industry choices are the fair's own target industries. A fair that
         names none takes every industry, so the choices are then whatever the
         eligible agencies are in — a list with nothing behind it is not a
         choice worth offering.

         lineup_event, dili event_id: kining page naay kaugalingon nga paginator
         para sa events ug sa postings, ug ang usa ka parehas nga ngalan mokuha
         sa lain. --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="peso-label mb-2" for="lineupEventPicker">Job fair event</label>
                    <select id="lineupEventPicker" class="form-select form-select-sm"
                            style="border-color:var(--n-200);font-size:12.5px;border-radius:8px;">
                        @foreach($lineupEvents as $e)
                        <option value="{{ $e->job_fair_events_id }}"
                            {{ $lineupEvent && $lineupEvent->job_fair_events_id === $e->job_fair_events_id ? 'selected' : '' }}>
                            {{ $e->title }} — {{ $e->event_date->format('M d, Y') }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="peso-label mb-2" for="lineupIndustryPicker">Industry</label>
                    <select id="lineupIndustryPicker" class="form-select form-select-sm"
                            style="border-color:var(--n-200);font-size:12.5px;border-radius:8px;"
                            {{ ($lineupIndustries ?? collect())->isEmpty() ? 'disabled' : '' }}>
                        <option value="">
                            {{ ($lineupIndustries ?? collect())->isEmpty()
                                ? 'No industry to choose from'
                                : 'All industries this fair wants' }}
                        </option>
                        @foreach($lineupIndustries ?? [] as $group)
                        <option value="{{ $group }}" {{ ($lineupIndustry ?? null) === $group ? 'selected' : '' }}>
                            {{ $group }}
                        </option>
                        @endforeach
                    </select>
                    <div class="mt-1" style="font-size:11px;color:var(--n-500);">
                        @if($lineupEvent && ($lineupEvent->target_industries ?? null))
                            This fair asks for {{ count((array) $lineupEvent->target_industries) }}
                            industry(ies). An agency that has not recorded its industry is never listed —
                            there is nothing to match it against.
                        @else
                            This fair names no target industry, so every approved overseas agency is eligible.
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($lineupEvent)

        {{-- ── WAITING FOR A DECISION ──

             Ang ahensya nga mitubag ug oo, apan wala pa nadesisyonan. Una siya
             sa panid kay siya ra ang nagpaabot ug lihok — ang listahan sa ubos
             basahon, kini aksyonan.

             PESO SRA, 2026-09-01: ang imbitasyon ug ang lugar sa fair duha ka
             desisyon. Gipangayo ang permiso sa pangulo sa dili pa moadto ang
             imbitasyon; karon, kung kinsa ang tinuod nga dad-on, ang desk na
             ang mopili gikan sa mitubag. --}}
        @if($lineupAwaiting->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
            <div class="card-header border-0 py-2 px-3" style="background:var(--warn);border-radius:12px 12px 0 0;">
                <h6 class="mb-0 fw-bold text-white" style="font-size:13px;">
                    <i class="ph ph-hourglass-medium me-2"></i>Waiting for your decision ({{ $lineupAwaiting->count() }})
                </h6>
            </div>
            <div class="card-body p-3 d-flex flex-column gap-2">
                @foreach($lineupAwaiting as $waiting)
                <div class="rounded-3 p-3" style="border:1px solid var(--n-200);">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="fw-semibold" style="color:var(--g-700);font-size:14px;">
                                {{ $waiting->employer->company_name ?? 'None' }}
                            </div>
                            <div style="font-size:11.5px;color:var(--n-500);margin-top:2px;">
                                Accepted {{ optional($waiting->responded_at)->format('M d, Y') ?? 'None' }}
                                &nbsp;&bull;&nbsp;
                                Invited by
                                {{ $waiting->invitedBy->full_name
                                    ?? $waiting->invitedBy->first_name
                                    ?? 'the system' }}
                            </div>
                            @if($waiting->permission_note)
                            <div style="font-size:11.5px;color:var(--n-500);margin-top:2px;">
                                <i class="ph ph-note me-1"></i>{{ $waiting->permission_note }}
                            </div>
                            @endif
                        </div>
                        <div class="d-flex gap-2 align-items-start">
                            <form action="{{ route('staff.jobfair.overseas.decide', $waiting->job_fair_participants_id) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="decision" value="confirmed">
                                <button type="submit" class="btn btn-sm fw-semibold"
                                    style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:11.5px;">
                                    <i class="ph ph-check me-1"></i>Bring to fair
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm fw-semibold"
                                data-bs-toggle="collapse"
                                data-bs-target="#dropAgency{{ $waiting->job_fair_participants_id }}"
                                style="border:1px solid var(--danger);color:var(--danger);background:#fff;border-radius:8px;font-size:11.5px;">
                                <i class="ph ph-minus-circle me-1"></i>Not selected
                            </button>
                        </div>
                    </div>

                    {{-- Ang rason gikinahanglan. Ang ahensya mitubag ug oo, ug
                         ang pagbalibad nga walay gisulti mao ang pagpasabot nga
                         wala siyay angay masayran. --}}
                    <div class="collapse mt-3" id="dropAgency{{ $waiting->job_fair_participants_id }}">
                        <form action="{{ route('staff.jobfair.overseas.decide', $waiting->job_fair_participants_id) }}"
                              method="POST">
                            @csrf
                            <input type="hidden" name="decision" value="not_selected">
                            <label class="form-label fw-semibold" style="color:var(--danger);font-size:12px;">
                                Why is this agency not being brought to the fair?
                            </label>
                            <textarea name="reason" rows="2" required maxlength="255"
                                class="form-control mb-2"
                                style="border:1px solid var(--n-200);border-radius:8px;font-size:13px;"
                                placeholder="The agency is told this reason - write what they should know."></textarea>
                            <button type="submit" class="btn btn-sm fw-semibold"
                                style="background:var(--danger);color:#fff;border:none;border-radius:8px;font-size:11.5px;">
                                <i class="ph ph-minus-circle me-1"></i>Confirm not selected
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── THE ONE TABLE ──

             Every overseas agency this fair can take, whether it has been
             invited or not. There used to be two tables — one of agencies you
             could invite and one of agencies already invited — and pressing the
             button moved a row from the bottom table to the top one, so the
             desk lost the line it was reading.

             Nothing moves now. The row stays where it is and the last column
             changes: a Send Invitation button while there is nothing to say,
             and the agency's own answer once there is. --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header border-0 py-2 px-3" style="background:var(--g-600);border-radius:12px 12px 0 0;">
                <h6 class="mb-0 fw-bold text-white" style="font-size:13px;">
                    <i class="ph ph-buildings me-2"></i>Overseas agencies for this fair ({{ $lineupRows->count() }})
                    @if($lineupIndustry ?? null)
                    <span style="font-weight:500;opacity:0.85;"> · {{ $lineupIndustry }}</span>
                    @endif
                    @if(($lineupUninvited ?? 0) > 0)
                    <span style="font-weight:500;opacity:0.85;"> · {{ $lineupUninvited }} not yet invited</span>
                    @endif
                </h6>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="background:var(--n-50);font-size:12px;border:none;padding:10px 16px;color:var(--g-700);">#</th>
                            <th style="background:var(--n-50);font-size:12px;border:none;padding:10px 16px;color:var(--g-700);">Company Name</th>
                            <th style="background:var(--n-50);font-size:12px;border:none;padding:10px 16px;color:var(--g-700);">Company Email</th>
                            <th style="background:var(--n-50);font-size:12px;border:none;padding:10px 16px;color:var(--g-700);">Contact Number</th>
                            <th style="background:var(--n-50);font-size:12px;border:none;padding:10px 16px;color:var(--g-700);">Industry</th>
                            <th style="background:var(--n-50);font-size:12px;border:none;padding:10px 16px;color:var(--g-700);text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lineupRows as $row)
                        @php
                            $agency      = $row->employer;
                            $participant = $row->participant;
                        @endphp
                        <tr style="font-size:13px;">
                            <td style="padding:10px 16px;color:var(--n-500);">{{ $loop->iteration }}</td>
                            <td style="padding:10px 16px;font-weight:600;color:var(--g-700);">
                                {{ $agency->company_name ?? 'None' }}
                                @if($agency->contact_person)
                                <div style="font-size:11px;color:var(--n-500);font-weight:400;">
                                    {{ $agency->contact_person }}{{ $agency->position_title ? ' — ' . $agency->position_title : '' }}
                                </div>
                                @endif
                            </td>
                            <td style="padding:10px 16px;color:var(--n-700);">
                                {{ $agency->employer->email ?? 'None' }}
                            </td>
                            <td style="padding:10px 16px;color:var(--n-700);">
                                {{ $agency->mobile_number ?: 'None' }}
                            </td>
                            <td style="padding:10px 16px;color:var(--n-700);">
                                {{ $agency->industry_group ?: 'None' }}
                            </td>
                            <td style="padding:10px 16px;text-align:center;">
                                @if($participant)
                                    {{-- Already invited. The button is gone because
                                         the invitation cannot be sent twice; what
                                         the agency said back takes its place. --}}
                                    @php
                                        $lineupLabels = [
                                            'pending'      => ['Awaiting reply',       'var(--warn)'],
                                            'accepted'     => ['Accepted — your call', 'var(--warn)'],
                                            'confirmed'    => ['In the fair',          'var(--g-700)'],
                                            'not_selected' => ['Not selected',         'var(--n-500)'],
                                            'declined'     => ['Agency declined',      'var(--danger)'],
                                            'expired'      => ['No reply — lapsed',    'var(--n-500)'],
                                        ];
                                        [$lineupLabel, $lineupColor] = $lineupLabels[$participant->confirmation_status]
                                            ?? [ucfirst($participant->confirmation_status), 'var(--n-700)'];
                                    @endphp
                                    <span class="fw-semibold" style="color:{{ $lineupColor }};font-size:12.5px;">
                                        {{ $lineupLabel }}
                                    </span>
                                    <div style="font-size:10.5px;color:var(--n-500);">
                                        Invited {{ optional($participant->invited_at)->format('M d, Y') ?? 'None' }}
                                        by {{ $participant->invitedBy->full_name
                                                ?? $participant->invitedBy->first_name
                                                ?? 'the system' }}
                                    </div>
                                    @if($participant->permission_note)
                                    <div style="font-size:10.5px;color:var(--n-500);">
                                        <i class="ph ph-note me-1"></i>{{ $participant->permission_note }}
                                    </div>
                                    @endif
                                @else
                                    <button type="button" class="btn btn-sm fw-semibold"
                                        data-bs-toggle="modal"
                                        data-bs-target="#inviteAgency{{ $agency->employer_nsrp_registrations_id }}"
                                        style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:11.5px;white-space:nowrap;">
                                        <i class="ph ph-paper-plane-tilt me-1"></i>Send Invitation
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding:36px 16px;color:var(--n-500);font-size:13px;">
                                <i class="ph ph-tray" style="font-size:40px;color:var(--n-300);display:block;margin-bottom:8px;"></i>
                                @if($lineupIndustry ?? null)
                                    No approved overseas agency is registered under {{ $lineupIndustry }}.
                                @else
                                    No approved overseas agency matches what this fair is asking for.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── ONE MODAL PER UNINVITED AGENCY ──

             Outside the table: a <div> between two <tr> elements is not valid
             table markup and the browser moves it anyway.

             The permission is asked for here rather than once at the bottom of
             the page, because it is not one permission for the list — it is
             what the head of the office said about this agency. --}}
        @foreach($lineupRows as $row)
        @continue($row->participant)
        @php $agency = $row->employer; @endphp
        <div class="modal fade" id="inviteAgency{{ $agency->employer_nsrp_registrations_id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content border-0 rounded-3">
                    <form action="{{ route('staff.jobfair.overseas.invite', $lineupEvent->job_fair_events_id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="employer_ids[]" value="{{ $agency->employer_nsrp_registrations_id }}">

                        <div class="modal-header border-0" style="background:var(--g-600);">
                            <h6 class="modal-title fw-bold text-white">
                                <i class="ph-fill ph-envelope-simple me-2"></i>Invite {{ $agency->company_name }}
                            </h6>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body p-4">
                            <div style="font-size:12.5px;color:var(--n-500);" class="mb-3">
                                The invitation goes to {{ $agency->employer->email ?? 'this agency' }} for
                                <strong style="color:var(--g-700);">{{ $lineupEvent->title }}</strong>
                                on {{ $lineupEvent->event_date->format('M d, Y') }}. They have
                                {{ (int) config('peso.jobfair.confirm_window_days') }} days to answer.
                            </div>

                            {{-- The permission itself is given in the office. This
                                 line is all the system can hold of it, so it is
                                 required. --}}
                            <label class="peso-label">Permission recorded *</label>
                            <input type="text" name="permission_note" required maxlength="255"
                                   class="form-control peso-input"
                                   placeholder="e.g. Cleared by the PESO head on Aug 26, 2026">
                        </div>

                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-sm fw-semibold" data-bs-dismiss="modal"
                                style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-peso px-4">
                                <i class="ph ph-paper-plane-tilt me-1"></i> Send Invitation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

    @endif
@endif

@push('scripts')
<script>
    // Picking a fair reloads the page carrying the choice in the URL, so the
    // list below and anything the desk submits belong to the same fair. The
    // industry is dropped: it belongs to the fair that was showing, and the new
    // fair may not ask for it at all.
    document.getElementById('lineupEventPicker')?.addEventListener('change', function () {
        const url = new URL(window.location.href);
        url.searchParams.set('lineup_event', this.value);
        url.searchParams.delete('lineup_industry');
        window.location.href = url.toString();
    });

    document.getElementById('lineupIndustryPicker')?.addEventListener('change', function () {
        const url = new URL(window.location.href);
        if (this.value) {
            url.searchParams.set('lineup_industry', this.value);
        } else {
            url.searchParams.delete('lineup_industry');
        }
        window.location.href = url.toString();
    });
</script>
@endpush
