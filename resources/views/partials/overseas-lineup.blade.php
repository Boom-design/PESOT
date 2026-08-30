{{--
    Invite Overseas Agencies — which overseas agencies the SRA is taking to a fair.

    PESO Project Manager, 2026-08-26: an overseas agency does not walk into a
    job fair. The SRA clears it with the head of the office first, then adds it
    here — the permission itself is given in the room, and this line is all the
    system can hold of it, so it is required.

    Lives inside the SRA's Job Fair tab as a panel of its own. It used to be a
    tab called "Overseas Line-up", which said nothing about what it was for.
    Named after the thing you do here instead.

    Needs: $lineupEvents, $lineupEvent, $lineupAvailable, $lineupOnTheList.
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

    {{-- EVENT PICKER --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <label class="peso-label mb-2">Job fair event</label>
            <div class="d-flex gap-2 flex-wrap">
                @foreach($lineupEvents as $e)
                    {{-- lineup_event, dili event_id: kining page naay kaugalingon
                         nga paginator para sa events ug sa postings, ug ang usa
                         ka parehas nga ngalan mokuha sa lain. Ang panel gidala
                         sad, kay ang pagpili ug fair dili pagbiya sa panel. --}}
                    <a href="{{ route('staff.inhouse.jobfair', ['panel' => 'invite', 'lineup_event' => $e->job_fair_events_id]) }}"
                       class="btn btn-sm fw-semibold"
                       style="{{ $lineupEvent && $lineupEvent->job_fair_events_id === $e->job_fair_events_id
                           ? 'background:var(--g-600);color:#fff;border:none;'
                           : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
                           border-radius:20px;font-size:12px;padding:5px 16px;">
                        {{ $e->title }}
                        <span style="opacity:0.75;">· {{ $e->event_date->format('M d, Y') }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @if($lineupEvent)

        {{-- ── ALREADY ON THE LIST ── --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
            <div class="card-header border-0 py-2 px-3" style="background:var(--g-600);border-radius:12px 12px 0 0;">
                <h6 class="mb-0 fw-bold text-white" style="font-size:13px;">
                    <i class="ph ph-list-checks me-2"></i>Already invited ({{ $lineupOnTheList->count() }})
                </h6>
            </div>

            @if($lineupOnTheList->isEmpty())
                <div class="p-4 text-center" style="color:var(--n-500);font-size:13px;">
                    No overseas agency has been invited to this fair yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="background:var(--n-50);font-size:12px;border:none;padding:10px 16px;color:var(--g-700);">Agency</th>
                                <th style="background:var(--n-50);font-size:12px;border:none;padding:10px 16px;color:var(--g-700);">Response</th>
                                <th style="background:var(--n-50);font-size:12px;border:none;padding:10px 16px;color:var(--g-700);">Invited by</th>
                                <th style="background:var(--n-50);font-size:12px;border:none;padding:10px 16px;color:var(--g-700);">Permission recorded</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lineupOnTheList as $participant)
                            <tr style="font-size:13px;">
                                <td style="padding:10px 16px;font-weight:600;color:var(--g-700);">
                                    {{ $participant->employer->company_name ?? 'None' }}
                                </td>
                                <td style="padding:10px 16px;text-transform:capitalize;color:var(--n-700);">
                                    {{ $participant->confirmation_status }}
                                </td>
                                <td style="padding:10px 16px;color:var(--n-700);">
                                    {{ $participant->invitedBy->full_name
                                        ?? $participant->invitedBy->first_name
                                        ?? 'Invited by the system' }}
                                    <div style="font-size:11px;color:var(--n-500);">
                                        {{ optional($participant->invited_at)->format('M d, Y') }}
                                    </div>
                                </td>
                                <td style="padding:10px 16px;color:var(--n-700);">
                                    {{ $participant->permission_note ?: '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ── ADD MORE ── --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header border-0 py-2 px-3" style="background:var(--g-600);border-radius:12px 12px 0 0;">
                <h6 class="mb-0 fw-bold text-white" style="font-size:13px;">
                    <i class="ph ph-plus-circle me-2"></i>Agencies you can invite ({{ $lineupAvailable->count() }})
                </h6>
            </div>
            <div class="card-body p-3">

                @if($lineupAvailable->isEmpty())
                    <div class="text-center py-4" style="color:var(--n-500);font-size:13px;">
                        Every eligible overseas agency has already been invited to this fair.
                    </div>
                @else
                    <form action="{{ route('staff.jobfair.overseas.invite', $lineupEvent->job_fair_events_id) }}" method="POST">
                        @csrf

                        <div class="row g-2 mb-3">
                            @foreach($lineupAvailable as $employer)
                            <div class="col-md-6">
                                <label class="d-flex align-items-start gap-2 p-2 rounded-3"
                                       style="border:1px solid var(--n-200);background:var(--n-0);cursor:pointer;">
                                    <input type="checkbox" name="employer_ids[]"
                                           value="{{ $employer->employer_nsrp_registrations_id }}"
                                           class="form-check-input mt-1" style="flex-shrink:0;">
                                    <span>
                                        <span style="font-size:13px;font-weight:600;color:var(--g-700);">
                                            {{ $employer->company_name }}
                                        </span>
                                        <span class="d-block" style="font-size:11px;color:var(--n-500);">
                                            {{ $employer->industry_group ?: 'No industry group set' }}
                                        </span>
                                    </span>
                                </label>
                            </div>
                            @endforeach
                        </div>

                        {{-- The permission itself is given in the office. This line is
                             all the system can hold of it, so it is required. --}}
                        <label class="peso-label">Permission recorded *</label>
                        <input type="text" name="permission_note"
                               class="form-control peso-input @error('permission_note') is-invalid @enderror"
                               value="{{ old('permission_note') }}"
                               maxlength="255"
                               placeholder="e.g. Cleared by the PESO head on Aug 26, 2026">
                        @error('permission_note')
                            <div class="invalid-feedback" style="font-size:11px;">{{ $message }}</div>
                        @enderror

                        @error('employer_ids')
                            <div style="font-size:11px;color:var(--danger);margin-top:6px;">{{ $message }}</div>
                        @enderror

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-peso px-4">
                                <i class="ph ph-paper-plane-tilt me-1"></i> Invite to this fair
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        </div>

    @endif
@endif
