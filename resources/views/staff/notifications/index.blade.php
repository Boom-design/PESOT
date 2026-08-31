@extends('staff.layouts.app')

@section('page-title', 'All Notifications')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">
            <i class="ph-fill ph-bell me-2" style="color:var(--g-600);"></i>All Notifications
        </h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">
            Complete history of your notifications
        </p>
    </div>
</div>

@if($notifications->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="ph ph-bell-slash" style="font-size:48px;color:var(--n-300);"></i>
        <div class="mt-3 fw-semibold" style="color:var(--g-700);">No notifications yet</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        @foreach($notifications as $notif)
        {{-- Ang bell dropdown ug kining page parehas ug tubag karon: tan-awa
             ang Announcement::staffLinkUrl(). Kaniadto duha ka lista kini ug
             nagkalahi — ang parehas nga notice moabli ug lain nga screen
             depende kung asa siya gi-click. --}}
        <a href="{{ $notif->staffLinkUrl() }}"
           class="d-block text-decoration-none px-4 py-3"
           style="border-bottom:1px solid var(--n-50); background:{{ !$notif->is_read ? 'var(--g-50)' : '#fff' }};">
            <div style="font-size:13px;font-weight:{{ !$notif->is_read ? '700' : '500' }};color:var(--g-700);">
                <i class="ph-fill ph-bell me-1" style="color:var(--g-600);"></i>
                {{ $notif->title }}
            </div>
            <div style="font-size:12.5px;color:var(--n-700);margin-top:2px;">{{ $notif->message }}</div>
            <div style="font-size:11px;color:var(--n-400);margin-top:4px;">{{ $notif->created_at->diffForHumans() }}</div>
        </a>
        @endforeach
    </div>
@endif

@endsection