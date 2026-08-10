@extends('jobseeker.layouts.app')

@section('page-title', 'Notifications')

@section('content')

<div class="mb-4 fade-in">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-bell-fill me-2" style="color:#4dd9c0;"></i> All Notifications
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">View all your notifications</p>
</div>

@php
    $notifTargetUrl = function ($notif) {
        return match($notif->reference_type) {
            'job'                     => route('jobseeker.jobs.show', $notif->reference_id),
            'inhouse_schedule'        => route('jobseeker.schedules'),
            'job_fair'                => route('jobseeker.schedules'),
            'job_fair_registration'   => route('jobseeker.schedules'),
            'jobseeker_registration'  => route('jobseeker.nsrp'),
            default                   => null,
        };
    };
@endphp

<div class="peso-card fade-in-1">
    @if($notifications->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-bell-slash"></i></div>
            <h6>No notifications yet</h6>
            <p>You'll see updates about your applications and job fair events here.</p>
        </div>
    @else
        <div class="list-group list-group-flush">
            @foreach($notifications as $notif)
            <a href="{{ $notifTargetUrl($notif) ?? '#' }}"
               class="list-group-item list-group-item-action py-3 px-4 {{ !$notif->is_read ? 'bg-light' : '' }}"
               style="border-color:#f0f9f6;text-decoration:none;"
               onclick="fetch('/jobseeker/notifications/{{ $notif->announcements_id }}/read', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}})">
                <div style="font-size:13px;font-weight:{{ !$notif->is_read ? '700' : '600' }};color:#2d7a5f;">
                    {{ $notif->title }}
                </div>
                <div style="font-size:12px;color:#666;margin-top:2px;">{{ $notif->message }}</div>
                <div style="font-size:11px;color:#aaa;margin-top:4px;">{{ $notif->created_at->diffForHumans() }}</div>
            </a>
            @endforeach
        </div>
    @endif
</div>

@endsection