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
      lra          In-house Schedule, In-house Job Vacancy, Participants
      sra          + Company Interview, Job Fair
      job_vacancy  In-house, Company Interview, Job Fair (no Participants)
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
    $onInterview   = $tabRoute === 'staff.jobs' && !$onInhouseJobs;
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
        <i class="ph-fill ph-calendar-check me-1"></i> In-house Schedule
    </a>
    @endif

    @if($tabRole !== 'lra')
    <a href="{{ route('staff.jobs', ['type' => 'company_interview']) }}" class="btn btn-sm fw-semibold"
       style="{{ $onInterview ? $tabOn : $tabOff }}{{ $tabBase }}">
        <i class="ph-fill ph-briefcase me-1"></i> Company Interview
    </a>
    @endif

    <a href="{{ route('staff.jobs', ['type' => 'inhouse']) }}" class="btn btn-sm fw-semibold"
       style="{{ $onInhouseJobs ? $tabOn : $tabOff }}{{ $tabBase }}">
        <i class="ph-fill ph-{{ $tabRole === 'job_vacancy' ? 'calendar-check' : 'briefcase' }} me-1"></i>
        {{ $tabRole === 'job_vacancy' ? 'In-house' : 'In-house Job Vacancy' }}
    </a>

    @if($tabRole !== 'lra')
    {{-- Ang LRA walay Job Fair nga tab: dili iya ang event. Apan ang jobseeker
         nga niapil didto iya gihapon nga tubagan, mao nga naa siya sa
         Participants sa ubos. --}}
    <a href="{{ route('staff.inhouse.jobfair') }}" class="btn btn-sm fw-semibold"
       style="{{ $tabRoute === 'staff.inhouse.jobfair' ? $tabOn : $tabOff }}{{ $tabBase }}">
        <i class="ph-fill ph-calendar-dots me-1"></i> Job Fair
    </a>
    @endif

    @if(in_array($tabRole, ['lra', 'sra'], true))
    <a href="{{ route('staff.participants') }}" class="btn btn-sm fw-semibold"
       style="{{ $tabRoute === 'staff.participants' ? $tabOn : $tabOff }}{{ $tabBase }}">
        <i class="ph-fill ph-users-three me-1"></i> Participants
    </a>
    @endif

    @isset($tabsRight)
    <div class="ms-auto flex-shrink-0">{!! $tabsRight !!}</div>
    @endisset

</div>
