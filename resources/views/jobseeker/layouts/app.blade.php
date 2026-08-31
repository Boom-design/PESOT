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

    {{-- flatpickr — the dashboard draws the same activity calendar the staff
         dashboards use, and that partial expects flatpickr to be loaded. --}}
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
    <div class="sidebar-brand">
        <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo">
        <div class="brand-title">PUBLIC EMPLOYMENT SERVICE OFFICE</div>
        <div class="brand-sub">A Web-based Job Management System</div>
    </div>

    @php
        $jobseekerRegistration = \App\Models\JobseekerRegistration::where('user_id', Auth::id())->first();
        $hasNsrp = $jobseekerRegistration !== null;

        // ── Job Fair Attendance Confirmation — naa bay unresolved (na-notify na pero wala pa nag-respond) ──
        $pendingAttendanceConfirmation = $jobseekerRegistration
            ? \App\Models\JobFairRegistration::with('jobFair')
                ->where('user_id', $jobseekerRegistration->jobseeker_registrations_id)
                ->whereNull('is_attended')
                ->whereNotNull('attendance_notified_at')
                ->first()
            : null;

        // Red dot per nav item — see App\Support\NavAlerts for what counts.
        $navAlerts = \App\Support\NavAlerts::forJobseeker(
            $jobseekerRegistration?->jobseeker_registrations_id
        );
    @endphp

    <nav class="sidebar-nav">
        <div class="nav-section">Main</div>
        <ul>
            <li>
                @php $isDash = request()->routeIs('jobseeker.dashboard'); @endphp
                <a href="{{ route('jobseeker.dashboard') }}" class="nav-link {{ $isDash ? 'active' : '' }}">
                    <i class="{{ $isDash ? 'ph-fill' : 'ph' }} ph-squares-four"></i> Dashboard
                </a>
            </li>
            <li>
                @php $isNsrp = request()->routeIs('jobseeker.nsrp*'); @endphp
                <a href="{{ route('jobseeker.nsrp') }}" class="nav-link {{ $isNsrp ? 'active' : '' }}">
                    <i class="{{ $isNsrp ? 'ph-fill' : 'ph' }} ph-clipboard-text"></i> My Profile
                    @if(!$hasNsrp)
                        <span class="nav-flag is-required">Required</span>
                    @endif
                </a>
            </li>
            <li>
                @php $isJobs = request()->routeIs('jobseeker.jobs*'); @endphp
                <a href="{{ route('jobseeker.jobs') }}" class="nav-link {{ $isJobs ? 'active' : '' }}">
                    <i class="{{ $isJobs ? 'ph-fill' : 'ph' }} ph-briefcase"></i> Job Vacancies
                    @include('partials.nav-dot', ['navKey' => 'job_vacancies'])
                </a>
            </li>
        </ul>

        @if($hasNsrp)
        <div class="nav-section">My Activity</div>
        <ul>
            <li>
                @php $isSched = request()->routeIs('jobseeker.schedules*'); @endphp
                <a href="{{ route('jobseeker.schedules') }}" class="nav-link {{ $isSched ? 'active' : '' }}">
                    <i class="{{ $isSched ? 'ph-fill' : 'ph' }} ph-calendar-check"></i> PESO Events
                    @include('partials.nav-dot', ['navKey' => 'schedules'])
                </a>
            </li>
            <li>
                @php $isHist = request()->routeIs('jobseeker.history*'); @endphp
                <a href="{{ route('jobseeker.history') }}" class="nav-link {{ $isHist ? 'active' : '' }}">
                    <i class="{{ $isHist ? 'ph-fill' : 'ph' }} ph-clock-counter-clockwise"></i> History
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
                <div class="u-name">{{ Auth::user()->first_name ?? Auth::user()->name ?? 'Jobseeker' }}</div>
                <div class="u-role">Jobseeker</div>
            </div>
        </div>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- ── TOPBAR ── --}}
