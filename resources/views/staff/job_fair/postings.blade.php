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
            would bring. Pick a fair and post everything it takes — a posted vacancy goes live
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
     wala silay nadawat nga pagbalibad ug walay nahibaw-an ang employer. --}}
@if($invite === 'pending' && $waitingTotal > 0 && $events->isEmpty())
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

@if($invite === 'pending' && $events->isNotEmpty() && $waitingTotal > 0)
<form method="POST" action="{{ route('staff.jobfair.postings.postFitting') }}" id="postFittingForm" class="mb-3">
    @csrf
    <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <label class="fw-semibold mb-0" style="color:var(--g-700);font-size:12.5px;white-space:nowrap;">
                <i class="ph ph-flag-banner me-1" style="color:var(--g-600);"></i>Post the waiting vacancies to
            </label>
            <select name="job_fair_id" id="fitEventSelect" class="form-select form-select-sm"
                    style="max-width:340px;border-color:var(--n-200);font-size:12.5px;border-radius:8px;" required>
                @foreach($events as $event)
                    <option value="{{ $event->job_fair_events_id }}" data-fit="{{ $fitCounts[$event->job_fair_events_id] ?? 0 }}">
                        {{ $event->title }} — {{ $event->event_date->format('M d, Y') }}{{ $event->pwd_only ? ' · PWD only' : '' }}
                    </option>
                @endforeach
            </select>

            <span id="fitHint" style="font-size:11.5px;color:var(--n-500);"></span>

            <button type="submit" id="postFittingButton" class="btn btn-sm fw-semibold ms-auto"
                style="background:var(--g-700);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 16px;">
                <i class="ph ph-check-circle me-1"></i> Post <span id="fitCount">0</span> vacancy(s)
            </button>
        </div>
    </div>
</form>
@endif

{{-- ── SHOW THE VACANCIES TO JOBSEEKERS ──

     PESO Job Fair staff, 2026-09-02: the desk decides the moment the list goes
     public, normally {{ $openDaysBefore }} days before the fair, once the
     employers that are coming have answered. Until then the vacancies sit
     closed — a vacancy announced a month before the day it can be applied for
     is buried under everything posted since.

     It sits on Accepted Invitation because that is the tab that answers the
     question the desk asks first: who is actually coming. --}}
@if($invite === 'accepted' && $openable->isNotEmpty())
<form method="POST" action="{{ route('staff.jobfair.postings.openAll') }}" id="openAllForm" class="mb-3">
    @csrf
    <div class="card border-0 shadow-sm rounded-3 p-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <label class="fw-semibold mb-0" style="color:var(--g-700);font-size:12.5px;white-space:nowrap;">
                <i class="ph ph-megaphone me-1" style="color:var(--g-600);"></i>Show the vacancies of
            </label>
            <select name="job_fair_id" id="openAllSelect" class="form-select form-select-sm"
                    style="max-width:340px;border-color:var(--n-200);font-size:12.5px;border-radius:8px;" required>
                @foreach($openable as $option)
                    <option value="{{ $option['id'] }}"
                            data-waiting="{{ $option['waiting'] }}"
                            data-inrange="{{ $option['inRange'] ? '1' : '0' }}"
                            data-title="{{ $option['title'] }}">
                        {{ $option['title'] }} — {{ $option['date']->format('M d, Y') }}
                    </option>
                @endforeach
            </select>

            <span id="openAllHint" style="font-size:11.5px;color:var(--n-500);"></span>

            <button type="button" id="openAllButton" class="btn btn-sm fw-semibold ms-auto"
                style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 16px;">
                <i class="ph-fill ph-megaphone me-1"></i> Post All Job Vacancies
                (<span id="openAllCount">0</span>)
            </button>
        </div>
        <div id="openAllEarly" class="mt-2" style="display:none;font-size:11.5px;color:var(--warn);">
            <i class="ph-fill ph-warning-circle me-1"></i>
            This fair is more than {{ $openDaysBefore }} days away. Posting now means jobseekers
            see these vacancies well before the day they can act on them.
        </div>
    </div>
