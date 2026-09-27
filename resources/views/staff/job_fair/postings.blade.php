@extends('staff.layouts.app')

@section('content')

{{-- Ang teksto naay max-width ug ang buton naay ms-auto. Kung wala, ang
     paragrapo mokaon sa tibuok laray ug ang buton mahulog sa sunod nga linya —
     mao nga makita siya sa wala imbis sa tuong ngilit. --}}
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div style="max-width:640px;">
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">
            <i class="ph-fill ph-briefcase me-2" style="color:var(--g-600);"></i>Job Fair Vacancies
        </h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">
            The employers invited to a fair, by what they answered, and the vacancies each
            would bring. Nothing to press: every vacancy a fair takes is posted
            {{ \App\Support\JobFairPostingWindow::daysBefore() }} days before that fair.
        </p>
    </div>

    {{-- Ang employer nga niduol sa adlaw sa fair, dala ang iyang papel ug ang
         iyang bakante. Naa siya dinhi ug dili sa Employers kay usa ra ang
         gibuhat sa Job Fair desk sa employer: ang pagdala kaniya sa fair. --}}
    <a href="{{ route('staff.employers.walkin') }}"
       class="btn btn-sm fw-semibold ms-auto flex-shrink-0"
       style="background:var(--g-600);color:#fff;border:none;border-radius:8px;
              font-size:12px;padding:8px 16px;white-space:nowrap;">
        <i class="ph-fill ph-storefront me-1"></i> Walk-in Employer
    </a>
</div>

<div class="d-flex align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2">
        {{-- Ang tab mao ang tubag sa employer, dili ang kahimtang sa
             bakante. "Waiting for a fair" ug "Posted" nagtubag sa lain nga
             pangutana kay sa gipangutana sa desk dinhi. --}}
        @foreach([
            'pending'  => 'Pending Invitation',
            'accepted' => 'Accepted Invitation',
            'declined' => 'Declined Invitation',
        ] as $val => $label)
        <a href="{{ route('staff.jobfair.postings', array_merge(request()->query(), ['invite' => $val, 'page' => 1])) }}"
           class="btn btn-sm fw-semibold"
           style="{{ $invite === $val
               ? 'background:var(--g-600);color:#fff;border:none;'
               : 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;' }}
               border-radius:8px;font-size:12px;padding:5px 16px;">
            {{ $label }}
            <span class="ms-1 fw-bold">({{ $inviteCounts[$val] ?? 0 }})</span>
        </a>
        @endforeach
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        {{-- Ang fair, pilion kausa alang sa tulo ka tab.
             Ang Job Fair nga kolum kaniadto nagsulti niini kada laray; kining
             usa ka kahon nagsulti niini kausa, ug ang lamesa nagpabilin nga
             tulo lang ka pangutana: kinsa, unsa ang dad-on, ug pila. --}}
        <select id="eventFilter" class="form-select form-select-sm"
                style="max-width:260px;border-color:var(--n-200);font-size:12.5px;border-radius:8px;">
            <option value="">All job fair events</option>
            @foreach($fairOptions as $fair)
                <option value="{{ $fair->job_fair_events_id }}"
                    {{ (int) $eventId === $fair->job_fair_events_id ? 'selected' : '' }}>
                    {{ $fair->title }} · {{ $fair->event_date->format('M d, Y') }}
                </option>
            @endforeach
        </select>

        {{-- Ang sala nga gigamit sa dili pa mo-post ug tinapok: ang fair nga
             para sa Education modawat sa Education ra, mao nga salaan ang
             listahan hangtod nga ang nahibilin mao na ang i-post. --}}
        <select id="industryFilter" class="form-select form-select-sm"
                style="max-width:220px;border-color:var(--n-200);font-size:12.5px;border-radius:8px;">
            <option value="">All industries</option>
            @foreach($industries as $group)
                <option value="{{ $group }}" {{ $industry === $group ? 'selected' : '' }}>{{ $group }}</option>
            @endforeach
        </select>

        {{-- Ang duha ka PWD nga pill gitangtang. Ang lamesa naay Accepts PWD
             nga kolum, mao nga ang tubag makita na sa matag laray nga
             gisalaan — usa ka sala nga nagtago ug mga laray aron ipakita ang
             butang nga makita na sa nawala nga laray. --}}
    </div>

    {{-- Ang search sa tuong ngilit gyud. Ang ms-auto mao ang nagtulak kaniya:
         ang tab ug ang sala magkuyog sa wala, ug ang usa ka kahon nga naglutaw
         sa tuo mas limpyo basahon kay sa kwatro ka butang nga nagsigpit. --}}
    <div class="input-group ms-auto" style="max-width:260px;">
        <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
            <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
        </span>
        <input type="text" id="searchInput" class="form-control"
            placeholder="Search title or company..."
            style="border-color:var(--n-200);font-size:13px;"
            value="{{ request('search') }}">
    </div>