<div class="topbar">
    <div class="d-flex align-items-center gap-2">
        <button class="hamburger" onclick="toggleSidebar()" type="button">
            <i class="ph ph-list"></i>
        </button>
        <div class="page-title">@yield('page-title', 'Dashboard')</div>
    </div>

    <div class="d-flex align-items-center gap-2">

        {{-- User Dropdown — permi mopakita para maka-logout, bisan wala pa NSRP --}}
        @unless($hasNsrp)
        <div class="dropdown">
            <button class="btn-peso-ghost dropdown-toggle" data-bs-toggle="dropdown"
                    style="padding:6px 8px;font-weight:500;color:var(--n-700);">
                @if(Auth::user()->profile_photo)
                    <img src="{{ asset('storage/'.Auth::user()->profile_photo) }}" alt=""
                         style="width:24px;height:24px;object-fit:cover;border-radius:50%;">
                @else
                    <i class="ph ph-user-circle" style="font-size:20px;"></i>
                @endif
                <span class="d-none d-sm-inline" style="font-size:13px;">{{ Auth::user()->first_name ?? Auth::user()->name ?? 'Jobseeker' }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end peso-dropdown">
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item is-danger w-100">
                            <i class="ph ph-sign-out"></i> Logout
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
            <a class="icon-btn" data-bs-toggle="dropdown" href="#">
                <i class="ph ph-bell"></i>
                @php
                    $unreadCount = $jobseekerRegistration
                        ? \App\Models\Announcement::where('jobseeker_id', $jobseekerRegistration->jobseeker_registrations_id)->where('is_read', false)->count()
                        : 0;
                @endphp
                @if($unreadCount > 0)
                    <span class="notif-badge">{{ $unreadCount }}</span>
                @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end peso-dropdown notif-dropdown">
                <li class="notif-head">Notifications</li>
                @php
                    $notifications = $jobseekerRegistration
                        ? \App\Models\Announcement::where('jobseeker_id', $jobseekerRegistration->jobseeker_registrations_id)->orderBy('created_at', 'desc')->take(5)->get()
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
                        <a class="notif-item {{ !$notif->is_read ? 'unread' : '' }}"
                           href="{{ $notifTargetUrl($notif) }}"
                           onclick="markRead({{ $notif->announcements_id }})">
                            <div style="font-weight:{{ !$notif->is_read ? '600' : '500' }};color:var(--n-900);">
                                {{ $notif->title }}
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
                    <a href="{{ route('jobseeker.notifications.index') }}"
                       class="d-block text-center py-2"
                       style="font-size:12px;font-weight:600;color:var(--g-700);border-top:1px solid var(--n-200);">
                        View More <i class="ph ph-arrow-right"></i>
                    </a>
                </li>
            </ul>
        </div>

        {{-- User Dropdown --}}
        <div class="dropdown">
            <button class="btn-peso-ghost dropdown-toggle" data-bs-toggle="dropdown"
                    style="padding:6px 8px;font-weight:500;color:var(--n-700);">
                @if(Auth::user()->profile_photo)
                    <img src="{{ asset('storage/'.Auth::user()->profile_photo) }}" alt=""
                         style="width:24px;height:24px;object-fit:cover;border-radius:50%;">
                @else
                    <i class="ph ph-user-circle" style="font-size:20px;"></i>
                @endif
                <span class="d-none d-sm-inline" style="font-size:13px;">{{ Auth::user()->first_name ?? Auth::user()->name ?? 'Jobseeker' }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end peso-dropdown">
                @if($hasNsrp)
                <li>
                    <a class="dropdown-item" href="{{ route('jobseeker.profile') }}">
                        <i class="ph ph-user-circle"></i> My Account Profile
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                @endif
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item is-danger w-100">
                            <i class="ph ph-sign-out"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
        @endif {{-- end @if($hasNsrp) for notification+settings --}}
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
            confirmButtonColor: '#28812F',
            confirmButtonText: 'OK',
        });
    @endif

    @if(session('info'))
        Swal.fire({
            title: 'Matching Jobs Found!',
            text: @json(session('info')),
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#28812F',
            cancelButtonColor: '#5B655C',
            confirmButtonText: '<i class="ph ph-paper-plane-tilt me-1"></i> Apply Now',
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
            confirmButtonColor: '#C4271B',
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
            confirmButtonColor: '#28812F',
            cancelButtonColor: '#C4271B',
            confirmButtonText: 'Yes, I attended',
            cancelButtonText: 'No, I did not attend',
            allowOutsideClick: false,
        }).then((result) => {
            const response = result.isConfirmed ? 'yes' : 'no';
            fetch('{{ url("/jobseeker/jobfair-registrations") }}/{{ $pendingAttendanceConfirmation->job_fair_registrations_id }}/attendance-response', {
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@yield('scripts')
@stack('scripts')
</body>
</html>
