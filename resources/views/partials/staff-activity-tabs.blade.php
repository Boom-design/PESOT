{{--
    The one tab row for Manage Job Activities.

    Every page under it used to carry its own copy, and they drifted: the Job
    Fair page lost Participants, the Job Vacancy page kept a tab another role
    could not open, and a tab could disappear simply by moving between views.
    One row, written once, so a page cannot answer the question differently.

    Which tab is lit is worked out from the current route and the `type` in the
    query string — the caller passes nothing, so a new page joins the row by
    including this and nothing else.

    Who sees what:
      lra          In-house Schedule, In-house Job Vacancy, Job Fair
      sra          Pending In-house Schedule, Pending Company Interview,
                   In-house Job Vacancy, Company Interview, Job Fair
      job_vacancy  the same, minus Pending In-house Schedule (LRA holds that
                   calendar), and named In-house rather than In-house Job Vacancy
--}}
@php
    $tabRole  = optional(Auth::user()->staff)->staff_role ?? 'staff';
    $tabRoute = \Illuminate\Support\Facades\Route::currentRouteName();
    $tabType  = request('type');

    $tabOn  = 'background:var(--g-600);color:#fff;border:none;';
    $tabOff = 'border:1px solid var(--n-200);color:var(--g-700);background:#fff;';
    $tabBase = 'border-radius:8px;font-size:12px;padding:5px 16px;white-space:nowrap;flex-shrink:0;';

    // Ang Job Vacancy walay In-house Schedule nga page; ang In-house niya kay
    // ang posting mismo. Ang SRA nagsugod sa In-house kung walay type. Ang LRA
    // usa ra ka jobs nga tab, mao nga siya ang dagkotan bisan unsa pa ang type.
    $onInhouseJobs = $tabRoute === 'staff.jobs' && (
        $tabRole === 'lra'
        || $tabType === 'inhouse'
        || ($tabRole === 'sra' && $tabType === null)
    );

    // Ang company interview nga wala pa nahitabo. Kaugalingon niyang tab kay
    // lahi ang pangutana: ang Company Interview nga tab kay rekord sa tanan nga
    // gi-solicit, kini kay ang umaabot pa nga kinahanglan pang bantayan.
    $onPendingInterview = $tabRoute === 'staff.jobs' && $tabType === 'company_interview_pending';

    $onInterview = $tabRoute === 'staff.jobs' && !$onInhouseJobs && !$onPendingInterview;
@endphp

{{-- $tabsRight — anything the page wants sitting at the end of this row.
     The Job Vacancy list puts its Total Jobs count there. It used to be a
     full-width stat card of its own above the table, alongside Open and Closed
     cards for a filter that no longer exists; one number does not need a card,
     and next to the tabs it reads as a count of what the lit tab is showing. --}}
<div class="d-flex align-items-center gap-2 mb-4" style="overflow-x:auto;flex-wrap:nowrap;-webkit-overflow-scrolling:touch;padding-bottom:4px;">

    @if($tabRole !== 'job_vacancy')
    <a href="{{ route('staff.inhouse') }}" class="btn btn-sm fw-semibold"
       style="{{ $tabRoute === 'staff.inhouse' ? $tabOn : $tabOff }}{{ $tabBase }}">
        {{-- Named for what it holds. The tab is where an in-house request is
             accepted or rejected; once accepted it moves to In-house Job
             Vacancy, so nothing decided stays behind. --}}
        <i class="ph-fill ph-calendar-check me-1"></i> Pending In-house Schedule
    </a>
    @endif

    @if($tabRole !== 'lra')
    <a href="{{ route('staff.jobs', ['type' => 'company_interview_pending']) }}" class="btn btn-sm fw-semibold"
       style="{{ $onPendingInterview ? $tabOn : $tabOff }}{{ $tabBase }}">
        {{-- Ang interview nga wala pa nahitabo — karon o umaabot pa ang petsa.
             Walay approval nga agian ang company interview, buhi na siya
             pag-post, mao nga ang "pending" dinhi kay ang wala pa nahitabo. --}}
        <i class="ph-fill ph-hourglass-medium me-1"></i> Pending Company Interview
    </a>
    @endif

    <a href="{{ route('staff.jobs', ['type' => 'inhouse']) }}" class="btn btn-sm fw-semibold"
       style="{{ $onInhouseJobs ? $tabOn : $tabOff }}{{ $tabBase }}">
        <i class="ph-fill ph-{{ $tabRole === 'job_vacancy' ? 'calendar-check' : 'briefcase' }} me-1"></i>
        {{ $tabRole === 'job_vacancy' ? 'In-house' : 'In-house Job Vacancy' }}
    </a>

    @if($tabRole !== 'lra')
    <a href="{{ route('staff.jobs', ['type' => 'company_interview']) }}" class="btn btn-sm fw-semibold"
       style="{{ $onInterview ? $tabOn : $tabOff }}{{ $tabBase }}">
        <i class="ph-fill ph-briefcase me-1"></i> Company Interview
    </a>
    @endif

    @if($tabRole !== 'lra')
    {{-- Dili iya sa LRA ang event mismo, mao nga wala siya niining tab. Ang
         jobseeker nga niapil sa usa ka fair iya gihapon nga tubagan, ug para
         niana naa siyay kaugalingong Job Fair nga tab sa ubos. --}}
    <a href="{{ route('staff.inhouse.jobfair') }}" class="btn btn-sm fw-semibold"
       style="{{ $tabRoute === 'staff.inhouse.jobfair' ? $tabOn : $tabOff }}{{ $tabBase }}">
        <i class="ph-fill ph-calendar-dots me-1"></i> Job Fair
    </a>
    @endif

    {{-- Ang LRA ra. Ang page nagsulti kinsa nga employer ang niapil sa usa ka
         fair, mao nga para niya ang ngalan sa fair mismo ang husto — Job Fair
         ang iyang tab, ug kini ang bugtong agianan niya ngadto sa fair.

         Ang SRA wala nay Participants nga tab. Naa na siyay Job Fair nga tab sa
         ibabaw nga nagbuhat sa iyang trabaho didto, ug ang ikaduha nga tab
         padulong sa parehas nga fair nagbahin sa pangutana sa duha ka lugar. --}}
    @if($tabRole === 'lra')
    <a href="{{ route('staff.participants') }}" class="btn btn-sm fw-semibold"
       style="{{ $tabRoute === 'staff.participants' ? $tabOn : $tabOff }}{{ $tabBase }}">
        <i class="ph-fill ph-calendar-dots me-1"></i> Job Fair
    </a>
    @endif

    @isset($tabsRight)
    <div class="ms-auto flex-shrink-0">{!! $tabsRight !!}</div>
    @endisset

</div>
