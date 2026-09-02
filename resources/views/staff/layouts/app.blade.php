<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

@php
    $staffRole = optional(Auth::user()->staff)->staff_role ?? 'staff';
    $roleLabel = ['job_fair' => 'JOB FAIR', 'job_vacancy' => 'JOB VACANCY', 'lra' => 'LRA', 'sra' => 'SRA'][$staffRole] ?? strtoupper($staffRole);

    // Active-state tests, named once so the markup below stays readable.
    $isDash      = request()->routeIs('staff.dashboard');
    $isJobseeker = request()->routeIs('staff.registrations*') || request()->routeIs('staff.nsrp*');
    $isEmployers = request()->is('staff/employers*');
    // Ang Job Fair nga tab sa LRA nagpuyo sa staff/participants. Wala siya
    // dinhi, mao nga ang sidebar walay dan-ag samtang naa ka niini nga tab —
    // ang tab row nagpakita nga sulod ka sa Manage Job Activities ug ang
    // sidebar nag-ingon nga wala.
    $isActivity  = request()->is('staff/inhouse*') || request()->is('staff/jobs') || request()->is('staff/jobs/*') || request()->is('staff/jobfair*') || request()->is('staff/participants*');
    // Ang Job Fair nga tab sa Manage Job Activities naa sa staff/inhouse/jobfair,
    // dili sa staff/jobfair — mao nga kining sala wala siya nadakpan ug walay
    // nagdan-ag sa sidebar samtang naa ka sa maong tab.
    $isJobsOnly  = request()->is('staff/jobs') || request()->is('staff/jobs/*')
                   || request()->is('staff/jobfair*') || request()->routeIs('staff.inhouse.jobfair');
    $isReports   = request()->is('staff/reports*');
    $isEvents    = request()->routeIs('staff.jobfair.events*');
    $isPostings  = request()->routeIs('staff.jobfair.postings*');
    $isRepEmp    = request()->routeIs('staff.reports.employers*');

    // Red dot per nav item — see App\Support\NavAlerts for what counts.
    $navAlerts = \App\Support\NavAlerts::forStaff(Auth::user()->staff);
@endphp