</div>

{{-- ── USA KA BUTON ──
     Ang fair mismo ang nagsala. Gihimo na ang desisyon sa dihang gihimo ang
     event — industriya, PWD, lokal o overseas — mao nga ang desk dili na
     magbasa ug usa-usa nga bakante. Pilia ang fair, tan-awa pila ang mosulod,
     ug i-post silang tanan. Ang wala mohaom maghulat sa fair nga modawat nila;
     wala silay nadawat nga pagbalibad ug walay nahibaw-an ang employer.

     PESO Job Fair staff, 2026-09-04: naa ni sa Accepted Invitation, dili sa
     Pending. Ang bakante nga i-post iya sa employer nga miingon ug oo, ug ang
     listahan sa mga miingon ug oo anaa dinhi. Sa Pending nga tab, ang desk
     motan-aw sa buton nga nagdala sa bakante sa mga tawo nga wala pa gani
     mitubag. --}}
@if($invite === 'accepted' && $waitingTotal > 0 && $events->isEmpty())
{{-- Ang buton nagkinahanglan ug fair nga kasudlan. Kung walay upcoming nga
     event, ang lugar dili magpabilin nga blangko: ang desk mangita sa buton
     ug maghunahuna nga nabuak siya, nga ang tinuod nga tubag mao nga wala pa
     gyud siyay gihimo nga fair. --}}
<div class="card border-0 shadow-sm rounded-3 p-3 mb-3"
     style="background:var(--warn-bg);border:1px solid var(--warn-br) !important;">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <i class="ph-fill ph-warning-circle" style="color:var(--warn);font-size:18px;"></i>
        <div style="font-size:12.5px;color:var(--warn);">
            <strong>{{ $waitingTotal }} vacancy(s) waiting, but there is no upcoming job fair to post them to.</strong>
            <div style="color:var(--n-600);margin-top:2px;">
                Create the event first — the fair decides which of these vacancies it takes,
                by industry, by PWD, and by local or overseas.
            </div>
        </div>
        <a href="{{ route('staff.jobfair.events') }}" class="btn btn-sm fw-semibold ms-auto"
           style="background:var(--g-700);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 16px;white-space:nowrap;">
            <i class="ph ph-plus-circle me-1"></i> Create a job fair event
        </a>
    </div>
</div>
@endif

@if($invite === 'accepted' && $events->isNotEmpty())
{{-- ── NO BUTTON ──

     PESO Job Fair staff, 2026-09-04: the desk used to press two buttons here,
     and neither of them could answer anything but yes. The fair already decides
     which vacancies it takes, and the only question left was when — which is
     always the same answer: shortly before the fair. So the date presses them.

     What is left is the statement of what will happen and when, so the desk can
     see it coming and knows nothing is waiting on it. --}}
