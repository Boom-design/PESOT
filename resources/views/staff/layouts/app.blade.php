<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PESO — Staff Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        .flatpickr-day.fp-date-has-schedule {
            background: #e8f8f3 !important;
            color: #2d7a5f !important;
            font-weight: 700;
            border: 1.5px solid #4dd9c0 !important;
        }
    </style>
    <style>
        body { background-color: #f4f6f9; margin: 0; }

        .sidebar {
            width: 220px;
            min-width: 220px;
            min-height: 100vh;
            background: linear-gradient(180deg, #1a5c45 0%, #0d1f18 100%);
            padding-top: 0;
            display: flex;
            flex-direction: column;
        }
        .sidebar .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 12px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.4);
            margin-bottom: 8px;
            text-align: center;
        }
        .sidebar .brand img {
            width: 56px; height: 56px;
            object-fit: contain;
            border-radius: 50%;
            margin-bottom: 8px;
        }
        .sidebar .brand-text .title {
            font-size: 8.5px;
            font-weight: 700;
            color: #fff;
            line-height: 1.4;
            white-space: nowrap;
        }
        .sidebar .brand-text .subtitle {
            font-size: 10px;
            font-weight: 600;
            color: #fff;
            line-height: 1.3;
            margin-top: 2px;
        }
        .staff-role-badge {
            display: inline-block;
            margin-top: 20px;
            padding: 3px 12px;
            border-radius: 20px;
            background: rgba(255,255,255,0.3);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .sidebar a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 8px;
            margin: 2px 8px;
            transition: all 0.2s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: linear-gradient(90deg, #90d870, #4dd9c0);
            color: #fff;
            box-shadow: 0 2px 8px rgba(77,217,192,0.3);
        }

        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 0; left: 0;
                height: 100vh;
                z-index: 1050;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.sidebar-open { transform: translateX(0); }
        }
        .sidebar-overlay { display: none; }
        @media (max-width: 767.98px) {
            .sidebar-overlay {
                position: fixed; inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1040;
            }
            .sidebar-overlay.show { display: block; }
        }
        .hamburger-btn {
            display: none;
            background: none; border: none;
            font-size: 22px; color: #2d7a5f;
            cursor: pointer; padding: 4px 8px;
        }
        @media (max-width: 767.98px) {
            .hamburger-btn { display: inline-flex; }
        }

        .topbar {
            background-color: #fff;
            padding: 10px 24px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        @media (max-width: 767.98px) {
            .topbar { padding: 10px 14px; }
        }
        .topbar .welcome-text {
            font-weight: 600;
            color: #2d7a5f;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .topbar .icon-btn {
            background: none;
            border: none;
            font-size: 19px;
            color: #4dd9c0;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 8px;
            transition: background 0.2s;
            position: relative;
        }
        .topbar .icon-btn:hover { background: #f0fdf9; }

        .settings-dropdown {
            min-width: 160px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            padding: 6px;
        }
        .settings-dropdown .dropdown-item {
            border-radius: 8px;
            font-size: 13px;
            padding: 9px 14px;
            color: #2d7a5f;
            font-weight: 500;
        }
        .settings-dropdown .dropdown-item:hover {
            background: linear-gradient(90deg, #90d870, #4dd9c0);
            color: #fff;
        }
        .settings-dropdown .dropdown-item.text-danger { color: #e05252 !important; }
        .settings-dropdown .dropdown-item.text-danger:hover {
            background: linear-gradient(90deg, #f87171, #ef4444);
            color: #fff !important;
        }
        .dropdown-divider { margin: 4px 0; }
        .main-content { padding: 16px; overflow-x: hidden; max-width: 100vw; }
        @media (min-width: 768px) {
            .main-content { padding: 28px; }
        }
        body { overflow-x: hidden; }

        /* ── NOTIFICATION BADGE ── */
        .notif-badge {
            position: absolute;
            top: 0px; right: 2px;
            min-width: 16px; height: 16px;
            background: #e05252;
            border-radius: 50%;
            font-size: 9px; font-weight: 700;
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            padding: 0 3px;
        }

        /* ── NOTIFICATION DROPDOWN ── */
        .notif-dropdown {
            min-width: 300px;
            max-width: calc(100vw - 32px);
            border: none; border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            padding: 0; overflow: hidden;
        }
        .notif-dropdown .notif-header {
            background: linear-gradient(90deg, #90d870, #4dd9c0);
            padding: 10px 16px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .notif-dropdown .notif-header span {
            color: #fff; font-weight: 700; font-size: 13px;
        }
        .notif-dropdown .notif-header a {
            color: rgba(255,255,255,0.85); font-size: 11px; text-decoration: none;
        }
        .notif-item {
            padding: 10px 16px;
            border-bottom: 1px solid #f0f9f6;
            cursor: pointer; transition: background 0.2s;
        }
        .notif-item:hover { background: #f0f9f6; }
        .notif-item.unread { background: #e8f8f3; }
        .notif-item .notif-title {
            font-size: 12px; font-weight: 700; color: #2d7a5f;
        }
        .notif-item .notif-msg { font-size: 11px; color: #555; margin-top: 2px; }
        .notif-item .notif-time { font-size: 10px; color: #aaa; margin-top: 3px; }
        .notif-empty {
            padding: 20px; text-align: center;
            font-size: 12px; color: #888;
        }

        /* ── PESO FORM STYLES (shared — used by NSRP walk-in form, ubp.) ── */
        .peso-label {
            font-size: 11.5px;
            font-weight: 600;
            color: #2d7a5f;
            margin-bottom: 4px;
            display: block;
        }
        .peso-input {
            font-size: 13px;
            border: 1.5px solid #d8ece5;
            border-radius: 8px;
            padding: 8px 12px;
        }
        .peso-input:focus {
            border-color: #4dd9c0;
            box-shadow: 0 0 0 0.2rem rgba(77,217,192,0.15);
        }
        .peso-card {
            background: #fff;
            border-radius: 14px;
            border: none;
        }
        .peso-card-body {
            padding: 24px;
        }
        .btn-peso {
            background: linear-gradient(90deg, #90d870, #4dd9c0);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
        }
        .btn-peso:hover {
            color: #fff;
            opacity: 0.92;
        }
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="d-flex">

    {{-- SIDEBAR --}}
    <div class="sidebar">
        <div class="brand">
            <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo">
            <div class="brand-text">
                <div class="title">PUBLIC EMPLOYMENT SERVICE OFFICE<br>A Web-based Job Management System</div>
            </div>
            @php $staffRole = optional(Auth::user()->staff)->staff_role ?? 'staff'; @endphp
            <span class="staff-role-badge">
                {{ ['job_fair' => 'JOB FAIR', 'job_vacancy' => 'JOB VACANCY', 'lra' => 'LRA', 'sra' => 'SRA'][$staffRole] ?? strtoupper($staffRole) }} Staff Portal
            </span>
        </div>

        {{-- SRA Nav --}}
        @if($staffRole === 'sra')
            <a href="{{ route('staff.dashboard') }}"
               class="{{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('staff.registrations') }}"
               class="{{ request()->routeIs('staff.registrations*') || request()->is('staff/jobseekers*') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i> Jobseekers
            </a>
            <a href="{{ route('staff.nsrp') }}"
               class="{{ request()->routeIs('staff.nsrp*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-person-fill"></i> NSRP Form
            </a>
            <a href="{{ route('staff.employers') }}"
               class="{{ request()->is('staff/employers*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Employers
            </a>
            <a href="{{ route('staff.inhouse') }}"
               class="{{ request()->is('staff/inhouse*') || request()->is('staff/jobs') || request()->is('staff/jobs/*') || request()->is('staff/jobfair*') ? 'active' : '' }}">
                <i class="bi bi-briefcase-fill"></i> Manage Job Activities
            </a>
            <a href="{{ route('staff.reports') }}"
               class="{{ request()->is('staff/reports*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill"></i> Reports
            </a>

        {{-- LRA Nav --}}
        @elseif($staffRole === 'lra')
            <a href="{{ route('staff.dashboard') }}"
               class="{{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('staff.registrations') }}"
               class="{{ request()->routeIs('staff.registrations*') || request()->is('staff/jobseekers*') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i> Jobseekers
            </a>
            <a href="{{ route('staff.nsrp') }}"
               class="{{ request()->routeIs('staff.nsrp*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-person-fill"></i> NSRP Form
            </a>
            <a href="{{ route('staff.employers') }}"
               class="{{ request()->is('staff/employers*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Employers
            </a>
            <a href="{{ route('staff.inhouse') }}"
               class="{{ request()->is('staff/inhouse*') || request()->is('staff/jobs') || request()->is('staff/jobs/*') || request()->is('staff/jobfair*') ? 'active' : '' }}">
                <i class="bi bi-briefcase-fill"></i> Manage Job Activities
            </a>
            <a href="{{ route('staff.reports') }}"
               class="{{ request()->is('staff/reports*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill"></i> Reports
            </a>

        {{-- JOB FAIR Nav --}}
        @elseif($staffRole === 'job_fair')
            <a href="{{ route('staff.dashboard') }}"
               class="{{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('staff.jobfair.events') }}"
               class="{{ request()->routeIs('staff.jobfair.events*') ? 'active' : '' }}">
                <i class="bi bi-calendar-event-fill"></i> Job Fair Events
            </a>
            <a href="{{ route('staff.jobfair.postings') }}"
               class="{{ request()->routeIs('staff.jobfair.postings*') ? 'active' : '' }}">
                <i class="bi bi-briefcase-fill"></i> Job Postings
            </a>
            <a href="{{ route('staff.jobfair.send') }}"
               class="{{ request()->routeIs('staff.jobfair.send*') ? 'active' : '' }}">
                <i class="bi bi-megaphone-fill"></i> Notification
            </a>
            <a href="{{ route('staff.reports') }}"
               class="{{ request()->routeIs('staff.reports*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill"></i> Reports
            </a>

        {{-- JOB VACANCY Nav --}}
        @elseif($staffRole === 'job_vacancy')
            <a href="{{ route('staff.dashboard') }}"
               class="{{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('staff.employers') }}"
               class="{{ request()->is('staff/employers*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Employers
            </a>
            <a href="{{ route('staff.jobs') }}"
               class="{{ request()->is('staff/jobs') || request()->is('staff/jobs/*') || request()->is('staff/jobfair*') ? 'active' : '' }}">
                <i class="bi bi-briefcase-fill"></i> Manage Job Activities
            </a>
            <a href="{{ route('staff.reports.employers') }}"
               class="{{ request()->routeIs('staff.reports.employers*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-fill"></i> Reports
            </a>
        @endif
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    {{-- MAIN --}}
    <div class="flex-grow-1">

        {{-- TOPBAR --}}
        @php
            $staffRecordForNotif = \App\Models\Staff::where('user_id', Auth::id())->first();
            $staffUnreadCount = $staffRecordForNotif
                ? \App\Models\Announcement::where('staff_id', $staffRecordForNotif->id)->where('is_read', false)->count()
                : 0;
            $staffNotifications = $staffRecordForNotif
                ? \App\Models\Announcement::where('staff_id', $staffRecordForNotif->id)->latest()->take(5)->get()
                : collect();
        @endphp

        <div class="topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="hamburger-btn" onclick="toggleSidebar()" type="button">
                    <i class="bi bi-list"></i>
                </button>
                <span class="welcome-text">
                    <i class="bi bi-person-badge"></i>
                    Welcome, {{ Auth::user()->name }}
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">

                {{-- Notification Bell --}}
                <div class="dropdown">
                    <button class="icon-btn dropdown-toggle"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            title="Notifications"
                            style="text-decoration:none;"
                            onclick="staffMarkAllReadOnOpen()">
                        <i class="bi bi-bell"></i>
                        @if($staffUnreadCount > 0)
                            <span class="notif-badge" id="staffNotifBadge">{{ $staffUnreadCount > 9 ? '9+' : $staffUnreadCount }}</span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end notif-dropdown">
                        <li>
                            <div class="notif-header">
                                <span><i class="bi bi-bell me-1"></i> Notifications</span>
                                @if($staffUnreadCount > 0)
                                    <form id="staff-mark-all-read"
                                        action="{{ route('staff.notifications.markAllRead') }}"
                                        method="POST" style="display:none;">
                                        @csrf
                                    </form>
                                    <a href="#" onclick="event.preventDefault();
                                        document.getElementById('staff-mark-all-read').submit();">
                                        Mark all as read
                                    </a>
                                @endif
                            </div>
                        </li>
                        @forelse($staffNotifications as $notif)
                        <li>
                            <a href="{{ 
                            $notif->reference_type === 'employer_requirement' ? route('staff.requirements.view', $notif->reference_id) :
                            ($notif->reference_type === 'employer_registration' ? route('staff.employers', ['tab' => 'pre']) :
                            ($notif->reference_type === 'jobseeker_registration' ? route('staff.registrations.view', $notif->reference_id) :
                            ($notif->reference_type === 'job' ? route('staff.jobs') :
                            ($notif->reference_type === 'inhouse_schedule' ? route('staff.inhouse') :
                            ($notif->reference_type === 'job_fair' ? route('staff.jobfair.events') : route('staff.notifications.index'))))))
                           }}"
                           class="notif-item {{ !$notif->is_read ? 'unread' : '' }} text-decoration-none d-block"
                           onclick="staffMarkRead({{ $notif->id }})">
                                <div class="notif-title">
                                    <i class="bi bi-bell-fill me-1" style="color:#4dd9c0;"></i>
                                    {{ $notif->title }}
                                </div>
                                <div class="notif-msg">{{ $notif->message }}</div>
                                <div class="notif-time">{{ $notif->created_at->diffForHumans() }}</div>
                            </a>
                        </li>
                        @empty
                        <li>
                            <div class="notif-empty">
                                <i class="bi bi-bell-slash" style="font-size:24px; color:#c0e8dc;"></i>
                                <div class="mt-2">No notifications yet</div>
                            </div>
                        </li>
                        @endforelse
                        <li>
                            <a href="{{ route('staff.notifications.index') }}"
                               class="text-decoration-none d-block text-center py-2"
                               style="font-size:12px;font-weight:700;color:#2d7a5f;border-top:1px solid #f0f9f6;">
                                View More <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="dropdown">
                    <button class="icon-btn dropdown-toggle"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            title="Settings">
                        <i class="bi bi-gear"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end settings-dropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('staff.profile') }}">
                                <i class="bi bi-person me-2"></i> My Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- CONTENT --}}
        <div class="main-content">
            @yield('content')
        </div>
    </div>
</div>

@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.add('sidebar-open');
    document.getElementById('sidebarOverlay').classList.add('show');
}
function closeSidebar() {
    document.querySelector('.sidebar').classList.remove('sidebar-open');
    document.getElementById('sidebarOverlay').classList.remove('show');
}
document.querySelectorAll('.sidebar a').forEach(function(link) {
    link.addEventListener('click', closeSidebar);
});

function staffMarkRead(id) {
    fetch('/staff/notifications/' + id + '/read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    });
}

function staffMarkAllReadOnOpen() {
    const badge = document.getElementById('staffNotifBadge');
    if (!badge) return;

    fetch('{{ route('staff.notifications.markAllRead') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(() => {
        badge.remove();
        document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
    });
}

@if(session('success'))
    Swal.fire({
        title: 'Success!',
        text: @json(session('success')),
        icon: 'success',
        confirmButtonColor: '#4dd9c0',
        confirmButtonText: 'OK',
    });
@endif

@if(session('error'))
    Swal.fire({
        title: 'Oops!',
        text: @json(session('error')),
        icon: 'error',
        confirmButtonColor: '#e05252',
        confirmButtonText: 'OK',
    });
@endif

@if($errors->any())
    Swal.fire({
        title: 'Oops!',
        text: @json($errors->first()),
        icon: 'error',
        confirmButtonColor: '#e05252',
        confirmButtonText: 'OK',
    });
@endif
</script>


</body>
</html>