{{-- ── SIDEBAR ── --}}
<div class="sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo">
        <div class="brand-title">PUBLIC EMPLOYMENT SERVICE OFFICE</div>
        <div class="brand-sub">A Web-based Job Management System</div>
    </div>

    <nav class="sidebar-nav">

        {{-- SRA / LRA share the same menu --}}
        @if($staffRole === 'sra' || $staffRole === 'lra')
            <div class="nav-section">Main</div>
            <ul>
                <li>
                    <a href="{{ route('staff.dashboard') }}" class="nav-link {{ $isDash ? 'active' : '' }}">
                        <i class="{{ $isDash ? 'ph-fill' : 'ph' }} ph-gauge"></i> Dashboard
                    </a>
                </li>
                {{-- One entry, and it opens on the NSRP Registration list. The
                     two tabs are gone: encoding a walk-in is one action taken
                     from that list, so it is a button there. --}}
                <li>
                    <a href="{{ route('staff.registrations') }}" class="nav-link {{ $isJobseeker ? 'active' : '' }}">
                        <i class="{{ $isJobseeker ? 'ph-fill' : 'ph' }} ph-user-list"></i> Jobseekers
                    </a>
                </li>
            </ul>

            <div class="nav-section">Manage</div>
            <ul>
                <li>
                    <a href="{{ route('staff.employers') }}" class="nav-link {{ $isEmployers ? 'active' : '' }}">
                        <i class="{{ $isEmployers ? 'ph-fill' : 'ph' }} ph-buildings"></i> Employers
                        @include('partials.nav-dot', ['navKey' => 'employers'])
                    </a>
                </li>
                <li>
                    <a href="{{ route('staff.inhouse') }}" class="nav-link {{ $isActivity ? 'active' : '' }}">
                        <i class="{{ $isActivity ? 'ph-fill' : 'ph' }} ph-briefcase"></i> Manage Job Activities
                        @include('partials.nav-dot', ['navKey' => 'job_activities'])
                    </a>
                </li>
                {{-- Ang Employer Report usa ka tab sulod sa Reports, dili
                     kaugalingon nga entry. Taas na ang sidebar sa LRA, ug
                     report man gihapon siya — didto siya pangitaon. --}}
                <li>
                    <a href="{{ route('staff.reports') }}" class="nav-link {{ $isReports ? 'active' : '' }}">
                        <i class="{{ $isReports ? 'ph-fill' : 'ph' }} ph-chart-bar"></i> Reports
                    </a>
                </li>
            </ul>

        {{-- JOB FAIR --}}
        @elseif($staffRole === 'job_fair')
            <div class="nav-section">Main</div>
            <ul>
                <li>
                    <a href="{{ route('staff.dashboard') }}" class="nav-link {{ $isDash ? 'active' : '' }}">
                        <i class="{{ $isDash ? 'ph-fill' : 'ph' }} ph-gauge"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('staff.jobfair.events') }}" class="nav-link {{ $isEvents ? 'active' : '' }}">
                        <i class="{{ $isEvents ? 'ph-fill' : 'ph' }} ph-calendar-dots"></i> Job Fair Events
                    </a>
                </li>
                {{-- Walay Employers dinhi. PESO, 2026-08-26: ang Job Fair desk
                     dili mo-approve ug account, mao nga ang listahan sa employer
                     dili iyaha. Ang usa ka butang nga iya niya — ang pagdala ug
                     walk-in ngadto sa fair — naa na sa Job Postings.

                     Ang industry group mahimo gihapon usbon sa Job Vacancy ug sa
                     SRA sa ilang kaugalingong listahan, mao nga walay kapabilidad
                     nga nawala sa opisina. --}}
            </ul>

            <div class="nav-section">Manage</div>
            <ul>
                <li>
                    <a href="{{ route('staff.jobfair.postings') }}" class="nav-link {{ $isPostings ? 'active' : '' }}">
                        <i class="{{ $isPostings ? 'ph-fill' : 'ph' }} ph-briefcase"></i> Job Fair Vacancies
                        @include('partials.nav-dot', ['navKey' => 'postings'])
                    </a>
                </li>
                <li>
                    <a href="{{ route('staff.reports') }}" class="nav-link {{ request()->routeIs('staff.reports*') ? 'active' : '' }}">
                        <i class="{{ request()->routeIs('staff.reports*') ? 'ph-fill' : 'ph' }} ph-chart-bar"></i> Reports
                    </a>
                </li>
            </ul>

        {{-- JOB VACANCY --}}
        @elseif($staffRole === 'job_vacancy')
            <div class="nav-section">Main</div>
            <ul>
                <li>
                    <a href="{{ route('staff.dashboard') }}" class="nav-link {{ $isDash ? 'active' : '' }}">
                        <i class="{{ $isDash ? 'ph-fill' : 'ph' }} ph-gauge"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('staff.employers') }}" class="nav-link {{ $isEmployers ? 'active' : '' }}">
                        <i class="{{ $isEmployers ? 'ph-fill' : 'ph' }} ph-buildings"></i> Employers
                        @include('partials.nav-dot', ['navKey' => 'employers'])
                    </a>
                </li>
            </ul>

            <div class="nav-section">Manage</div>
            <ul>
                <li>
                    <a href="{{ route('staff.jobs') }}" class="nav-link {{ $isJobsOnly ? 'active' : '' }}">
                        <i class="{{ $isJobsOnly ? 'ph-fill' : 'ph' }} ph-briefcase"></i> Manage Job Activities
                        @include('partials.nav-dot', ['navKey' => 'job_activities'])
                    </a>
                </li>
                <li>
                    <a href="{{ route('staff.reports.employers') }}" class="nav-link {{ $isRepEmp ? 'active' : '' }}">
                        <i class="{{ $isRepEmp ? 'ph-fill' : 'ph' }} ph-chart-bar"></i> Reports
                    </a>
                </li>
            </ul>
        @endif
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar">
                @if(Auth::user()->profile_photo)
                    <img src="{{ asset('storage/'.Auth::user()->profile_photo) }}" alt="">
                @else
                    <i class="ph ph-user"></i>
                @endif
            </div>
            <div style="min-width:0;flex:1;">
                <div class="u-name">{{ Auth::user()->name ?? 'Staff' }}</div>
                <div class="u-role">{{ $roleLabel }} Staff</div>
            </div>
        </div>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- ── TOPBAR ── --}}
@php
    $staffRecordForNotif = \App\Models\Staff::where('user_id', Auth::id())->first();
    $staffUnreadCount = $staffRecordForNotif
        ? \App\Models\Announcement::where('staff_id', $staffRecordForNotif->staff_id)->where('is_read', false)->count()
        : 0;
    $staffNotifications = $staffRecordForNotif
        ? \App\Models\Announcement::where('staff_id', $staffRecordForNotif->staff_id)->latest()->take(5)->get()
        : collect();
@endphp