<div class="card border-0 shadow-sm rounded-3 p-3 mb-3">
    <div class="d-flex align-items-center gap-2 mb-2">
        <i class="ph-fill ph-clock-countdown" style="color:var(--g-600);font-size:18px;"></i>
        <div class="fw-semibold" style="color:var(--g-700);font-size:12.5px;">
            Vacancies post themselves {{ $openDaysBefore }} days before each fair
        </div>
    </div>
    <div style="font-size:11.5px;color:var(--n-600);line-height:1.6;">
        On that day every waiting vacancy the fair takes is accepted onto it and shown to
        jobseekers at once — whether or not the employer target has been reached. A vacancy
        the fair does not take keeps waiting for one that does.
    </div>
    <div class="mt-2 d-flex flex-column gap-1">
        @foreach($openable as $option)
        <div class="d-flex align-items-center gap-2 flex-wrap p-2 rounded-3"
             style="background:{{ $option['inRange'] ? 'var(--g-50)' : 'var(--n-50)' }};
                    border:1px solid {{ $option['inRange'] ? 'var(--g-500)' : 'var(--n-200)' }};">
            <span class="fw-semibold" style="color:var(--g-700);font-size:12px;">{{ $option['title'] }}</span>
            <span style="color:var(--n-500);font-size:11px;">· {{ $option['date']->format('M d, Y') }}</span>
            <span class="ms-auto text-nowrap" style="font-size:11px;color:{{ $option['inRange'] ? 'var(--g-700)' : 'var(--n-600)' }};">
                @if($option['inRange'])
                    <i class="ph-fill ph-check-circle me-1"></i>Posted {{ $option['opensOn']->format('M d, Y') }}
                @else
                    <i class="ph ph-clock me-1"></i>Posts {{ $option['opensOn']->format('M d, Y') }}
                    · {{ $option['goingLive'] }} vacancy(s) queued
                @endif
            </span>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── ONE ROW PER EMPLOYER, NOT PER VACANCY ──

     The tab asks what the employer answered, so the employer is the row. The
     vacancies they would bring are listed inside it, each with how many
     jobseekers it would match.

     That match count is a suggestion and nothing more. It says who is
     registered today whose NSRP form lines up with the vacancy — no names, no
     promise that any of them will turn up or be hired. It is there so the
     employer weighing an invitation can see there are people to meet, and so
     the desk can see which invitation is worth chasing. --}}