</form>
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
                    <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Job Fair</th>
                    <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Industry</th>
                    <th style="color:var(--g-700);font-size:12px;border:none;padding:12px 16px;">Vacancies</th>
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
                    </td>
                    <td style="padding:12px 16px;color:var(--n-700);">
                        {{ $row->jobFair->title ?? 'None' }}
                        @if($row->jobFair?->event_date)
                            <div style="font-size:11px;color:var(--n-500);">
                                {{ $row->jobFair->event_date->format('M d, Y') }}
                            </div>
                        @endif
                    </td>
                    <td style="padding:12px 16px;color:var(--n-700);font-size:12px;">
                        {{ $company->industry_group ?? 'Not set' }}
                    </td>
                    <td style="padding:12px 16px;color:var(--n-700);">
                        @forelse($vacancies as $vacancy)
                            <div style="font-size:12px;">
                                <a href="{{ route('staff.jobfair.postings.applicants', $vacancy->job_qualifications_id) }}"
                                   class="fw-semibold text-decoration-none" style="color:var(--g-700);">
                                    {{ $vacancy->title }}
                                </a>
                                <span style="color:var(--n-500);font-size:11px;">
                                    · {{ $vacancy->slots }} slot(s)@if($vacancy->acceptsPwd()) · accepts PWD @endif
                                </span>
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
                    <td colspan="6" class="text-center"
                        style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                        <i class="ph ph-envelope-simple me-1"
                           style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                        @if($invite === 'pending')
                            No employer is waiting to answer an invitation.
                        @elseif($invite === 'accepted')
                            No employer has accepted an invitation yet.
                        @else
                            No employer has declined an invitation.
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
                @foreach($invitations->getUrlRange(1, $invitations->lastPage()) as $page => $url)
                <li class="page-item {{ $page == $invitations->currentPage() ? 'active' : '' }}">
                    <a class="page-link rounded-2"
                       style="{{ $page == $invitations->currentPage() ? 'background:var(--g-600);border-color:transparent;color:#fff;' : 'border-color:var(--n-200);color:var(--g-700);' }}"
                       href="{{ $url }}">{{ $page }}</a>
                </li>
                @endforeach
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

    // ── POST ALL JOB VACANCIES ──
    // The count is the server's, so the button and the actual open cannot
    // disagree. A fair with nothing waiting kills the button — there is
    // nothing to show. The press asks first: the employers are told their
    // posting is live and the matching jobseekers are notified, and neither
    // message can be taken back.
    (function () {
        const select = document.getElementById('openAllSelect');
        if (!select) return;

        const button = document.getElementById('openAllButton');
        const count  = document.getElementById('openAllCount');
        const hint   = document.getElementById('openAllHint');
        const early  = document.getElementById('openAllEarly');
        const form   = document.getElementById('openAllForm');

        function refresh() {
            const option  = select.options[select.selectedIndex];
            const waiting = parseInt(option?.dataset.waiting || '0', 10);
            const inRange = option?.dataset.inrange === '1';

            count.textContent = waiting;
            button.disabled = waiting === 0;
            button.style.opacity = waiting === 0 ? '0.55' : '';
            button.style.cursor  = waiting === 0 ? 'not-allowed' : '';

            hint.textContent = waiting === 0
                ? 'Every vacancy on this fair is already visible to jobseekers.'
                : waiting + ' vacancy(s) on this fair are still hidden from jobseekers.';

            early.style.display = waiting > 0 && !inRange ? 'block' : 'none';
        }

        select.addEventListener('change', refresh);
        refresh();

        button.addEventListener('click', function () {
            const option  = select.options[select.selectedIndex];
            const waiting = parseInt(option?.dataset.waiting || '0', 10);

            Swal.fire({
                title: 'Post these vacancies?',
                html: '<div style="font-size:14px;">This makes <strong>' + waiting + '</strong> vacancy(s) on '
                      + '<strong>' + (option?.dataset.title || 'this fair') + '</strong> visible to every jobseeker. '
                      + 'The employers are told their posting is live and the matching jobseekers are notified. '
                      + 'This cannot be taken back.</div>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Post them now',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#2e7d32',
            }).then((result) => {
                if (result.isConfirmed) {
                    button.disabled = true;
                    form.submit();
                }
            });
        });
    })();

    // ── PILA ANG MOSULOD SA PINILING FAIR ──
    // Ang numero gikwenta sa server para sa matag fair, mao nga ang gipakita
    // sa buton ug ang tinuod nga i-post parehas gyud. Ang fair nga walay
    // sakop nga bakante mopatay sa buton — walay ipadala.
    (function () {
        const select = document.getElementById('fitEventSelect');
        if (!select) return;

        const button = document.getElementById('postFittingButton');
        const count  = document.getElementById('fitCount');
        const hint   = document.getElementById('fitHint');

        function refresh() {
            const option = select.options[select.selectedIndex];
            const fits   = parseInt(option?.dataset.fit || '0', 10);

            if (count)  count.textContent = fits;
            if (button) button.disabled = fits === 0;
            if (hint) {
                hint.textContent = fits === 0
                    ? 'No waiting vacancy belongs to this fair.'
                    : fits + ' of the waiting vacancies belong to this fair.';
            }
        }

        select.addEventListener('change', refresh);
        refresh();
    })();
</script>
@endpush

@endsection