<div class="topbar">
    <div class="d-flex align-items-center gap-2">
        <button class="hamburger" onclick="toggleSidebar()" type="button">
            <i class="ph ph-list"></i>
        </button>
        <div class="page-title">@yield('page-title', 'Dashboard')</div>
    </div>

    <div class="d-flex align-items-center gap-2">

        {{-- Notification Bell --}}
        <div class="dropdown">
            <button class="icon-btn" data-bs-toggle="dropdown" aria-expanded="false"
                    title="Notifications" onclick="staffMarkAllReadOnOpen()">
                <i class="ph ph-bell"></i>
                <span class="notif-badge" id="staffNotifBadge" style="{{ $staffUnreadCount > 0 ? '' : 'display:none;' }}">{{ $staffUnreadCount > 9 ? '9+' : $staffUnreadCount }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end peso-dropdown notif-dropdown">
                <li class="notif-head d-flex justify-content-between align-items-center">
                    <span>Notifications</span>
                    @if($staffUnreadCount > 0)
                        <form id="staff-mark-all-read" action="{{ route('staff.notifications.markAllRead') }}"
                              method="POST" style="display:none;">
                            @csrf
                        </form>
                        <a href="#" style="font-size:11px;font-weight:600;color:var(--g-700);"
                           onclick="event.preventDefault(); document.getElementById('staff-mark-all-read').submit();">
                            Mark all as read
                        </a>
                    @endif
                </li>
                <div id="staffNotifList">
                @forelse($staffNotifications as $notif)
                <li>
                    {{-- Kining dropdown, ang notifications page ug ang live
                         fetch parehas nga motubag karon: Announcement::staffLinkUrl().
                         Tulo ka kopya kini kaniadto ug nagkalahi — ang usa
                         wala kabalo sa employer inactivity, mao nga ang
                         pag-click dinhi mo-abot lang sa notifications page samtang
                         ang View more mo-abot sa employer. --}}
                    <a href="{{ $notif->staffLinkUrl() }}"
                   class="notif-item {{ !$notif->is_read ? 'unread' : '' }}"
                   onclick="staffMarkRead({{ $notif->announcements_id }})">
                        <div style="font-weight:{{ !$notif->is_read ? '600' : '500' }};color:var(--n-900);">
                            <i class="ph-fill ph-bell me-1" style="color:var(--g-700);"></i>{{ $notif->title }}
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
                </div>
                <li>
                    <a href="{{ route('staff.notifications.index') }}"
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
                <span class="d-none d-sm-inline" style="font-size:13px;">{{ Auth::user()->name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end peso-dropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('staff.profile') }}">
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

function staffMarkRead(id) {
    fetch('/staff/notifications/' + id + '/read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    });
}

// ── Live notification bell — dili na kinahanglan i-reload ang page para makita ang bag-ong notification ──
function renderStaffNotifItem(n) {
    return `<li>
        <a href="${n.url}" class="notif-item ${!n.is_read ? 'unread' : ''}" onclick="staffMarkRead(${n.id})">
            <div style="font-weight:${!n.is_read ? '600' : '500'};color:var(--n-900);"><i class="ph-fill ph-bell me-1" style="color:var(--g-700);"></i>${n.title}</div>
            <div style="color:var(--n-500);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${n.message}</div>
            <div class="notif-time">${n.time_ago}</div>
        </a>
    </li>`;
}

function refreshStaffNotifications() {
    fetch(`{{ route('staff.notifications.fetch') }}`)
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('staffNotifBadge');
            if (badge) {
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }
            }

            const list = document.getElementById('staffNotifList');
            if (list) {
                list.innerHTML = data.notifications.length === 0
                    ? `<li class="peso-empty" style="padding:32px 16px;"><i class="ph ph-bell-slash"></i><div class="peso-empty-text">No notifications yet</div></li>`
                    : data.notifications.map(renderStaffNotifItem).join('');
            }
        })
        .catch(() => {});
}

function staffMarkAllReadOnOpen() {
    fetch('{{ route('staff.notifications.markAllRead') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(refreshStaffNotifications);
}

setInterval(refreshStaffNotifications, 20000);

@if(session('success'))
    Swal.fire({
        title: 'Success!',
        text: @json(session('success')),
        icon: 'success',
        confirmButtonColor: '#28812F',
        confirmButtonText: 'OK',
    });
@endif

@if(session('error'))
    Swal.fire({
        title: 'Oops!',
        text: @json(session('error')),
        icon: 'error',
        confirmButtonColor: '#C4271B',
        confirmButtonText: 'OK',
    });
@endif

@if(session('warning'))
    Swal.fire({
        title: 'Partly done',
        text: @json(session('warning')),
        icon: 'warning',
        confirmButtonColor: '#B54708',
        confirmButtonText: 'OK',
    });
@endif

@if(session('info'))
    Swal.fire({
        title: 'Note',
        text: @json(session('info')),
        icon: 'info',
        confirmButtonColor: '#175CD3',
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