<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr style="background:var(--g-600);">
                    <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">#</th>
                    <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Company</th>
                    <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Industry</th>
                    <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;min-width:300px;">Vacancies</th>
                    <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;text-align:center;">Potential Applicants</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invitations as $i => $row)
                @php
                    $company   = $row->employer;
                    $vacancies = $vacanciesFor[$row->employer_id . ':' . $row->job_fair_id] ?? collect();
                    $highly    = $vacancies->sum('highly_count');
                    $qualified = $vacancies->sum('qualified_count');
                @endphp
                <tr style="font-size:13px;">
                    <td style="padding:12px 16px;color:var(--n-500);">{{ $invitations->firstItem() + $i }}</td>
                    <td style="padding:12px 16px;font-weight:600;color:var(--g-700);">
                        {{ $company->company_name ?? 'None' }}
                        @if($company->is_overseas ?? false)
                            <span style="color:var(--info);font-size:10px;font-weight:600;">Overseas</span>
                        @endif
                        <div style="font-size:11px;font-weight:400;color:var(--n-500);">
                            {{ $company->employer->email ?? 'None' }}
                        </div>
                        {{-- Ang laray usa ka IMBITASYON, dili usa ka kompanya.
                             Ang employer nga gi-invite sa duha ka fair naay duha
                             ka laray, ug kung walay ngalan sa fair, managsama
                             gyud sila ug hitsura ug mabasa nga sayop. --}}
                        <div class="mt-1" style="font-size:11px;font-weight:600;color:var(--g-600);">
                            <i class="ph-fill ph-calendar-dots me-1"></i>{{ $row->jobFair->title ?? 'Fair not found' }}
                            <span style="font-weight:400;color:var(--n-500);">
                                · {{ $row->jobFair?->event_date?->format('M d, Y') ?? 'no date' }}
                            </span>
                        </div>
                    </td>
                    <td style="padding:12px 16px;color:var(--n-700);font-size:12px;">
                        {{ $company->industry_group ?? 'Not set' }}
                    </td>
                    <td style="padding:12px 16px;color:var(--n-700);">
                        {{-- ── Ang titulo kaniadto mao ra ang link.
                             ──
                             ── PESO Job Fair staff, 2026-09-04: "wajud ko kabalo
                             ── nga button to". Ang panid nga naay listahan sa
                             ── aplikante ug ang buton nga mo-text kanila naabot
                             ── ra pinaagi niini, mao nga ang tinago nga link
                             ── nagtago sa tibuok trabaho. Buton na siya karon. --}}
                        {{-- Usa ka linya kada bakante, dili tulo.
                             ──
                             PESO Job Fair staff, 2026-09-12: ang employer nga
                             nagdala ug walo ka bakante naghimo sa usa ka laray
                             nga mas taas pa sa screen, ug ang sunod nga employer
                             maabot ra pinaagi sa pag-scroll. Ang titulo, ang
                             slots ug ang buton nagpuyo na sa parehas nga linya,
                             ug ang linya dili mo-wrap. --}}
                        @forelse($vacancies as $vacancy)
                            <div class="d-flex align-items-center gap-2 text-nowrap"
                                 style="padding:2px 0;{{ !$loop->last ? 'border-bottom:1px dashed var(--n-100);' : '' }}">
                                <span class="fw-semibold text-truncate" style="color:var(--g-700);font-size:12px;max-width:190px;"
                                      title="{{ $vacancy->title }}">{{ $vacancy->title }}</span>
                                <span style="color:var(--n-500);font-size:11px;">
                                    · {{ $vacancy->slots }}@if($vacancy->acceptsPwd()) · PWD @endif
                                </span>
                                {{-- PESO Job Fair staff, 2026-09-14: applicants belong to an
                                     employer that is coming. A pending or declined
                                     invitation has no one to send them to, so the
                                     button is on the Accepted tab only. --}}
                                @if($invite === 'accepted')
                                <a href="{{ route('staff.jobfair.postings.applicants', $vacancy->job_qualifications_id) }}"
                                   class="btn btn-sm fw-semibold ms-auto"
                                   title="View applicants for {{ $vacancy->title }}"
                                   style="border:1px solid var(--g-500);color:var(--g-700);background:var(--g-50);
                                          border-radius:8px;font-size:11px;padding:2px 9px;line-height:1.5;">
                                    <i class="ph ph-users-three me-1"></i>Applicants
                                </a>
                                @endif
                            </div>
                        @empty
                            <span style="font-size:11.5px;color:var(--n-400);">No job fair vacancy posted</span>
                        @endforelse
                    </td>
                    <td style="padding:12px 16px;text-align:center;">
                        @if($vacancies->isEmpty())
                            <span style="font-size:11.5px;color:var(--n-400);">None</span>
                        @else
                            <span class="fw-semibold" style="font-size:12px;color:var(--g-700);">
                                {{ $highly }} highly · {{ $qualified }} qualified
                            </span>
                            <div style="font-size:10.5px;color:var(--n-500);">
                                suggestion only — not a guaranteed turnout
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center"
                        style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                        <i class="ph ph-envelope-simple me-1"
                           style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                        @php $forFair = $eventId ? ' for this job fair' : ''; @endphp
                        @if($invite === 'pending')
                            No employer is waiting to answer an invitation{{ $forFair }}.
                        @elseif($invite === 'accepted')
                            No employer has accepted an invitation{{ $forFair }} yet.
                        @else
                            No employer has declined an invitation{{ $forFair }}.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invitations->hasPages())
    <div class="d-flex justify-content-between align-items-center px-3 py-3" style="border-top:1px solid var(--n-50);">
        <div style="font-size:12px;color:var(--n-500);">
            Showing {{ $invitations->firstItem() }}–{{ $invitations->lastItem() }} of {{ $invitations->total() }} results
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0 gap-1">
                <li class="page-item {{ $invitations->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $invitations->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                </li>
                @include('partials.page-links', ['pager' => $invitations, 'activeStyle' => 'background:var(--g-600);border-color:transparent;color:#fff;', 'idleStyle' => 'border-color:var(--n-200);color:var(--g-700);'])
                <li class="page-item {{ !$invitations->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link rounded-2" style="border-color:var(--n-200);color:var(--g-700);" href="{{ $invitations->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                </li>
            </ul>
        </nav>
    </div>
    @endif
</div>

@push('scripts')
<script>
    let searchTimer;
    document.getElementById('searchInput')?.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            goToFilter('search', this.value.trim());
        }, 500);
    });

    // Ang sala nagpabilin sa URL, mao nga ang pager ug ang tab magdala niini.
    function goToFilter(key, value) {
        const url = new URL(window.location.href);
        if (value) {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    document.getElementById('industryFilter')?.addEventListener('change', function () {
        goToFilter('industry', this.value);
    });

    document.getElementById('eventFilter')?.addEventListener('change', function () {
        goToFilter('event_id', this.value);
    });

</script>
@endpush

@endsection
