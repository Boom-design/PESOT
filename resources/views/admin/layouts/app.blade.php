<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.head-brand')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Design tokens and shared components. Loaded after Bootstrap so it wins. --}}
    <link rel="stylesheet" href="{{ asset('css/peso.css') }}">
</head>
<body>

{{-- ── SIDEBAR ── --}}
<div class="sidebar">
    {{-- The office name lives in the topbar. Under the logo the sidebar names
         who is signed in, which is the one thing that differs per session. --}}
    <div class="sidebar-brand">
        <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo">
        <div class="brand-user">
            <div class="avatar">
                @if(Auth::user()->profile_photo)
                    <img src="{{ asset('storage/'.Auth::user()->profile_photo) }}" alt="">
                @else
                    <i class="ph ph-user"></i>
                @endif
            </div>
            <div class="brand-user-text">
                <div class="u-name">{{ Auth::user()->name ?? 'Admin' }}</div>
                <div class="u-role">Administrator</div>
            </div>
        </div>
    </div>

    {{-- Red dot per nav item — see App\Support\NavAlerts for what counts. --}}
    @php $navAlerts = \App\Support\NavAlerts::forAdmin(); @endphp

    <nav class="sidebar-nav">
        <div class="nav-section">Main</div>
        <ul>
            <li>
                @php $isDash = request()->routeIs('admin.dashboard'); @endphp
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ $isDash ? 'active' : '' }}">
                    <i class="{{ $isDash ? 'ph-fill' : 'ph' }} ph-gauge"></i> Dashboard
                </a>
            </li>
            <li>
                @php $isUsers = request()->routeIs('admin.users.manage'); @endphp
                <a href="{{ route('admin.users.manage') }}" class="nav-link {{ $isUsers ? 'active' : '' }}">
                    <i class="{{ $isUsers ? 'ph-fill' : 'ph' }} ph-users-three"></i> Manage Users
                </a>
            </li>
        </ul>

        <div class="nav-section">Manage</div>
        <ul>
            <li>
                @php
                    $isAct = request()->routeIs('admin.job.activities*');

                    // The number says how many; the link says where. Landing on
                    // the tab holding the oldest unseen notice saves opening all
                    // four to find the red one.
                    $actTarget = ($navAlerts['job_activities'] ?? 0) > 0
                        ? route('admin.job.activities', ['tab' => \App\Support\AdminInbox::firstTabWithNews()])
                        : route('admin.job.activities');
                @endphp
                <a href="{{ $actTarget }}" class="nav-link {{ $isAct ? 'active' : '' }}">
                    <i class="{{ $isAct ? 'ph-fill' : 'ph' }} ph-calendar-check"></i> Job Activities
                    @include('partials.nav-dot', ['navKey' => 'job_activities'])
                </a>
            </li>
            <li>
                @php $isReg = request()->routeIs('admin.registrations') || request()->routeIs('admin.registration.view'); @endphp
                <a href="{{ route('admin.registrations') }}" class="nav-link {{ $isReg ? 'active' : '' }}">
                    <i class="{{ $isReg ? 'ph-fill' : 'ph' }} ph-user-list"></i> Jobseekers
                    @include('partials.nav-dot', ['navKey' => 'registrations'])
                </a>
            </li>
            <li>
                @php $isRep = request()->routeIs('admin.reports*'); @endphp
                <a href="{{ route('admin.reports') }}" class="nav-link {{ $isRep ? 'active' : '' }}">
                    <i class="{{ $isRep ? 'ph-fill' : 'ph' }} ph-chart-bar"></i> Reports
                </a>
            </li>
        </ul>
    </nav>

</div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- ── TOPBAR ── --}}
@php
    // `is_read` belongs to whoever the notice was addressed to. The admin
    // reads over their shoulder and keeps a separate mark, so opening the bell
    // here never clears a red dot in a jobseeker's or an employer's own.
    $unreadCount   = \App\Support\AdminInbox::bellCount();
    $notifications = \App\Models\Announcement::latest()->take(5)->get();
@endphp

