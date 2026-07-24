<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PESO — Company Portal</title>
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

        body {
            background: var(--peso-bg);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }

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
            width: 64px;
            height: 64px;
            object-fit: contain;
            margin-bottom: 8px;
        }

        .sidebar-brand .brand-title {
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            line-height: 1.4;
        }

        .sidebar-brand .brand-sub {
            display: inline-block;
            margin-top: 18px;
            padding: 3px 12px;
            border-radius: 20px;
            background: rgba(255,255,255,0.15);
            font-size: 10px; font-weight: 700;
            color: #fff;
            letter-spacing: 0.5px;
        }

        .sidebar-nav {
            padding: 16px 12px;
            flex: 1;
        }

        .sidebar-nav .nav-item {
            margin-bottom: 4px;
        }

        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.85);
            font-size: 13px;
            font-weight: 500;
            padding: 10px 14px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            background: rgba(255,255,255,0.25);
            color: #fff;
            transform: translateX(4px);
        }

        .sidebar-nav .nav-link i {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,0.3);
        }

        .sidebar-footer .company-badge {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
        }

        .sidebar-footer .company-badge > div:last-child {
            min-width: 0;
            flex: 1;
        }

        .sidebar-footer .company-avatar {
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
            flex-shrink: 0;
        }

        .sidebar-footer .company-name {
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-footer .company-role {
            font-size: 10px;
            color: rgba(255,255,255,0.75);
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
            position: fixed;
            top: 0;
            left: 240px;
            right: 0;
            height: 60px;
            background: #fff;
            border-bottom: 1px solid #e8f5f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 99;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        @media (max-width: 767.98px) {
            .topbar { left: 0; padding: 0 14px; }
        }

        .topbar .page-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--peso-dark);
        }

        .topbar .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar .btn-notif {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--peso-bg);
            border: 1px solid var(--peso-border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--peso-dark);
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .topbar .btn-notif:hover {
            background: var(--peso-border);
        }

        .topbar .dropdown-toggle {
            background: none;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 10px;
            transition: background 0.2s;
        }

        .topbar .dropdown-toggle::after {
            display: none;
        }

        .topbar .dropdown-toggle:hover {
            background: var(--peso-bg);
        }

        .topbar .dropdown-toggle span {
            font-size: 13px;
            font-weight: 600;
            color: var(--peso-dark);
        }

        .topbar .dropdown-menu {
            border: 1px solid var(--peso-border);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            min-width: 160px;
            max-width: calc(100vw - 24px) !important;
            padding: 8px;
        }

        .topbar .dropdown-item {
            border-radius: 8px;
            font-size: 13px;
            color: var(--peso-dark);
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topbar .dropdown-item:hover {
            background: var(--peso-bg);
            color: var(--peso-dark);
        }

        .topbar .dropdown-item.text-danger:hover {
            background: #fff5f5;
        }

        /* ── MAIN CONTENT ── */
        .main-content {
            margin-left: 240px;
            padding-top: 60px;
            min-height: 100vh;
        }

        .content-inner {
            padding: 24px;
        }

        @media (max-width: 767.98px) {
            .main-content { margin-left: 0; }
            .content-inner { padding: 14px; }
        }

        /* ── CARDS ── */
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
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
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #90d870, #4dd9c0);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 22px;
            margin-bottom: 12px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--peso-dark);
            line-height: 1;
        }

        .stat-label {
            font-size: 12px;
            color: #888;
            margin-top: 4px;
        }

        /* ── BUTTONS ── */
        .btn-peso {
            background: linear-gradient(90deg, #90d870, #4dd9c0);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            padding: 9px 20px;
            font-size: 13px;
            transition: opacity 0.2s, transform 0.2s;
            box-shadow: 0 4px 12px rgba(77,217,192,0.3);
        }

        .btn-peso:hover {
            opacity: 0.9;
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-peso-outline {
            background: transparent;
            border: 1.5px solid var(--peso-green);
            color: var(--peso-dark);
            font-weight: 600;
            border-radius: 10px;
            padding: 8px 20px;
            font-size: 13px;
            transition: all 0.2s;
        }

        .btn-peso-outline:hover {
            background: var(--peso-bg);
            color: var(--peso-dark);
        }

        /* ── TABLE ── */
        .peso-table {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e8f5f0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }

        .peso-table thead th {
            background: linear-gradient(90deg, #90d870, #4dd9c0);
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            padding: 12px 16px;
            border: none;
        }

        .peso-table tbody td {
            font-size: 13px;
            color: var(--peso-dark);
            padding: 12px 16px;
            vertical-align: middle;
            border-color: #f0f9f6;
        }

        .peso-table tbody tr:hover {
            background: var(--peso-bg);
        }

        /* ── BADGES ── */
        .badge-open {
            background: #e8f8f3;
            color: var(--peso-dark);
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-closed {
            background: #f5f5f5;
            color: #888;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-pending {
            background: #fff8e1;
            color: #e6a817;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-hired {
            background: #e8f8f3;
            color: var(--peso-dark);
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-rejected {
            background: #fff5f5;
            color: #e53935;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        /* ── FORM ── */
        .peso-input {
            border: 1.5px solid var(--peso-border);
            border-radius: 10px;
            font-size: 13px;
            padding: 10px 14px;
            color: var(--peso-dark);
            transition: border-color 0.2s;
        }

        .peso-input:focus {
            border-color: var(--peso-green);
            box-shadow: 0 0 0 3px rgba(77,217,192,0.15);
            outline: none;
        }

        .peso-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--peso-dark);
            margin-bottom: 6px;
        }

        /* ── CARD PANEL ── */
        .peso-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e8f5f0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        .peso-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f0f9f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .peso-card-header h6 {
            font-size: 14px;
            font-weight: 700;
            color: var(--peso-dark);
            margin: 0;
        }

        .peso-card-body {
            padding: 20px;
        }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center;
            padding: 48px 20px;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #90d870, #4dd9c0);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 36px;
            color: #fff;
            animation: pulse 2s infinite;
        }

        .empty-state h6 {
            font-size: 15px;
            font-weight: 700;
            color: var(--peso-dark);
            margin-bottom: 6px;
        }

        .empty-state p {
            font-size: 13px;
            color: #888;
            margin: 0;
        }

        /* ── ALERTS ── */
        .alert-peso {
            border-radius: 10px;
            border: none;
            font-size: 13px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeInUp 0.3s ease;
        }

        .alert-success {
            background: #e8f8f3;
            color: var(--peso-dark);
        }

        .alert-danger {
            background: #fff5f5;
            color: #c62828;
        }

        /* ── ANIMATIONS ── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50%       { transform: scale(1.06); }
        }

        .fade-in { animation: fadeInUp 0.5s ease forwards; }
        .fade-in-1 { animation: fadeInUp 0.5s ease 0.1s forwards; opacity: 0; }
        .fade-in-2 { animation: fadeInUp 0.5s ease 0.2s forwards; opacity: 0; }
        .fade-in-3 { animation: fadeInUp 0.5s ease 0.3s forwards; opacity: 0; }
    </style>
</head>
<body>

    {{-- ── SIDEBAR ── --}}
    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo">
            <div class="brand-title">A Web-based Job Management System for PESO CDO</div>
            <div class="brand-sub">Company Portal</div>
        </div>

        @php
            $sidebarEmployerNsrp = \App\Models\EmployerNsrpRegistration::where('user_id', Auth::id())->first();
            $sidebarReqApproved = $sidebarEmployerNsrp
                ? \App\Models\EmployerRequirement::where('user_id', $sidebarEmployerNsrp->id)->where('status', 'approved')->exists()
                : false;
        @endphp
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('company.dashboard') }}"
                       class="nav-link {{ request()->routeIs('company.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('company.jobs') }}"
                       id="jobVacancyNavLink"
                       class="nav-link {{ request()->routeIs('company.jobs') || request()->routeIs('company.jobs.*') || request()->routeIs('company.applicants*') ? 'active' : '' }}"
                       @if(!$sidebarReqApproved) onclick="return openComplyModal(event)" @endif>
                        <i class="bi bi-send"></i> Job Vacancy Request
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('company.jobseekers') }}"
                       class="nav-link {{ request()->routeIs('company.jobseekers*') ? 'active' : '' }}">
                        <i class="bi bi-calendar2-week-fill"></i> Schedule Job Vacancy
                    </a>
                </li>
                <li class="nav-item">
    <a href="{{ route('company.requirements') }}"
       class="nav-link {{ request()->routeIs('company.requirements*') ? 'active' : '' }} d-flex justify-content-between align-items-center">
        <span><i class="bi bi-upload"></i> Requirements</span>
        @if(!$sidebarReqApproved)
        <span class="badge" style="background:#fff5f5;color:#e53935;font-size:9px;padding:3px 8px;border-radius:10px;font-weight:700;">Required</span>
        @endif
    </a>
</li>
                <li class="nav-item">
                    <a href="{{ route('company.jobfair') }}"
                       class="nav-link {{ request()->routeIs('company.jobfair*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event-fill"></i> Job Fair
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('company.reports') }}"
                       class="nav-link {{ request()->routeIs('company.reports*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-fill"></i> Reports
                    </a>
                </li>

            </ul>
        </nav>

        <div class="sidebar-footer">
            <div class="company-badge">
                <div class="company-avatar">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <div class="company-name">{{ optional(Auth::user()->employerNsrp)->company_name ?? Auth::user()->name ?? 'Company' }}</div>
                    <div class="company-role">Company Account</div>
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    {{-- ── COMPLY REQUIREMENTS MODAL ── --}}
    <div class="modal fade" id="complyRequirementsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 8px 32px rgba(0,0,0,0.12);">
                <div class="modal-header" style="background:linear-gradient(90deg,#90d870,#4dd9c0);border:none;">
                    <h6 class="modal-title fw-bold text-white">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Requirements Needed
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p style="font-size:13px;color:#2d7a5f;font-weight:600;margin-bottom:12px;">
                        You must comply with these 6 requirements before requesting a job vacancy posting:
                    </p>
                    <ul style="font-size:12px;color:#555;line-height:1.9;padding-left:20px;margin-bottom:0;">
                        <li>CDO Business Permit 2026</li>
                        <li>SEC / DTI</li>
                        <li>Company Profile</li>
                        <li>Filled-up NSRP Establishment Form</li>
                        <li>Certificate of No Pending Case</li>
                        <li>Vacancy Posting</li>
                    </ul>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e8f5f0;">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="{{ route('company.requirements') }}" class="btn btn-sm px-4 fw-semibold"
                       style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;border:none;border-radius:8px;">
                        <i class="bi bi-upload me-1"></i> Go to Requirements
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TOPBAR ── --}}
    <div class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="hamburger-btn" onclick="toggleSidebar()" type="button">
                <i class="bi bi-list"></i>
            </button>
            <div class="page-title">@yield('page-title', 'Dashboard')</div>
        </div>
        <div class="topbar-right">

            {{-- Notification Bell --}}
            <div class="dropdown">
                <a class="btn-notif position-relative" data-bs-toggle="dropdown" href="#">
                    <i class="bi bi-bell"></i>
                    @php
                        $employerNsrp = \App\Models\EmployerNsrpRegistration::where('user_id', Auth::id())->first();
                        $unreadCount = $employerNsrp
                            ? \App\Models\Announcement::where('employer_id', $employerNsrp->id)->where('is_read', false)->count()
                            : 0;
                    @endphp
                    @if($unreadCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill"
                              style="background:#e53935; font-size:9px; padding:3px 5px;">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width:300px; max-height:400px; overflow-y:auto;">
                    @php
                        $notifications = $employerNsrp
                            ? \App\Models\Announcement::where('employer_id', $employerNsrp->id)->orderBy('created_at', 'desc')->take(10)->get()
                            : collect();
                    @endphp
                    <li class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                        <span style="font-size:13px; font-weight:700; color:#2d7a5f;">
                            <i class="bi bi-bell me-1"></i> Notifications
                        </span>
                        @if($notifications->count() > 0)
                        <form action="{{ route('company.notifications.clearAll') }}" method="POST">
                            @csrf
                            <button type="submit"
                                style="font-size:11px;color:#e05252;background:none;border:none;cursor:pointer;font-weight:600;">
                                Clear All
                            </button>
                        </form>
                        @endif
                    </li>
                    @forelse($notifications as $notif)
                        <li>
                            <a class="dropdown-item py-2 {{ !$notif->is_read ? 'bg-light' : '' }}"
                               href="#"
                               onclick="markRead({{ $notif->id }})">
                                <div style="font-size:12px; font-weight:{{ !$notif->is_read ? '700' : '500' }}; color:#2d7a5f;">
                                    {{ $notif->title }}
                                </div>
                                <div style="font-size:11px; color:#888; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:240px;">{{ $notif->message }}</div>
                                <div style="font-size:10px; color:#aaa;">{{ $notif->created_at->diffForHumans() }}</div>
                            </a>
                        </li>
                    @empty
                        <li class="text-center py-4">
                            <i class="bi bi-bell-slash" style="font-size:24px; color:#ccc;"></i>
                            <p style="font-size:12px; color:#aaa; margin-top:8px;">No notifications yet</p>
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="dropdown">
                <button class="dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-gear" style="color: var(--peso-dark); font-size:18px;"></i>
                    <span>{{ optional(Auth::user()->employerNsrp)->company_name ?? Auth::user()->name ?? 'Company' }}</span>
                    <i class="bi bi-chevron-down" style="font-size:11px; color:#888;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('company.profile') }}">
                            <i class="bi bi-person-circle" style="color: var(--peso-green);"></i>
                            My Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
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
        function openComplyModal(event) {
            event.preventDefault();
            new bootstrap.Modal(document.getElementById('complyRequirementsModal')).show();
            return false;
        }

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
            fetch(`/company/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(() => location.reload());
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
    @yield('scripts')
</body>
</html>