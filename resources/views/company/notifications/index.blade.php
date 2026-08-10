@extends('company.layouts.app')

@section('page-title', 'Notifications')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-bell-fill me-2" style="color:#4dd9c0;"></i> All Notifications
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">Complete history of your notifications</p>
</div>

@php
    $companyNotifTargetUrl = function ($notif) {
        // ── "New Job Applicant" kay dapat mo-adto sa Qualified Applicants page sa maong job, dili sa Job Vacancy Request listing ──
        if ($notif->type === 'new_applicant' && $notif->reference_id) {
            return route('company.jobs.qualified', $notif->reference_id);
        }
        return match($notif->reference_type) {
            'job'                     => route('company.jobs'),
            'employer_requirement'    => route('company.requirements'),
            'job_fair'                => route('company.jobfair'),
            default                   => null,
        };
    };
@endphp

<div class="peso-card">
    @forelse($notifications as $notif)
        <a href="{{ $companyNotifTargetUrl($notif) ?? '#' }}"
           class="d-block text-decoration-none"
           style="padding:14px 20px;border-bottom:1px solid #f0f9f6;{{ !$notif->is_read ? 'background:#f0f9f6;' : '' }}"
           onclick="markRead({{ $notif->announcements_id }}, {{ $companyNotifTargetUrl($notif) ? 'true' : 'false' }})">
            <div style="font-size:13px;font-weight:{{ !$notif->is_read ? '700' : '600' }};color:#2d7a5f;">
                <i class="bi bi-bell-fill me-1" style="color:#4dd9c0;"></i>
                {{ $notif->title }}
            </div>
            <div style="font-size:12px;color:#555;margin-top:2px;">{{ $notif->message }}</div>
            <div style="font-size:11px;color:#aaa;margin-top:4px;">{{ $notif->created_at->diffForHumans() }}</div>
        </a>
    @empty
        <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-bell-slash"></i></div>
            <h6>No notifications yet</h6>
            <p>Your notifications will appear here.</p>
        </div>
    @endforelse
</div>

@endsection