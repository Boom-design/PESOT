{{-- The points one qualification is worth in the match score, shown right under
     that qualification so the employer sets both in one place.
     Expects: $key (a Job::MATCH_POINTS key), $value. --}}
@php $meta = \App\Models\Job::MATCH_POINTS[$key]; @endphp
<div class="d-flex align-items-center flex-wrap gap-2 mt-1">
    <label for="points_{{ $key }}" style="font-size:11px;font-weight:600;color:var(--n-600);margin:0;">Points</label>
    <input type="number" id="points_{{ $key }}" name="match_points[{{ $key }}]" class="form-control form-control-sm match-points-input"
        min="0" max="100" step="1" value="{{ $value }}"
        style="width:76px;border-color:var(--n-200);font-size:12px;border-radius:8px;">
    @if(!empty($meta['hint']))
    <span style="font-size:11px;color:var(--n-500);">{{ $meta['hint'] }}</span>
    @endif
</div>
