<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PESO Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; margin: 0; }

        ::-webkit-scrollbar { width: 0; height: 0; display: none; }
        html { scrollbar-width: none; }

        .sidebar {
            width: 200px;
            min-width: 200px;
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
            width: 88px;
            height: 88px;
            object-fit: contain;
            border-radius: 50%;
            margin-bottom: 8px;
        }
        .sidebar .brand-text .title {
            font-size: 10px;
            font-weight: 700;
            color: #fff;
            line-height: 1.4;
        }
        .sidebar .brand-text .subtitle {
            font-size: 10px;
            font-weight: 600;
            color: #fff;
            line-height: 1.3;
            margin-top: 10px;
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

        /* ── NOTIFICATION BADGE ── */
        .notif-badge {
            position: absolute;
            top: 0px;
            right: 2px;
            min-width: 16px;
            height: 16px;
            background: #e05252;
            border-radius: 50%;
            font-size: 9px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 3px;
        }

        /* ── NOTIFICATION DROPDOWN ── */
        .notif-dropdown {
            min-width: 300px;
            max-width: calc(100vw - 32px);
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            padding: 0;
            overflow: hidden;
        }
        .notif-dropdown .notif-header {
            background: linear-gradient(90deg, #90d870, #4dd9c0);
            padding: 10px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notif-dropdown .notif-header span {
            color: #fff;
            font-weight: 700;
            font-size: 13px;
        }
        .notif-dropdown .notif-header a {
            color: rgba(255,255,255,0.85);
            font-size: 11px;
            text-decoration: none;
        }
        .notif-item {
            padding: 10px 16px;
            border-bottom: 1px solid #f0f9f6;
            cursor: pointer;
            transition: background 0.2s;
        }
        .notif-item:hover { background: #f0f9f6; }
        .notif-item.unread { background: #e8f8f3; }
        .notif-item .notif-title {
            font-size: 12px;
            font-weight: 700;
            color: #2d7a5f;
        }
        .notif-item .notif-msg {
            font-size: 11px;
            color: #555;
            margin-top: 2px;
        }
        .notif-item .notif-time {
            font-size: 10px;
            color: #aaa;
            margin-top: 3px;
        }
        .notif-empty {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }

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
        .main-content { padding: 16px; }
        @media (min-width: 768px) {
            .main-content { padding: 28px; }
        }
    </style>
</head>
<body>
<div class="d-flex" style="min-height:100vh;">

    {{-- SIDEBAR --}}
    <div class="sidebar">
        <div class="brand">
            <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo">
            <div class="brand-text">
                <div class="title"><small>PUBLIC EMPLOYMENT SERVICE OFFICE<br>A Web-based Job Management System</small></div>
                <div class="subtitle">Admin Portal</div>
            </div>
        </div>

        <a href="{{ route('admin.dashboard') }}"
           class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('admin.users.manage') }}"
           class="{{ request()->routeIs('admin.users.manage') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Manage Users
        </a>
        <a href="{{ route('admin.job.activities') }}"
           class="{{ request()->routeIs('admin.job.activities*') ? 'active' : '' }}">
            <i class="bi bi-calendar2-check-fill"></i> Job Activities
        </a>
        <a href="{{ route('admin.registrations') }}"
           class="{{ request()->routeIs('admin.registrations') || request()->routeIs('admin.registration.view') ? 'active' : '' }}">
            <i class="bi bi-person-lines-fill"></i> Jobseekers
        </a>
        <a href="{{ route('admin.reports') }}"
           class="{{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-fill"></i> Reports
        </a>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    {{-- MAIN --}}
    <div class="flex-grow-1 d-flex flex-column" style="min-height:100vh;">

        {{-- TOPBAR --}}
        @php
            $unreadCount = \App\Models\Announcement::where('is_read', false)->count();

            $notifications = \App\Models\Announcement::latest()->take(5)->get();
        @endphp

        <div class="topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="hamburger-btn" onclick="toggleSidebar()" type="button">
                    <i class="bi bi-list"></i>
                </button>
                <span class="welcome-text">
                    <i class="bi bi-person-circle"></i>
                    Welcome, PESO Admin
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
                            onclick="markAllReadOnOpen()">
                        <i class="bi bi-bell"></i>
                        @if($unreadCount > 0)
                            <span class="notif-badge" id="notifBadge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end notif-dropdown">
                        <li>
                            <div class="notif-header">
    <span><i class="bi bi-bell me-1"></i> Notifications</span>
    <div class="d-flex gap-2">
        @if($unreadCount > 0)
            <a href="#" onclick="event.preventDefault(); document.getElementById('mark-all-read').submit();">
                Mark all as read
            </a>
            <form id="mark-all-read" action="{{ route('admin.notifications.markAllRead') }}" method="POST" style="display:none;">
                @csrf
            </form>
            <span style="color:rgba(255,255,255,0.5);">|</span>
        @endif
        @if($notifications->count() > 0)
            <a href="#" onclick="event.preventDefault(); document.getElementById('clear-all-notif').submit();">
                Clear all
            </a>
            <form id="clear-all-notif" action="{{ route('admin.notifications.clearAll') }}" method="POST" style="display:none;">
                @csrf
            </form>
        @endif
    </div>
</div>
                        </li>
                        @forelse($notifications as $notif)
                        <li>
                            <a href="{{ $notif->reference_type === 'registration' ? route('admin.registration.view', $notif->reference_id) : route('admin.dashboard') }}"
                               class="notif-item {{ !$notif->is_read ? 'unread' : '' }} text-decoration-none d-block"
                               onclick="markRead({{ $notif->id }})">
                                <div class="notif-title">
                                    <i class="bi bi-person-fill-check me-1" style="color:#4dd9c0;"></i>
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
                            <a href="{{ route('admin.notifications.index') }}"
                               class="text-decoration-none d-block text-center py-2"
                               style="font-size:12px;font-weight:700;color:#2d7a5f;border-top:1px solid #f0f9f6;">
                                View More <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Settings Dropdown --}}
                <div class="dropdown">
                    <button class="icon-btn dropdown-toggle"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            title="Settings">
                        <i class="bi bi-gear"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end settings-dropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.profile') }}">
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
        <div class="main-content flex-grow-1">
            @yield('content')
        </div>

        {{-- FOOTER --}}
        <div style="margin-top:24px; padding:16px 24px; background:linear-gradient(180deg,#1a5c45,#0d1f18); border-radius:14px 14px 0 0; text-align:center; box-shadow:0 -4px 16px rgba(0,0,0,0.06);">
            <div style="font-size:11.5px; color:rgba(255,255,255,0.75);">
                © {{ date('Y') }} Southern de Oro Philippines College, Bachelor of Science in Information Technology Researchers — All rights reserved
            </div>
        </div>

    </div>
</div>

@stack('scripts')
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
document.querySelectorAll('.sidebar a').forEach(function(link) {
    link.addEventListener('click', closeSidebar);
});

function markRead(id) {
    fetch('/admin/notifications/' + id + '/read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    });
}

function markAllReadOnOpen() {
    const badge = document.getElementById('notifBadge');
    if (!badge) return; // wala nay unread, wala nay kinahanglan buhaton

    fetch('{{ route('admin.notifications.markAllRead') }}', {
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