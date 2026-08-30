@extends('company.layouts.app')

@section('page-title', 'Notifications')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:var(--g-700);">
        <i class="ph-fill ph-bell me-2" style="color:var(--g-600);"></i> All Notifications
    </h5>
    <p class="mb-0" style="font-size:13px;color:var(--n-500);">Complete history of your notifications</p>
</div>

@php
    $companyNotifTargetUrl = function ($notif) {
        // ── "New Job Applicant" kay dapat mo-adto sa Qualified Applicants page sa maong job, dili sa lista sa Active Job Postings ──
        if ($notif->type === 'new_applicant' && $notif->reference_id) {
            return route('company.jobs.qualified', $notif->reference_id);
        }
        return match($notif->reference_type) {
            'job'                     => route('company.jobseekers'),
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
           style="padding:14px 20px;border-bottom:1px solid var(--n-50);{{ !$notif->is_read ? 'background:var(--n-50);' : '' }}"
           onclick="markRead({{ $notif->announcements_id }}, {{ $companyNotifTargetUrl($notif) ? 'true' : 'false' }})">
            <div style="font-size:13px;font-weight:{{ !$notif->is_read ? '700' : '600' }};color:var(--g-700);">
                <i class="ph-fill ph-bell me-1" style="color:var(--g-600);"></i>
                {{ $notif->title }}
            </div>
            <div style="font-size:12px;color:var(--n-700);margin-top:2px;">{{ $notif->message }}</div>
            <div style="font-size:11px;color:var(--n-400);margin-top:4px;">{{ $notif->created_at->diffForHumans() }}</div>
        </a>
    @empty
        <div class="empty-state">
            <div class="empty-icon"><i class="ph ph-bell-slash"></i></div>
            <h6>No notifications yet</h6>
            <p>Your notifications will appear here.</p>
        </div>
    @endforelse
</div>

@endsection