<div class="topbar">
    <div class="d-flex align-items-center gap-2">
        <button class="hamburger" onclick="toggleSidebar()" type="button">
            <i class="ph ph-list"></i>
        </button>
        {{-- Not one admin page sets a page title, so the fallback printed
             "Dashboard" across Manage Users, Job Activities, Registrations and
             Reports alike. The office name stands here instead; each page
             carries its own heading in its content. --}}
        <div class="topbar-brand">
            <div class="topbar-brand-title">PUBLIC EMPLOYMENT SERVICE OFFICE</div>
            <div class="topbar-brand-sub">A Web-based Job Management System</div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">

        {{-- Notification Bell --}}
        <div class="dropdown">
            <button class="icon-btn" data-bs-toggle="dropdown" aria-expanded="false"
                    title="Notifications" onclick="markAllReadOnOpen()">
                <i class="ph ph-bell"></i>
                @if($unreadCount > 0)
                    <span class="notif-badge" id="notifBadge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </button>
            <ul class="dropdown-menu dropdown-menu-end peso-dropdown notif-dropdown">
                <li class="notif-head d-flex justify-content-between align-items-center">
                    <span>Notifications</span>
                    <div class="d-flex gap-2" style="font-size:11px;font-weight:600;">
                        @if($unreadCount > 0)
                            <a href="#" style="color:var(--g-700);"
                               onclick="event.preventDefault(); document.getElementById('mark-all-read').submit();">
                                Mark all as read
                            </a>
                            <form id="mark-all-read" action="{{ route('admin.notifications.markAllRead') }}" method="POST" style="display:none;">
                                @csrf
                            </form>
                            <span style="color:var(--n-300);">|</span>
                        @endif
                        @if($notifications->count() > 0)
                            <a href="#" style="color:var(--danger);"
                               onclick="event.preventDefault(); document.getElementById('clear-all-notif').submit();">
                                Clear all
                            </a>
                            <form id="clear-all-notif" action="{{ route('admin.notifications.clearAll') }}" method="POST" style="display:none;">
                                @csrf
                            </form>
                        @endif
                    </div>
                </li>
                @forelse($notifications as $notif)
                <li>
                    <a href="{{ $notif->reference_type === 'registration' ? route('admin.registration.view', $notif->reference_id) : route('admin.dashboard') }}"
                       class="notif-item {{ !$notif->is_read ? 'unread' : '' }}"
                       onclick="markRead({{ $notif->announcements_id }})">
                        <div style="font-weight:{{ !$notif->is_read ? '600' : '500' }};color:var(--n-900);">
                            <i class="ph-fill ph-user-list me-1" style="color:var(--g-700);"></i>{{ $notif->title }}
                        </div>
                        <div style="color:var(--n-500);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $notif->message }}</div>
                        <div class="notif-time">{{ $notif->created_at->diffForHumans() }}</div>
                    </a>
                </li>
                @empty
                <li class="peso-empty" style="padding:32px 16px;">
                    <i class="ph ph-bell-slash"></i>
                    <div class="peso-empty-text">No notifications yet</div>
                </li>
                @endforelse
                <li>
                    <a href="{{ route('admin.notifications.index') }}"
                       class="d-block text-center py-2"
                       style="font-size:12px;font-weight:600;color:var(--g-700);border-top:1px solid var(--n-200);">
                        View More <i class="ph ph-arrow-right"></i>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Account menu --}}
        <div class="dropdown">
            <button class="btn-peso-ghost dropdown-toggle" data-bs-toggle="dropdown"
                    aria-expanded="false" title="My Profile"
                    style="padding:6px 8px;font-weight:500;color:var(--n-700);">
                @if(Auth::user()->profile_photo)
                    <img src="{{ asset('storage/'.Auth::user()->profile_photo) }}" alt=""
                         style="width:24px;height:24px;object-fit:cover;border-radius:50%;">
                @else
                    <i class="ph ph-user-circle" style="font-size:20px;"></i>
                @endif
                <span class="d-none d-sm-inline" style="font-size:13px;">{{ Auth::user()->name ?? 'Admin' }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end peso-dropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('admin.profile') }}">
                        <i class="ph ph-user-circle"></i> My Profile
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item is-danger w-100">
                            <i class="ph ph-sign-out"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

{{-- ── MAIN CONTENT ── --}}
<div class="main-shell">
    <div class="content-inner">
        @yield('content')
    </div>

    <div class="peso-footer">
        © {{ date('Y') }} Southern de Oro Philippines College, Bachelor of Science in Information Technology Researchers — All rights reserved
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
        confirmButtonColor: '#28812F',
        confirmButtonText: 'OK',
    });
@endif

@if($errors->any())
    Swal.fire({
        title: 'Oops!',
        text: @json($errors->first()),
        icon: 'error',
        confirmButtonColor: '#C4271B',
        confirmButtonText: 'OK',
    });
@endif
</script>
</body>
</html>
