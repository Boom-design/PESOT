{{--
    Bar chart nga puro CSS — walay Chart.js, walay CDN, walay bag-ong file nga
    i-download. PESO interview 2026-08-13: "Mas maayo kung adunay graph o visual
    representation aron dali makita ang statistics."
    Ang report kinahanglan pud ma-print, ug ang CSS bar mo-gawas sa papel nga
    hapsay — ang canvas dili kanunay.

    Gamit:
        @include('partials.bar-chart', [
            'title' => 'Applicants per month',
            'rows'  => ['Jan 2026' => 12, 'Feb 2026' => 30],
        ])
--}}
@php
    $rows = collect($rows ?? []);
    $max  = max(1, (int) $rows->max());
@endphp

<div class="peso-card p-3 mb-3">
    <div class="fw-bold mb-3" style="font-size:13px;color:var(--g-700);">
        <i class="ph ph-chart-bar me-1"></i>{{ $title ?? 'Chart' }}
    </div>

    {{-- A month with nothing in it is still an answer. The chart used to hide
         itself whenever every bar was zero, so a quiet year looked like a
         broken page; now the empty months are drawn, and only a caller that
         passes no months at all gets the note. --}}
    @if($rows->isEmpty())
        <div class="text-center py-4" style="color:var(--n-400);font-size:12.5px;">
            No data in this period.
        </div>
    @else
        {{-- justify-content-start + flex:0 0 auto: kung usa ra ka bulan ang naay
             datos, ang usa ka bar dili mo-inat sa tibuok gilapdon sa card ug
             mahimong dako nga berde nga kahon. Fixed ang gilapdon sa bar; ang
             wala nagamit nga luna magpabilin nga blangko. --}}
        <div class="d-flex align-items-end justify-content-start gap-3"
             style="height:180px;overflow-x:auto;padding-bottom:4px;">
            @foreach($rows as $label => $value)
                <div class="d-flex flex-column align-items-center justify-content-end"
                     style="height:100%;flex:0 0 56px;">
                    <div class="fw-bold" style="font-size:11px;color:var(--g-700);margin-bottom:3px;">{{ $value }}</div>
                    {{-- 8px ang pinakaubos aron ang bar nga 0 makita gihapon isip bar. --}}
                    <div title="{{ $label }}: {{ $value }}"
                         style="width:34px;border-radius:6px 6px 0 0;background:var(--g-600);
                                height:{{ max(8, (int) round($value / $max * 140)) }}px;"></div>
                    <div style="font-size:10px;color:var(--n-500);margin-top:5px;text-align:center;line-height:1.3;">
                        {{ $label }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
