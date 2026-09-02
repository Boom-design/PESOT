@extends('staff.layouts.app')

@section('content')

{{-- Walay tab row dinhi. Ang Employer Participants dili lain nga lugar —
     unsa may sulod sa usa ka event — ug ang laray sa event mismo ang mo-abli
     niini. Isip tab kinahanglan siya mangutana usa kung asa nga event, nga
     natubag na sa laray. --}}

{{-- Walay Attendance nga tab dinhi. Naa siya sa Reports, tab nga Attendance —
     usa ra ka listahan, usa ra ka ihap, uban ang Mark Attended. --}}

@if(request('view') === 'participants')

    {{-- EMPLOYER PARTICIPANTS VIEW --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1" style="color:var(--g-700);">
                <i class="ph-fill ph-users-three me-2" style="color:var(--g-600);"></i>Employer Participants
            </h5>
            <p class="mb-0" style="font-size:13px;color:var(--n-500);">List of employers per job fair event</p>
        </div>
    </div>

    {{-- EVENT FILTER --}}
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <select id="eventFilter" class="form-select form-select-sm"
            style="max-width:300px;border-color:var(--n-200);font-size:13px;"
            onchange="filterByEvent(this.value)">
            <option value="">— Select Event —</option>
            @foreach($allEvents as $ev)
            <option value="{{ $ev->job_fair_events_id }}"
                {{ request('event_id') == $ev->job_fair_events_id ? 'selected' : '' }}>
                {{ $ev->title }} ({{ $ev->event_date->format('M d, Y') }})
            </option>
            @endforeach
        </select>

        {{-- Local ug overseas nga employer sa usa ka listahan. Gipangayo sa
             Job Fair staff, 2026-08-23. --}}
        @if(request('event_id'))
        <select class="form-select form-select-sm"
            style="max-width:200px;border-color:var(--n-200);font-size:13px;"
            onchange="filterParticipantType(this.value)">
            <option value="all"      {{ $participantFilter === 'all'      ? 'selected' : '' }}>All (Local + Overseas)</option>
            <option value="local"    {{ $participantFilter === 'local'    ? 'selected' : '' }}>Local Only</option>
            <option value="overseas" {{ $participantFilter === 'overseas' ? 'selected' : '' }}>Overseas Only</option>
        </select>
        @endif
    </div>

    @if(request('event_id') && isset($participants))

        {{-- No send buttons and no confirmed-count line here any more.

             The reminder to employers who have not responded is sent by
             jobfair:send-confirmation-reminders once the event is within the
             reminder window, so there is nothing for staff to press and no way
             to forget. The count against the threshold reads as a warning on a
             list that is not about the ones being counted — this table holds
             the employers who accepted. --}}

        {{-- ── THE ROSTER, AGAINST THE CALENDAR ──

             Project manager, 2026-08-23: ten days before the fair the employer
             slots are meant to be filled, and that roster is what PESO submits
             to DOLE. Nothing here blocks an employer — a confirmation after
             that day is still taken, and marked. What the staff needs is to
             see the shortfall while there is still time to act on it. --}}
        @php
            $daysToEvent  = $event?->daysUntil();
            $doleDays     = (int) config('peso.jobfair.dole_cutoff_days_before');
            $target       = $event?->employer_capacity;
            $shortfall    = $target ? max($target - $confirmedCount, 0) : 0;
            $insideWindow = $daysToEvent !== null && $daysToEvent <= $doleDays && $daysToEvent >= 0;
        @endphp
        @if($event && $event->status !== 'completed')
        <div class="card border-0 shadow-sm rounded-3 p-3 mb-3"
             style="border-left:3px solid {{ $shortfall > 0 && $insideWindow ? 'var(--warn)' : 'var(--g-600)' }} !important;">
            <div style="font-size:12.5px;color:var(--n-700);">
                <i class="ph-fill ph-calendar-check me-1" style="color:var(--g-600);"></i>
                @if($daysToEvent > 0)
                    <strong style="color:var(--g-700);">{{ $daysToEvent }} day{{ $daysToEvent === 1 ? '' : 's' }} to the fair.</strong>
                @elseif($daysToEvent === 0)
                    <strong style="color:var(--g-700);">The fair is today.</strong>
                @endif
                {{ $confirmedCount }} confirmed{{ $target ? ' of a target of ' . $target : '' }},
                {{ $pendingCount }} still deciding, {{ $expiredCount }} lapsed.
            </div>

            @if($event->pastDoleCutoff())
                <div style="font-size:11.5px;color:var(--n-500);margin-top:5px;">
                    <i class="ph ph-paper-plane-tilt me-1"></i>
                    The list of participating companies was submitted to DOLE on
                    {{ $event->dole_cutoff_at->format('M d, Y') }}. Any employer who confirms
                    after that date is marked below and is not on the submitted list.
                </div>
            @elseif($insideWindow)
                <div style="font-size:11.5px;color:var(--warn);margin-top:5px;">
                    <i class="ph-fill ph-warning me-1"></i>
                    The roster goes to DOLE {{ $doleDays }} days before the fair
                    ({{ $event->doleCutoffDate()->format('M d, Y') }}).
                    @if($shortfall > 0)
                        {{ $shortfall }} more employer(s) needed to reach the target.
                    @endif
                </div>
            @elseif($shortfall > 0)
                <div style="font-size:11.5px;color:var(--n-500);margin-top:5px;">
                    <i class="ph ph-paper-plane-tilt me-1"></i>
                    {{ $shortfall }} more employer(s) to reach the target before the DOLE submission on
                    {{ $event->doleCutoffDate()->format('M d, Y') }}.
                </div>
            @endif
        </div>
        @endif

        {{-- ── INVITE MORE ──

             PESO Job Fair staff, 2026-08-23: when an employer does not answer
             they look for another one, but still in the industry the fair is
             after. So this list holds only employers this fair would have
             invited anyway — same cater, same target industries — who are not
             on it yet.

             It is usually empty, and that is correct: every eligible employer
             was invited the day the event was created, and one approved or
             classified later is swept in automatically. The empty state below
             says what is actually left to do. --}}
        @if($notYetInvited->isNotEmpty())
        <div class="mb-3">
            <button class="btn btn-sm fw-semibold" type="button"
                data-bs-toggle="collapse" data-bs-target="#inviteMoreCollapse"
                style="border:1px solid var(--n-200);color:var(--g-700);background:#fff;border-radius:8px;font-size:12px;">
                <i class="ph ph-user-plus me-1"></i>Invite more employers ({{ $notYetInvited->count() }} available)
                <i class="ph ph-caret-down ms-1"></i>
            </button>

            <div class="collapse mt-2" id="inviteMoreCollapse">
                <form method="POST" action="{{ route('staff.jobfair.events.inviteMore', request('event_id')) }}"
                      class="card border-0 shadow-sm rounded-3 p-3">
                    @csrf
                    <div style="font-size:11.5px;color:var(--n-500);" class="mb-2">
                        These employers match what this fair is looking for but are not on it yet.
                        Each gets {{ config('peso.jobfair.confirm_window_days') }} days to reply.
                    </div>

                    <div class="row g-2">
                        @foreach($notYetInvited as $candidate)
                        <div class="col-12 col-md-6">
                            <label class="d-flex align-items-start gap-2 p-2"
                                   style="border:1px solid var(--n-200);border-radius:8px;cursor:pointer;">
                                <input type="checkbox" name="employer_ids[]" class="form-check-input mt-1"
                                       value="{{ $candidate->employer_nsrp_registrations_id }}">
                                <span style="min-width:0;">
                                    <span class="fw-semibold d-block" style="color:var(--g-700);font-size:12.5px;">
                                        {{ $candidate->company_name }}
                                    </span>
                                    <span style="font-size:11px;color:var(--n-500);">
                                        {{ $candidate->is_overseas ? 'Overseas' : 'Local' }}
                                        &nbsp;•&nbsp; {{ $candidate->industry_group ?? 'No industry set' }}
                                    </span>
                                </span>
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-sm fw-semibold"
                                style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 16px;">
                            <i class="ph ph-paper-plane-tilt me-1"></i>Send invitations
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @elseif($event && $event->status !== 'completed' && ($expiredCount > 0 || $shortfall > 0))
        {{-- Kulang ang roster apan walay makuhaan. Ang staff angay masayod
             nga wala ni sayop sa sistema, ug unsa ang duha ka tinuod nga
             mahimo. Kung dili ni isulti, mangita siya ug buton nga wala. --}}
        <div class="card border-0 shadow-sm rounded-3 p-3 mb-3"
             style="border-left:3px solid var(--n-200) !important;">
            <div style="font-size:12px;color:var(--n-700);">
                <i class="ph ph-user-plus me-1" style="color:var(--n-500);"></i>
                <strong style="color:var(--g-700);">No more employers to invite.</strong>
                Every approved employer in
                @if($event->target_industries)
                    {{ implode(', ', $event->target_industries) }}
                @else
                    the industries this fair accepts
                @endif
                is already on this list. To reach more, either add another industry to this
                event, or have a new employer register and get their requirements approved —
                the system invites them to this fair the moment that happens.
            </div>
        </div>
        @endif

        {{-- PARTICIPANTS TABLE --}}
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr style="background:var(--g-600);">
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Company Name</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Company Email</th>
                            {{-- Duha ka "type" ang naa niini nga laray, mao nga
                                 ginganlan gyud ang matag usa. Ang "Type" lang
                                 nagpangutana kung asa niini. --}}
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Local or Overseas</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Establishment Type</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Industry</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Job Offers</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Invitation Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $p)
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:var(--n-500);">
                                {{ $participants->firstItem() + $loop->index }}
                            </td>
                            <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                                {{ $p->employer->company_name ?? 'None' }}
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">
                                {{-- Ang email naa sa users, dili sa
                                     employer_nsrp_registrations — walay email nga
                                     kolum didto, mao nga "None" gyud ni sukad. --}}
                                {{ $p->employer->employer->email ?? 'None' }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php $pIsOverseas = $p->employer->is_overseas ?? false; @endphp
                                <span class="fw-semibold"
                                    style="color:{{ $pIsOverseas ? 'var(--info)' : 'var(--g-700)' }};font-size:12px;">
                                    {{ $pIsOverseas ? 'Overseas' : 'Local' }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">
                                {{ $p->employer->employer_type ?? 'None' }}
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);font-size:12px;">
                                {{ $p->employer->industry_group ?? 'Not set' }}
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">
                                @php
                                    $offers = $employmentRequests->get($p->employer_id, collect());
                                    // Wala pa siya nagpili para niini nga fair — apan
                                    // basin naa siyay bakante. Mao gyud kana ang
                                    // gipangita sa staff, ug ang "No jobs selected yet"
                                    // nagtago sa tubag.
                                    $otherPostings = $employerOpenPostings->get($p->employer_id, collect());
                                @endphp
                                @forelse($offers as $req)
                                    <span class="me-1 mb-1" style="color:var(--g-700);font-size:11px;font-weight:600;">
                                        {{ $req->job->title ?? 'None' }}@if(!$loop->last),@endif
                                    </span>
                                @empty
                                    @if($otherPostings->isNotEmpty())
                                        @foreach($otherPostings as $posting)
                                            <span class="me-1 mb-1" style="color:var(--n-500);font-size:11px;font-weight:600;">
                                                {{ $posting->title }}@if(!$loop->last),@endif
                                            </span>
                                        @endforeach
                                        <div style="font-size:10px;color:var(--n-400);">
                                            Approved postings — not yet attached to this event.
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size:11px;">No job fair vacancies posted</span>
                                    @endif
                                @endforelse
                            </td>
                            {{-- Ang duha ka oo managlahi: ang lokal nga oo kay
                                 oo dayon, ang overseas nga oo naghulat pa sa
                                 pagpili sa SRA. Parehas silang naa niini nga
                                 lamesa, mao nga kinahanglan mailhan. --}}
                            <td style="padding:12px 16px;text-align:center;">
                                @php
                                    $statusColor = $p->confirmation_status === 'confirmed'
                                        ? 'var(--g-700)' : 'var(--warn)';
                                    $statusLabel = $p->confirmation_status === 'confirmed'
                                        ? 'Confirmed ✓' : 'Accepted — with SRA';
                                @endphp
                                <span class="fw-semibold" style="color:{{ $statusColor }};font-size:12px;">
                                    {{ $statusLabel }}
                                </span>
                                @if($p->confirmation_status === 'confirmed' && !$p->confirmedBeforeCutoff())
                                    <div style="font-size:10.5px;color:var(--warn);">
                                        after DOLE submission
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center"
                                style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                <i class="ph ph-users-three me-1"
                                   style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                                No employer has accepted the invitation to this fair yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($participants->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3"
                style="border-top:1px solid var(--n-50);">
                <div style="font-size:12px;color:var(--n-500);">
                    Showing {{ $participants->firstItem() }}–{{ $participants->lastItem() }}
                    of {{ $participants->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $participants->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                               href="{{ $participants->previousPageUrl() }}">
                                <i class="ph ph-caret-left"></i>
                            </a>
                        </li>
                        @foreach($participants->getUrlRange(1, $participants->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $participants->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $participants->currentPage()
                                    ? 'background:var(--g-600);border-color:transparent;color:#fff;'
                                    : 'border-color:var(--n-200);color:var(--g-700);' }}"
                               href="{{ $url }}">
                                {{ $page }}
                            </a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$participants->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                               href="{{ $participants->nextPageUrl() }}">
                                <i class="ph ph-caret-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="ph ph-users-three" style="font-size:48px;color:var(--n-300);"></i>
            <div class="mt-3 fw-semibold" style="color:var(--g-700);">Select an event to view participants</div>
        </div>
    @endif

@else

    {{-- EVENTS VIEW --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0" style="color:var(--g-700);">
                <i class="ph-fill ph-calendar-dots me-2" style="color:var(--g-600);"></i>List of Job Fair Events
            </h5>
        </div>
        <a href="{{ route('staff.jobfair.events.create') }}"
           class="btn btn-sm fw-semibold"
           style="background:var(--g-600);
                  color:#fff;border:none;border-radius:8px;padding:8px 18px;font-size:13px;">
            <i class="ph-fill ph-plus-circle me-1"></i> Create Event
        </a>
    </div>

    {{-- FILTER + SEARCH --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex gap-2 flex-wrap">
            @foreach(['upcoming' => 'Upcoming', 'ongoing' => 'Ongoing'] as $val => $label)
            <a href="{{ route('staff.jobfair.events', array_merge(request()->query(), ['status' => $val, 'page' => 1, 'view' => 'events'])) }}"
               class="btn btn-sm fw-semibold"
               style="{{ request('status','all') === $val
                   ? 'background:var(--g-600);color:#fff;border:none;'
                   : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
                   border-radius:8px;font-size:12px;padding:5px 16px;">
                {{ $label }}
            </a>
            @endforeach
        </div>
        <div class="input-group" style="max-width:260px;">
            <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
                <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
            </span>
            <input type="text" id="searchInput" class="form-control"
                placeholder="Search title or venue..."
                style="border-color:var(--n-200);font-size:13px;"
                value="{{ request('search') }}">
        </div>
    </div>

    {{-- TABLE --}}
    @if($events->isEmpty())
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
            <i class="ph ph-calendar-x" style="font-size:48px;color:var(--n-300);"></i>
            <div class="mt-3 fw-semibold" style="color:var(--g-700);">No events found</div>
            <div class="text-muted small mt-1">Create a new job fair event to get started</div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr style="background:var(--g-600);">
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Title</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Date</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Venue</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Employer Confirmed Invitation</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Status</th>
                            <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($events as $i => $event)
                        @php
                            $confirmed = $event->participants->where('confirmation_status','confirmed')->count();
                            // Read from config rather than a literal 3. The same number
                            // decides whether jobseekers may be notified at all, and a
                            // hardcoded copy here would go quietly wrong the day the
                            // office settles on 5.
                            $jobFairThreshold = config('peso.jobfair.min_confirmed_employers');
                        @endphp
                        <tr style="font-size:13px;">
                            <td style="padding:12px 16px;color:var(--n-500);">
                                {{ $events->firstItem() + $loop->index }}
                            </td>
                            <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                                {{ $event->title }}
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">
                                {{ $event->event_date->format('M d, Y') }}
                            </td>
                            <td style="padding:12px 16px;color:var(--n-700);">
                                {{ $event->venue }}
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                <span class="fw-semibold"
                                    style="color:{{ $confirmed >= $jobFairThreshold ? 'var(--g-700)' : 'var(--warn)' }}">
                                    {{ $confirmed }}
                                    @if($confirmed >= $jobFairThreshold)
                                        ✅
                                    @endif
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                @php
                                    $badge = [
                                        'upcoming'  => ['bg' => 'var(--g-600)', 'label' => 'Upcoming'],
                                        'ongoing'   => ['bg' => 'var(--warn)', 'label' => 'Ongoing'],
                                        'completed' => ['bg' => 'var(--g-700)', 'label' => 'Completed'],
                                    ][$event->status] ?? ['bg' => 'var(--n-500)', 'label' => ucfirst($event->status)];
                                @endphp
                                <span class="fw-semibold" style="color:{{ $badge['bg'] }};font-size:11px;">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;text-align:center;">
                                <a href="{{ route('staff.jobfair.events', ['view' => 'participants', 'event_id' => $event->job_fair_events_id]) }}"
                                   class="btn btn-sm fw-semibold me-1"
                                   style="border:1px solid var(--n-200);color:var(--g-700);
                                          background:#fff;border-radius:8px;font-size:12px;"
                                   title="View Participants">
                                    <i class="ph-fill ph-users-three"></i>
                                </a>
                                {{-- The megaphone shortcut to the SMS page used to sit here.
                                     It is reachable from Notification in the sidebar, and
                                     having it in three places made it unclear which button
                                     was the one that actually texted anybody. --}}
                                <a href="{{ route('staff.jobfair.events.edit', $event->job_fair_events_id) }}"
                                   class="btn btn-sm fw-semibold me-1"
                                   style="border:1px solid var(--n-200);color:var(--g-700);
                                          background:#fff;border-radius:8px;font-size:12px;"
                                   title="Update">
                                    <i class="ph-fill ph-pencil-simple"></i>
                                </a>
                                {{-- Walay Delete dinhi.

                                     Ang event nagdala ug imbitasyon, ug ang
                                     employer nga mitubag na. Ang pagpapas
                                     niini usa ka pindot ra ang gilay-on gikan
                                     sa Update, ug ang gipapas dili na
                                     mabalik. --}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($events->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3"
                style="border-top:1px solid var(--n-50);">
                <div style="font-size:12px;color:var(--n-500);">
                    Showing {{ $events->firstItem() }}–{{ $events->lastItem() }}
                    of {{ $events->total() }} results
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $events->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                               href="{{ $events->previousPageUrl() }}&status={{ request('status','all') }}&search={{ request('search') }}&view=events">
                                <i class="ph ph-caret-left"></i>
                            </a>
                        </li>
                        @foreach($events->getUrlRange(1, $events->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $events->currentPage() ? 'active' : '' }}">
                            <a class="page-link rounded-2"
                               style="{{ $page == $events->currentPage()
                                    ? 'background:var(--g-600);border-color:transparent;color:#fff;'
                                    : 'border-color:var(--n-200);color:var(--g-700);' }}"
                               href="{{ $url }}&status={{ request('status','all') }}&search={{ request('search') }}&view=events">
                                {{ $page }}
                            </a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$events->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);"
                               href="{{ $events->nextPageUrl() }}&status={{ request('status','all') }}&search={{ request('search') }}&view=events">
                                <i class="ph ph-caret-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>
    @endif

@endif

@push('scripts')
<script>
    function filterByEvent(eventId) {
        const url = new URL(window.location.href);
        url.searchParams.set('view', 'participants');
        url.searchParams.set('event_id', eventId);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    function filterParticipantType(type) {
        const url = new URL(window.location.href);
        url.searchParams.set('view', 'participants');
        url.searchParams.set('participant_filter', type);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    @if(request('view','events') === 'events')
    let searchTimer;
    document.getElementById('searchInput')?.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.set('search', this.value.trim());
            url.searchParams.set('page', 1);
            url.searchParams.set('view', 'events');
            window.location.href = url.toString();
        }, 500);
    });
    @endif
</script>
@endpush

@endsection