@extends('admin.layouts.app')

@section('content')

<div class="mb-4">
    <h4 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-clock-counter-clockwise me-2" style="color:var(--g-600);"></i>Activity Logs
    </h4>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">
        What each administrator did, so the others can see it.
    </p>
</div>

{{-- WHO, and WHAT KIND. Three administrators and a page of everything they
     have all done is a page nobody reads; the usual question is "what did the
     other one change" or "who touched the calendar".

     The two pickers sit in a card of their own, like every other filter row
     in the admin, instead of floating bare above the table. --}}
<div class="card border-0 shadow-sm rounded-3 mb-3">
    <div class="card-body py-2 px-3 d-flex align-items-center gap-2 flex-wrap">
        <i class="ph ph-funnel-simple" style="color:var(--g-600);font-size:15px;"></i>

        <select id="activityAdmin" class="form-select form-select-sm"
                style="max-width:230px;border-color:var(--n-200);font-size:12.5px;border-radius:8px;">
            <option value="">All administrators</option>
            @foreach($admins as $one)
                <option value="{{ $one->users_id }}" {{ (string) $actorId === (string) $one->users_id ? 'selected' : '' }}>
                    {{ $one->name }}{{ $one->users_id === auth()->id() ? ' (you)' : '' }}
                </option>
            @endforeach
        </select>

        <select id="activityAction" class="form-select form-select-sm"
                style="max-width:230px;border-color:var(--n-200);font-size:12.5px;border-radius:8px;">
            <option value="">Everything</option>
            @foreach($actions as $key => $label)
                <option value="{{ $key }}" {{ $action === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>

        <span class="ms-auto fw-semibold" style="font-size:12px;color:var(--g-700);">
            {{ number_format($logs->total()) }} {{ Str::plural('entry', $logs->total()) }}
        </span>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">#</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">Administrator</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;">What they did</th>
                    <th style="background:var(--g-600);color:#fff;font-size:12px;border:none;padding:12px 16px;white-space:nowrap;">When</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $i => $log)
                @php [$icon, $colour] = $log->look(); @endphp
                <tr style="font-size:13px;">
                    <td style="padding:12px 16px;color:var(--n-500);">{{ $logs->firstItem() + $i }}</td>
                    <td style="padding:12px 16px;">
                        {{-- The name as it was at the time, not as it is now.
                             An administrator who renames themselves must not
                             rewrite what the log says they did last month. --}}
                        <span class="fw-semibold" style="color:var(--g-700);">{{ $log->actor_name }}</span>
                        @if($log->actor_user_id === auth()->id())
                            <span style="font-size:10.5px;color:var(--n-500);">(you)</span>
                        @endif
                    </td>
                    {{-- The icon sits in a tinted disc rather than loose
                         against the text. A row of bare icons in the middle of
                         a sentence reads as decoration; a disc reads as the
                         kind of the entry, which is what it is. --}}
                    <td style="padding:12px 16px;color:var(--n-700);">
                        <div class="d-flex align-items-start gap-2">
                            <span class="d-flex align-items-center justify-content-center flex-shrink-0"
                                  style="width:26px;height:26px;border-radius:8px;
                                         background:{{ $colour }}1A;color:{{ $colour }};">
                                <i class="ph-fill {{ $icon }}" style="font-size:14px;"></i>
                            </span>
                            <span style="line-height:1.5;">{{ $log->summary }}</span>
                        </div>
                    </td>
                    <td style="padding:12px 16px;color:var(--n-500);white-space:nowrap;">
                        {{ $log->created_at->format('M d, Y') }}
                        <div style="font-size:11px;">{{ $log->created_at->format('g:i A') }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                        <i class="ph ph-clock-counter-clockwise me-1"
                           style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                        Nothing recorded yet. Actions taken by an administrator appear here.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- The house pager.

     $logs->links() renders Laravel's own Tailwind markup, and this project is
     Bootstrap — so Previous and Next came out as bare blue text in a row,
     the one thing on the page wearing nobody's design. partials/pager is the
     pill every other paginated list in the system already uses. --}}
@include('partials.pager', ['pager' => $logs, 'pagerLabel' => 'Page'])

@push('scripts')
<script>
    function setActivityFilter(name, value) {
        const url = new URL(window.location.href);
        if (value) { url.searchParams.set(name, value); } else { url.searchParams.delete(name); }
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
    }

    document.getElementById('activityAdmin')?.addEventListener('change', function () {
        setActivityFilter('actor', this.value);
    });
    document.getElementById('activityAction')?.addEventListener('change', function () {
        setActivityFilter('action', this.value);
    });
</script>
@endpush

@endsection
