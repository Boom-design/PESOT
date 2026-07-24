@extends('admin.layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
            <i class="bi bi-bell-fill me-2" style="color:#4dd9c0;"></i>All Notifications
        </h5>
        <p class="mb-0" style="font-size:13px;color:#888;">
            Complete history of system notifications
        </p>
    </div>
    @if($notifications->count() > 0)
    <form action="{{ route('admin.notifications.clearAll') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm fw-semibold"
            style="border:1.5px solid #f5b5b5;color:#e05252;background:#fff;border-radius:8px;font-size:12px;padding:8px 16px;">
            <i class="bi bi-trash me-1"></i> Clear All
        </button>
    </form>
    @endif
</div>

@if($notifications->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-bell-slash" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No notifications yet</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        @foreach($notifications as $notif)
        <a href="{{ $notif->reference_type === 'registration' ? route('admin.registration.view', $notif->reference_id) : route('admin.dashboard') }}"
           class="d-block text-decoration-none px-4 py-3 {{ !$notif->is_read ? '' : '' }}"
           style="border-bottom:1px solid #f0f9f6; background:{{ !$notif->is_read ? '#e8f8f3' : '#fff' }};"
           onclick="event.stopPropagation();">
            <div style="font-size:13px;font-weight:{{ !$notif->is_read ? '700' : '500' }};color:#2d7a5f;">
                <i class="bi bi-person-fill-check me-1" style="color:#4dd9c0;"></i>
                {{ $notif->title }}
            </div>
            <div style="font-size:12.5px;color:#555;margin-top:2px;">{{ $notif->message }}</div>
            <div style="font-size:11px;color:#aaa;margin-top:4px;">{{ $notif->created_at->diffForHumans() }}</div>
        </a>
        @endforeach
    </div>
@endif

@endsection