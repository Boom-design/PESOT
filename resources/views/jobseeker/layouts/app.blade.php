<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PESO — Jobseeker Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --peso-green:  #4dd9c0;
            --peso-light:  #90d870;
            --peso-dark:   #2d7a5f;
            --peso-border: #a8e6cf;
            --peso-bg:     #f0f9f6;
        }

        * { box-sizing: border-box; }
        body { background: var(--peso-bg); font-family: 'Segoe UI', sans-serif; margin: 0; }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: linear-gradient(180deg, #1a5c45, #0d1f18);
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: 2px 0 12px rgba(0,0,0,0.08);
        }
        .sidebar-brand {
            padding: 28px 20px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.3);
        }
        .sidebar-brand img {
            width: 64px; height: 64px;
            object-fit: contain; margin-bottom: 8px;
        }
        .sidebar-brand .brand-title {
            font-size: 12px; font-weight: 700;
            color: #fff; line-height: 1.4;
        }
        .sidebar-brand .brand-sub {
            display: inline-block;
            margin-top: 17px;
            padding: 3px 12px;
            border-radius: 20px;
            background: rgba(255,255,255,0.15);
            font-size: 10px; font-weight: 700;
            color: #fff;
            letter-spacing: 0.5px;
        }
        .sidebar-nav { padding: 16px 12px; flex: 1; }
        .sidebar-nav .nav-item { margin-bottom: 4px; }
        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.85);
            font-size: 13px; font-weight: 500;
            padding: 10px 14px; border-radius: 10px;
            display: flex; align-items: center; gap: 10px;
            transition: all 0.2s; text-decoration: none;
        }
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            background: rgba(255,255,255,0.25);
            color: #fff; transform: translateX(4px);
        }
        .sidebar-nav .nav-link i { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,0.3);
        }
        .sidebar-footer .user-badge {
            background: rgba(255,255,255,0.2);
            border-radius: 10px; padding: 10px 12px;
            display: flex; align-items: center; gap: 10px;
        }
        .sidebar-footer .user-avatar {
            width: 32px; height: 32px;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 14px; flex-shrink: 0;
        }
        .sidebar-footer .user-name {
            font-size: 12px; font-weight: 600; color: #fff;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-footer .user-role {
            font-size: 10px; color: rgba(255,255,255,0.75);
        }

        /* ── RESPONSIVE SIDEBAR (mobile off-canvas) ── */
        @media (max-width: 767.98px) {
            .sidebar {
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
                z-index: 90;
            }
            .sidebar-overlay.show { display: block; }
        }
        .hamburger-btn {
            display: none;
            background: none; border: none;
            font-size: 22px; color: var(--peso-dark);
            cursor: pointer; padding: 4px 8px;
        }
        @media (max-width: 767.98px) {
            .hamburger-btn { display: inline-flex; }
        }

        /* ── TOPBAR ── */
        .topbar {
            position: fixed; top: 0; left: 240px; right: 0;
            height: 60px; background: #fff;
            border-bottom: 1px solid #e8f5f0;
            display: flex; align-items: center;
            justify-content: space-between;
            padding: 0 24px; z-index: 99;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        @media (max-width: 767.98px) {
            .topbar { left: 0; padding: 0 14px; }
        }
        .topbar .page-title {
            font-size: 15px; font-weight: 700; color: var(--peso-dark);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            max-width: 140px;
        }
        @media (min-width: 768px) {
            .topbar .page-title { max-width: none; white-space: normal; }
        }
        .topbar .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar .btn-notif {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--peso-bg); border: 1px solid var(--peso-border);
            display: flex; align-items: center; justify-content: center;
            color: var(--peso-dark); font-size: 16px;
            cursor: pointer; transition: all 0.2s; text-decoration: none;
        }
        .topbar .btn-notif:hover { background: var(--peso-border); }
        .topbar .dropdown-toggle {
            background: none; border: none;
            display: flex; align-items: center; gap: 8px;
            cursor: pointer; padding: 6px 10px;
            border-radius: 10px; transition: background 0.2s;
        }
        .topbar .dropdown-toggle::after {
            display: none;
        }
        .topbar .dropdown-toggle:hover { background: var(--peso-bg); }
        .topbar .dropdown-toggle span { font-size: 13px; font-weight: 600; color: var(--peso-dark); }
        .topbar .dropdown-menu {
            border: 1px solid var(--peso-border);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            min-width: 160px; padding: 8px;
        }
        .notif-dropdown-menu {
            min-width: 300px !important;
            max-width: calc(100vw - 32px);
            padding: 0 !important;
            overflow: hidden;
        }
        .notif-dropdown-menu .notif-header {
            background: linear-gradient(90deg, #90d870, #4dd9c0);
            padding: 12px 16px;
        }
        .notif-dropdown-menu .notif-header span {
            color: #fff; font-weight: 700; font-size: 13px;
        }
        .notif-dropdown-menu .dropdown-item {
            white-space: normal;
            padding: 10px 16px;
            border-bottom: 1px solid #f0f9f6;
        }
        .topbar .dropdown-item {
            border-radius: 8px; font-size: 13px;
            color: var(--peso-dark); padding: 8px 12px;
            display: flex; align-items: center; gap: 8px;
        }
        .topbar .dropdown-item:hover { background: var(--peso-bg); color: var(--peso-dark); }
        .topbar .dropdown-item.text-danger:hover { background: #fff5f5; }

        /* ── MAIN CONTENT ── */
        .main-content { margin-left: 240px; padding-top: 60px; min-height: 100vh; }
        .content-inner { padding: 24px; }
        @media (max-width: 767.98px) {
            .main-content { margin-left: 0; }
            .content-inner { padding: 14px; }
        }

        /* ── CARDS ── */
        .stat-card {
            background: #fff; border-radius: 16px; padding: 20px;
            border: 1px solid #e8f5f0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            transition: transform 0.2s, box-shadow 0.2s;
            animation: fadeInUp 0.5s ease forwards;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(77,217,192,0.15);
        }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            background: linear-gradient(135deg, #90d870, #4dd9c0);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 22px; margin-bottom: 12px;
        }
        .stat-value { font-size: 28px; font-weight: 700; color: var(--peso-dark); line-height: 1; }
        .stat-label { font-size: 12px; color: #888; margin-top: 4px; }

        /* ── BUTTONS ── */
        .btn-peso {
            background: linear-gradient(90deg, #90d870, #4dd9c0);
            border: none; color: #fff; font-weight: 600;
            border-radius: 10px; padding: 9px 20px; font-size: 13px;
            transition: opacity 0.2s, transform 0.2s;
            box-shadow: 0 4px 12px rgba(77,217,192,0.3);
        }
        .btn-peso:hover { opacity: 0.9; color: #fff; transform: translateY(-1px); }
        .btn-peso-outline {
            background: transparent; border: 1.5px solid var(--peso-green);
            color: var(--peso-dark); font-weight: 600;
            border-radius: 10px; padding: 8px 20px; font-size: 13px;
            transition: all 0.2s;
        }
        .btn-peso-outline:hover { background: var(--peso-bg); color: var(--peso-dark); }

        /* ── TABLE ── */
        .peso-table { background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid #e8f5f0; box-shadow: 0 2px 12px rgba(0,0,0,0.04); }
        .peso-table thead th { background: linear-gradient(90deg, #90d870, #4dd9c0); color: #fff; font-size: 12px; font-weight: 600; padding: 12px 16px; border: none; }
        .peso-table tbody td { font-size: 13px; color: var(--peso-dark); padding: 12px 16px; vertical-align: middle; border-color: #f0f9f6; }
        .peso-table tbody tr:hover { background: var(--peso-bg); }

        /* ── BADGES ── */
        .badge-open    { background:#e8f8f3; color:var(--peso-dark); font-size:11px; padding:4px 10px; border-radius:20px; font-weight:600; }
        .badge-closed  { background:#f5f5f5; color:#888; font-size:11px; padding:4px 10px; border-radius:20px; font-weight:600; }
        .badge-pending { background:#fff8e1; color:#e6a817; font-size:11px; padding:4px 10px; border-radius:20px; font-weight:600; }
        .badge-hired   { background:#e8f8f3; color:var(--peso-dark); font-size:11px; padding:4px 10px; border-radius:20px; font-weight:600; }
        .badge-rejected{ background:#fff5f5; color:#e53935; font-size:11px; padding:4px 10px; border-radius:20px; font-weight:600; }
        .badge-submitted { background:#e8f0fe; color:#3949ab; font-size:11px; padding:4px 10px; border-radius:20px; font-weight:600; }

        /* ── CARD PANEL ── */
        .peso-card { background:#fff; border-radius:16px; border:1px solid #e8f5f0; box-shadow:0 2px 12px rgba(0,0,0,0.04); overflow:hidden; }
        .peso-card-header { padding:16px 20px; border-bottom:1px solid #f0f9f6; display:flex; align-items:center; justify-content:space-between; }
        .peso-card-header h6 { font-size:14px; font-weight:700; color:var(--peso-dark); margin:0; }
        .peso-card-body { padding:20px; }

        /* ── EMPTY STATE ── */
        .empty-state { text-align:center; padding:48px 20px; }
        .empty-icon { width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg,#90d870,#4dd9c0); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; font-size:36px; color:#fff; animation:pulse 2s infinite; }
        .empty-state h6 { font-size:15px; font-weight:700; color:var(--peso-dark); margin-bottom:6px; }
        .empty-state p { font-size:13px; color:#888; margin:0; }

        /* ── ALERTS ── */
        .alert-peso { border-radius:10px; border:none; font-size:13px; padding:12px 16px; display:flex; align-items:center; gap:10px; animation:fadeInUp 0.3s ease; }
        .alert-success { background:#e8f8f3; color:var(--peso-dark); }
        .alert-danger  { background:#fff5f5; color:#c62828; }

        /* ── FORM ── */
        .peso-input { border:1.5px solid var(--peso-border); border-radius:10px; font-size:13px; padding:10px 14px; color:var(--peso-dark); transition:border-color 0.2s; }
        .peso-input:focus { border-color:var(--peso-green); box-shadow:0 0 0 3px rgba(77,217,192,0.15); outline:none; }
        .peso-label { font-size:12px; font-weight:600; color:var(--peso-dark); margin-bottom:6px; }

        /* ── ANIMATIONS ── */
        @keyframes fadeInUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
        @keyframes pulse { 0%,100%{transform:scale(1);} 50%{transform:scale(1.06);} }
        .fade-in   { animation:fadeInUp 0.5s ease forwards; }
        .fade-in-1 { animation:fadeInUp 0.5s ease 0.1s forwards; opacity:0; }
        .fade-in-2 { animation:fadeInUp 0.5s ease 0.2s forwards; opacity:0; }
        .fade-in-3 { animation:fadeInUp 0.5s ease 0.3s forwards; opacity:0; }
    </style>
</head>
<body>

{{-- ── SIDEBAR ── --}}
<div class="sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo">
        <div class="brand-title">A Web-based Job Management System for PESO CDO</div>
        <div class="brand-sub">Jobseeker Portal</div>
    </div>

    @php
        $jobseekerRegistration = \App\Models\JobseekerRegistration::where('user_id', Auth::id())->first();
        $hasNsrp = $jobseekerRegistration !== null;

        // ── Job Fair Attendance Confirmation — naa bay unresolved (na-notify na pero wala pa nag-respond) ──
        $pendingAttendanceConfirmation = $jobseekerRegistration
            ? \App\Models\JobFairRegistration::with('jobFair')
                ->where('user_id', $jobseekerRegistration->id)
                ->whereNull('is_attended')
                ->whereNotNull('attendance_notified_at')
                ->first()
            : null;
    @endphp
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('jobseeker.dashboard') }}"
                   class="nav-link {{ request()->routeIs('jobseeker.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('jobseeker.nsrp') }}"
                   class="nav-link {{ request()->routeIs('jobseeker.nsrp*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-check"></i> NSRP Form
                    @if(!$hasNsrp)
                        <span class="ms-auto" style="background:#e05252;color:#fff;font-size:9px;
                              padding:2px 6px;border-radius:10px;font-weight:700;">Required</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('jobseeker.jobs') }}"
                   class="nav-link {{ request()->routeIs('jobseeker.jobs*') ? 'active' : '' }}">
                    <i class="bi bi-briefcase"></i> Job Vacancies
                </a>
            </li>
            @if($hasNsrp)
            <li class="nav-item">
                <a href="{{ route('jobseeker.schedules') }}"
                   class="nav-link {{ request()->routeIs('jobseeker.schedules*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check-fill"></i> Schedules
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('jobseeker.history') }}"
                   class="nav-link {{ request()->routeIs('jobseeker.history*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> History
                </a>
            </li>
            @endif
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="user-badge">
            <div class="user-avatar">
                <i class="bi bi-person"></i>
            </div>
            <div>
                <div class="user-name">{{ Auth::user()->first_name ?? Auth::user()->name ?? 'Jobseeker' }}</div>
                <div class="user-role">Jobseeker Account</div>
            </div>
        </div>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- ── TOPBAR ── --}}
<div class="topbar">
    <div class="d-flex align-items-center gap-2">
        <button class="hamburger-btn" onclick="toggleSidebar()" type="button">
            <i class="bi bi-list"></i>
        </button>
        <div class="page-title">@yield('page-title', 'Dashboard')</div>
    </div>
    <div class="topbar-right">

        {{-- User Dropdown — permi mopakita para maka-logout, bisan wala pa NSRP --}}
        @unless($hasNsrp)
        <div class="dropdown">
            <button class="dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle" style="color:var(--peso-dark); font-size:18px;"></i>
                <span>{{ Auth::user()->first_name ?? Auth::user()->name ?? 'Jobseeker' }}</span>
                <i class="bi bi-chevron-down" style="font-size:11px; color:#888;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
        @endunless

        {{-- Hide notifications kung wala pay NSRP, pero mopakita permi ang settings/logout sa taas --}}
        @if($hasNsrp)
        {{-- Notification Bell --}}
        <div class="dropdown">
            <a class="btn-notif position-relative" data-bs-toggle="dropdown" href="#">
                <i class="bi bi-bell"></i>
                @php
                    $unreadCount = $jobseekerRegistration
                        ? \App\Models\Announcement::where('jobseeker_id', $jobseekerRegistration->id)->where('is_read', false)->count()
                        : 0;
                @endphp
                @if($unreadCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill"
                          style="background:#e53935; font-size:9px; padding:3px 5px;">
                        {{ $unreadCount }}
                    </span>
                @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end notif-dropdown-menu" style="max-height:400px; overflow-y:auto;">
                <li class="notif-header">
                    <span><i class="bi bi-bell me-1"></i> Notifications</span>
                </li>
                @php
                    $notifications = $jobseekerRegistration
                        ? \App\Models\Announcement::where('jobseeker_id', $jobseekerRegistration->id)->orderBy('created_at', 'desc')->take(5)->get()
                        : collect();

                    $notifTargetUrl = function ($notif) {
                        return match($notif->reference_type) {
                            'job'                     => route('jobseeker.jobs.show', $notif->reference_id),
                            'inhouse_schedule'        => route('jobseeker.schedules', ['type' => 'inhouse']),
                            'job_fair'                => route('jobseeker.schedules', ['type' => 'jobfair']),
                            'job_fair_registration'   => route('jobseeker.schedules', ['type' => 'jobfair']),
                            'jobseeker_registration'  => route('jobseeker.nsrp'),
                            default                   => route('jobseeker.notifications.index'),
                        };
                    };
                @endphp
                @forelse($notifications as $notif)
                    <li>
                        <a class="dropdown-item py-2 {{ !$notif->is_read ? 'bg-light' : '' }}"
                           href="{{ $notifTargetUrl($notif) }}"
                           onclick="markRead({{ $notif->id }})">
                            <div style="font-size:12px; font-weight:{{ !$notif->is_read ? '700' : '500' }}; color:#2d7a5f;">
                                {{ $notif->title }}
                            </div>
                            <div style="font-size:11px; color:#888;">{{ $notif->message }}</div>
                            <div style="font-size:10px; color:#aaa;">{{ $notif->created_at->diffForHumans() }}</div>
                        </a>
                    </li>
                @empty
                    <li class="text-center py-4">
                        <i class="bi bi-bell-slash" style="font-size:24px; color:#ccc;"></i>
                        <p style="font-size:12px; color:#aaa; margin-top:8px;">No notifications yet</p>
                    </li>
                @endforelse
                <li>
                    <a href="{{ route('jobseeker.notifications.index') }}"
                       class="text-decoration-none d-block text-center py-2"
                       style="font-size:12px;font-weight:700;color:#2d7a5f;border-top:1px solid #f0f9f6;">
                        View More <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </li>
            </ul>
        </div>

        {{-- User Dropdown --}}
        <div class="dropdown">
            <button class="dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle" style="color:var(--peso-dark); font-size:18px;"></i>
                <span>{{ Auth::user()->first_name ?? Auth::user()->name ?? 'Jobseeker' }}</span>
                <i class="bi bi-chevron-down" style="font-size:11px; color:#888;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @if($hasNsrp)
                <li>
                    <a class="dropdown-item" href="{{ route('jobseeker.profile') }}">
                        <i class="bi bi-person-circle" style="color:var(--peso-green);"></i>
                        My Profile
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                @endif
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
        @endif {{-- end @if($hasNsrp) for notification+settings --}}
    </div>
</div>

{{-- ── MAIN CONTENT ── --}}
<div class="main-content">
    <div class="content-inner">

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.add('sidebar-open');
        document.getElementById('sidebarOverlay').classList.add('show');
    }
    function closeSidebar() {
        document.querySelector('.sidebar').classList.remove('sidebar-open');
        document.getElementById('sidebarOverlay').classList.remove('show');
    }
    document.querySelectorAll('.sidebar-nav a').forEach(function(link) {
        link.addEventListener('click', closeSidebar);
    });

    function markRead(id) {
        fetch(`/jobseeker/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        });
        // dili mag-preventDefault — tugutan ang link mo-navigate diretso sa iyang target page
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

    @if(session('info'))
        Swal.fire({
            title: 'Matching Jobs Found!',
            text: @json(session('info')),
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#4dd9c0',
            cancelButtonColor: '#888',
            confirmButtonText: '<i class="bi bi-send me-1"></i> Apply Now',
            cancelButtonText: 'OK',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route("jobseeker.jobs") }}';
            }
        });
        @php session()->pull('info'); @endphp
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

    @if($pendingAttendanceConfirmation)
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            title: 'Job Fair Attendance',
            text: 'Did you attend/participate in {{ addslashes($pendingAttendanceConfirmation->jobFair->title ?? 'the job fair') }} today at {{ addslashes($pendingAttendanceConfirmation->jobFair->venue ?? '') }}?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4dd9c0',
            cancelButtonColor: '#e05252',
            confirmButtonText: 'Yes, I attended',
            cancelButtonText: 'No, I did not attend',
            allowOutsideClick: false,
        }).then((result) => {
            const response = result.isConfirmed ? 'yes' : 'no';
            fetch('{{ url("/jobseeker/jobfair-registrations") }}/{{ $pendingAttendanceConfirmation->id }}/attendance-response', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ response: response }),
            }).then(() => location.reload());
        });
    });
    @endif
</script>
@yield('scripts')
</body>
</html>