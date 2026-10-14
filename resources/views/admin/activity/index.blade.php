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
     other one change" or "who touched the calendar". --}}
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
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

    <span style="font-size:11.5px;color:var(--n-500);">
        {{ $logs->total() }} {{ Str::plural('entry', $logs->total()) }}
    </span>
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
                    <td style="padding:12px 16px;color:var(--n-700);">
                        <i class="ph-fill {{ $icon }} me-1" style="color:{{ $colour }};"></i>{{ $log->summary }}
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

@if($logs->hasPages())
<div class="mt-3">{{ $logs->links() }}</div>
@endif

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
