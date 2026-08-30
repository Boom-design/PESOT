{{--
    Usa ka badge para sa kahimtang sa posting, gamiton sa employer, staff ug
    admin — aron parehas ang bokabularyo sa tanan nga portal (PESO interview
    2026-08-13: "kinahanglan adunay klarong status sama sa Active, Closed,
    Expired, ug Filled").

    Ang kahimtang gikan sa Job::getLifecycleStatusAttribute(), nga naggamit sa
    parehas nga tulo ka kondisyon sa scopeActive/scopeInactive. Walay bag-ong
    column, mao nga dili gyud magkalahi ang badge ug ang listahan.

    Plain nga text, walay kahon sa likod: ang kahon nagpasabot nga ma-click, ug
    kini dili. Ang kolor gipabilin — kana ang datos mismo, dili dekorasyon: ang
    berde ug ang pula maoy dali nga makita kung buhi pa ba ang posting o wala.

    Gamit: @include('partials.job-status-badge', ['job' => $job])
--}}
@php
    $lifecycle = $job->lifecycle_status;
    $badgeColors = [
        'pending'  => '#8a5c00',
        'rejected' => '#8a1c13',
        'active'   => '#0f7a5f',
        'waiting'  => '#2a4d9b',
        'filled'   => '#1c3f8a',
        'expired'  => '#8a1c13',
        'closed'   => '#4a5260',
    ];
    $badgeIcons = [
        'pending'  => 'ph-fill ph-hourglass-medium',
        'rejected' => 'ph-fill ph-x-circle',
        'active'   => 'ph-fill ph-check-circle',
        'waiting'  => 'ph-fill ph-calendar-dots',
        'filled'   => 'ph-fill ph-users-three',
        'expired'  => 'ph-fill ph-clock-countdown',
        'closed'   => 'ph-fill ph-lock-simple',
    ];
@endphp
<span class="d-inline-flex align-items-center gap-1"
      style="color:{{ $badgeColors[$lifecycle] }};font-weight:600;"
      @if($job->lifecycle_block_reason) title="{{ $job->lifecycle_block_reason }}" @endif>
    <i class="{{ $badgeIcons[$lifecycle] }}" style="font-size:13px;"></i>
    {{ \App\Models\Job::LIFECYCLE_LABELS[$lifecycle] }}
</span>
