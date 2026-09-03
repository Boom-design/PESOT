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

    <style>
        /* Flatpickr: dates already booked for this employer. */
        .flatpickr-day.fp-date-booked {
            background: var(--danger-bg) !important;
            color: var(--danger) !important;
            text-decoration: line-through;
            cursor: not-allowed !important;
            position: relative;
        }
        .flatpickr-day.fp-date-booked:hover { background: var(--danger-bg) !important; }

        /* Holidays: PESO is closed, so these are never selectable. Kept visually
           distinct from a fully-booked date — a booked date can be solved by
           changing the venue, a holiday cannot. */
        .flatpickr-day.fp-date-holiday,
        .flatpickr-day.fp-date-holiday:hover {
            background: var(--warn-bg) !important;
            color: var(--warn) !important;
            cursor: not-allowed !important;
        }
    </style>
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
                        <i class="ph ph-buildings"></i>
                    @endif
                </div>
                <div class="brand-user-text">
                    <div class="u-name">{{ optional(Auth::user()->activeCompany())->company_name ?? Auth::user()->name ?? 'Company' }}</div>
                    <div class="u-role">Employer</div>
                </div>
            </div>
        </div>

        @php
            // Ang kompanya nga gitrabahoan karon, dili ang una nga nakit-an sa
            // database: usa ka HR mahimong maghawid ug duha, ug ang requirements
            // nga gipangita sa sidebar iya sa gitan-aw niya karon.
            $sidebarEmployerNsrp = Auth::user()->activeCompany();
            $sidebarHasRequirement = $sidebarEmployerNsrp
                ? \App\Models\EmployerRequirement::where('user_id', $sidebarEmployerNsrp->employer_nsrp_registrations_id)->exists()
                : false;
            $sidebarReqApproved = $sidebarEmployerNsrp
                ? \App\Models\EmployerRequirement::where('user_id', $sidebarEmployerNsrp->employer_nsrp_registrations_id)->where('status', 'approved')->exists()
                : false;

            // Red dot per nav item — see App\Support\NavAlerts for what counts.
            $navAlerts = \App\Support\NavAlerts::forCompany(
                $sidebarEmployerNsrp?->employer_nsrp_registrations_id
            );
        @endphp

        <nav class="sidebar-nav">
            <div class="nav-section">Main</div>
            <ul>
                <li>
                    @php $isDashboard = request()->routeIs('company.dashboard'); @endphp
                    <a href="{{ route('company.dashboard') }}" class="nav-link {{ $isDashboard ? 'active' : '' }}">
                        <i class="{{ $isDashboard ? 'ph-fill' : 'ph' }} ph-squares-four"></i> Dashboard
                    </a>
                </li>
                <li>
                    {{-- Ang "Post a Job" nga buton naa na sa Active Job Postings —
                         didto man siya makita human ma-post, mao nga didto pud
                         siya sugdan. Usa na lang ka lugar nga adtoan sa employer. --}}
                    @php $isSchedule = request()->routeIs('company.jobseekers*') || request()->routeIs('company.jobs.qualified'); @endphp
                    <a href="{{ route('company.jobseekers') }}"
                       class="nav-link {{ $isSchedule ? 'active' : '' }} {{ !$sidebarReqApproved ? 'disabled' : '' }}"
                       @if(!$sidebarReqApproved) onclick="return false" title="Requirements must be approved first" @endif>
                        <i class="{{ $isSchedule ? 'ph-fill' : 'ph' }} ph-briefcase"></i> Active Job Postings
                        @include('partials.nav-dot', ['navKey' => 'active_job_vacancy', 'navLocked' => !$sidebarReqApproved])
                        @if(!$sidebarReqApproved)<i class="ph ph-lock-simple ms-auto" style="font-size:13px;width:auto;"></i>@endif
                    </a>
                </li>
                {{-- Walay "Removed Postings" nga item diri. Wala nay gi-approve nga
                     posting, mao nga ang bugtong mosulod didto kay ang gitangtang
                     sa staff — bihira, ug ang rason anaa na sa notification mismo.
                     Ang route company.jobs buhi gihapon aron dili mabuak ang daan
                     nga link. --}}
            </ul>

            <div class="nav-section">Manage</div>
            <ul>
                <li>
                    @php $isReq = request()->routeIs('company.requirements*'); @endphp
                    <a href="{{ route('company.requirements') }}" class="nav-link {{ $isReq ? 'active' : '' }}">
                        <i class="{{ $isReq ? 'ph-fill' : 'ph' }} ph-upload-simple"></i> Requirements
                        @if(!$sidebarHasRequirement)
                            <span class="nav-flag is-required">Required</span>
                        @else
                            @include('partials.nav-dot', ['navKey' => 'requirements'])
                        @endif
                    </a>
                </li>
                {{-- Walay Job Fair nga item diri: usa na siya ka tab sa Active
                     Job Postings (PESO interview, 2026-08-12). Ang route
                     company.jobfair buhi gihapon ug mo-redirect didto, aron
                     dili mabuak ang daan nga link ug ang notification. --}}
                <li>
                    @php $isReports = request()->routeIs('company.reports*'); @endphp
                    <a href="{{ route('company.reports') }}"
                       class="nav-link {{ $isReports ? 'active' : '' }} {{ !$sidebarReqApproved ? 'disabled' : '' }}"
                       @if(!$sidebarReqApproved) onclick="return false" title="Requirements must be approved first" @endif>
                        <i class="{{ $isReports ? 'ph-fill' : 'ph' }} ph-chart-bar"></i> Reports
                        @if(!$sidebarReqApproved)<i class="ph ph-lock-simple ms-auto" style="font-size:13px;width:auto;"></i>@endif
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            {{-- ── ANG KOMPANYA NGA GITRABAHOAN ──
                 PESO IT, 2026-08-26: usa ka HR mahimong maghawid ug duha ka
                 kompanya sa usa ka email. Ang gipili dinhi mao ang tag-iya sa
                 requirements, sa posting, sa in-house ug sa bell hangtod nga
                 mo-ilis siya pag-usab. Ang account nga usa ra ug kompanya
                 wala gyuy makita nga kalainan. --}}
            @php
                $myCompanies = Auth::user()->employerCompanies;
                $myActive    = Auth::user()->activeCompany();
            @endphp
            @if($myCompanies->count() > 1)
            <div class="px-2 pb-2">
                <div style="font-size:10px;letter-spacing:0.06em;text-transform:uppercase;color:rgba(255,255,255,0.6);margin-bottom:6px;padding-left:6px;">
                    Working on
                </div>
                @foreach($myCompanies as $c)
                    @if($myActive && $c->employer_nsrp_registrations_id === $myActive->employer_nsrp_registrations_id)
                        <div class="d-flex align-items-center gap-2 px-2 py-2 mb-1"
                             style="background:rgba(255,255,255,0.16);border-radius:8px;">
                            <i class="ph-fill ph-check-circle" style="font-size:14px;color:#fff;flex-shrink:0;"></i>
                            <span style="font-size:12px;color:#fff;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $c->company_name }}
                            </span>
                        </div>
                    @else
                        <form method="POST" action="{{ route('company.companies.switch', $c->employer_nsrp_registrations_id) }}" class="mb-1">
                            @csrf
                            <button type="submit" class="d-flex align-items-center gap-2 px-2 py-2 w-100"
                                    style="background:transparent;border:none;border-radius:8px;text-align:left;">
                                <i class="ph ph-buildings" style="font-size:14px;color:rgba(255,255,255,0.7);flex-shrink:0;"></i>
                                <span style="font-size:12px;color:rgba(255,255,255,0.85);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $c->company_name }}
                                </span>
                            </button>
                        </form>
                    @endif
                @endforeach
                <a href="{{ route('company.companies.add') }}"
                   class="d-flex align-items-center gap-2 px-2 py-2 text-decoration-none">
                    <i class="ph ph-plus-circle" style="font-size:14px;color:rgba(255,255,255,0.7);flex-shrink:0;"></i>
                    <span style="font-size:12px;color:rgba(255,255,255,0.85);">Add another company</span>
                </a>
            </div>
            @else
            <div class="px-2 pb-2">
                <a href="{{ route('company.companies.add') }}"
                   class="d-flex align-items-center gap-2 px-2 py-2 text-decoration-none">
                    <i class="ph ph-plus-circle" style="font-size:14px;color:rgba(255,255,255,0.7);flex-shrink:0;"></i>
                    <span style="font-size:12px;color:rgba(255,255,255,0.85);">Add another company</span>
                </a>
            </div>
            @endif
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    {{-- ── COMPLY REQUIREMENTS MODAL ── --}}
    <div class="modal fade" id="complyRequirementsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="ph-fill ph-warning me-2" style="color:var(--warn-solid);"></i>Requirements Needed
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p style="font-size:13px;color:var(--n-900);font-weight:600;margin-bottom:12px;">
                        You must comply with these 6 requirements before requesting a job vacancy posting:
                    </p>
                    <ul style="font-size:13px;color:var(--n-500);line-height:1.9;padding-left:20px;margin-bottom:0;">
                        <li>CDO Business Permit 2026</li>
                        <li>SEC / DTI</li>
                        <li>Company Profile</li>
                        <li>Filled-up NSRP Establishment Form</li>
                        <li>Certificate of No Pending Case</li>
                        <li>Vacancy Posting</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-peso-outline btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <a href="{{ route('company.requirements') }}" class="btn-peso btn-sm">
                        <i class="ph ph-upload-simple"></i> Go to Requirements
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TOPBAR ── --}}
    <div class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="hamburger" onclick="toggleSidebar()" type="button">
                <i class="ph ph-list"></i>
            </button>
            {{-- The page name fell back to "Dashboard" on every screen, so it
                 said nothing. The office name stands here instead; each page
                 carries its own heading in its content. --}}
            <div class="topbar-brand">
                <div class="topbar-brand-title">PUBLIC EMPLOYMENT SERVICE OFFICE</div>
                <div class="topbar-brand-sub">A Web-based Job Management System</div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">

            {{-- Notification bell --}}
            <div class="dropdown">
                <a class="icon-btn" data-bs-toggle="dropdown" href="#" id="companyNotifBellBtn">
                    <i class="ph ph-bell"></i>
                    @php
                        $employerNsrp = Auth::user()->activeCompany();
                        $unreadCount = $employerNsrp
                            ? \App\Models\Announcement::where('employer_id', $employerNsrp->employer_nsrp_registrations_id)->where('is_read', false)->count()
                            : 0;
                    @endphp
                    <span id="companyNotifBadge" class="notif-badge" style="{{ $unreadCount > 0 ? '' : 'display:none;' }}">{{ $unreadCount }}</span>
                </a>
                {{-- notif-dropdown-pinned: ang ulohan ug ang "View More"
                     magpabilin nga makita; ang listahan ra ang mo-scroll.
                     Kung ang tibuok dropdown ang mo-scroll, ang View More
                     matago sa ubos ug kinahanglan pa nga pangitaon. --}}
                <ul class="dropdown-menu dropdown-menu-end peso-dropdown notif-dropdown notif-dropdown-pinned">
                    <li class="notif-head d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <form id="companyNotifClearAllForm" action="{{ route('company.notifications.clearAll') }}" method="POST" style="display:none;">
                            @csrf
                            <button type="submit" style="font-size:11px;color:var(--danger);background:none;border:none;cursor:pointer;font-weight:600;">
                                Clear All
                            </button>
                        </form>
                    </li>
                    @php
                        $notifications = $employerNsrp
                            ? \App\Models\Announcement::where('employer_id', $employerNsrp->employer_nsrp_registrations_id)->orderBy('created_at', 'desc')->take(5)->get()
                            : collect();

                        $companyNotifTargetUrl = function ($notif) {
                            // ── "New Job Applicant" kay dapat mo-adto sa Qualified Applicants page sa maong job, dili sa lista sa Active Job Postings ──
                            if ($notif->type === 'new_applicant' && $notif->reference_id) {
                                return route('company.jobs.qualified', $notif->reference_id);
                            }
                            // ── Ang sulat sa inactivity moabli sa buhat nga makapahunong niini. ──
                            //
                            // Ang pagpost ug bag-ong bakante mao ang tubag nga
                            // giila sa sweep, mao nga didto siya modala. Kaniadto
                            // walay padulngan kini nga notice: ang employer
                            // makabasa nga ang iyang account hapit na ma-inactive
                            // ug walay ma-click.
                            if ($notif->reference_type === 'employer_inactivity') {
                                return route('company.jobs.create');
                            }

                            return match($notif->reference_type) {
                                'job'                     => route('company.jobseekers'),
                                'employer_requirement'    => route('company.requirements'),
                                'job_archive'             => route('company.reports'),
                                'job_fair'                => route('company.jobfair'),
                                default                   => null,
                            };
                        };
                    @endphp
                    <div id="companyNotifList" class="notif-scroll">
                    @forelse($notifications as $notif)
                        <li>
                            <a class="notif-item {{ !$notif->is_read ? 'unread' : '' }}"
                               href="{{ $companyNotifTargetUrl($notif) ?? '#' }}"
                               onclick="markRead({{ $notif->announcements_id }}, {{ $companyNotifTargetUrl($notif) ? 'true' : 'false' }})">
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
                    </div>
                    <li class="notif-foot">
                        <a href="{{ route('company.notifications.index') }}"
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
                        style="padding:6px 8px;font-weight:500;color:var(--n-700);">
                    @if(Auth::user()->profile_photo)
                        <img src="{{ asset('storage/'.Auth::user()->profile_photo) }}" alt=""
                             style="width:24px;height:24px;object-fit:cover;border-radius:50%;">
                    @else
                        <i class="ph ph-user-circle" style="font-size:20px;"></i>
                    @endif
                    <span class="d-none d-sm-inline" style="font-size:13px;">{{ optional(Auth::user()->activeCompany())->company_name ?? Auth::user()->name ?? 'Company' }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end peso-dropdown">
                    <li>
                        <a class="dropdown-item" href="{{ route('company.profile') }}">
                            <i class="ph ph-user-circle"></i> My Profile
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
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
        </div>
    </div>

    {{-- ── MAIN CONTENT ── --}}
    <div class="main-shell">
        <div class="content-inner">
            {{-- Expired Business Permit: naa sa kada page, dili sa dashboard ra,
                 kay ang employer mahimong mo-diretso sa Active Job Postings ug
                 didto na lang mahibalo nga naka-block diay siya. --}}
            @if(Auth::check() && Auth::user()->status === 'restricted' && !request()->routeIs('company.requirements*'))
                <div class="alert alert-danger d-flex align-items-start gap-2 rounded-3 border-0 mb-3"
                     style="background:#fdecea;color:#8a1c13;font-size:13px;">
                    <i class="ph-fill ph-warning-circle" style="font-size:18px;flex-shrink:0;"></i>
                    <div class="flex-grow-1">
                        <strong>Your CDO Business Permit has expired.</strong>
                        Job posting and job fair invitations are paused. Upload a renewed copy —
                        your account resumes as soon as PESO staff approve it.
                    </div>
                    <a href="{{ route('company.requirements') }}" class="btn-peso btn-sm flex-shrink-0">
                        <i class="ph ph-upload-simple"></i> Renew now
                    </a>
                </div>
            @endif

            @yield('content')
        </div>

        <div class="peso-footer">
            © {{ date('Y') }} Southern de Oro Philippines College, Bachelor of Science in Information Technology Researchers — All rights reserved
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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

        function markRead(id, hasTarget) {
            fetch(`/company/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
            if (!hasTarget) {
                event.preventDefault();
                refreshCompanyNotifications();
            }
            // kung naay target URL, tugutan ang link mo-navigate diretso
        }

        // ── Live notification bell — dili na kinahanglan i-reload ang page para makita ang bag-ong notification ──
        function renderCompanyNotifItem(n) {
            const href = n.url || '#';
            return `<li>
                <a class="notif-item ${!n.is_read ? 'unread' : ''}" href="${href}" onclick="markRead(${n.id}, ${n.url ? 'true' : 'false'})">
                    <div style="font-weight:${!n.is_read ? '600' : '500'};color:var(--n-900);">${n.title}</div>
                    <div style="color:var(--n-500);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${n.message}</div>
                    <div class="notif-time">${n.time_ago}</div>
                </a>
            </li>`;
        }

        function refreshCompanyNotifications() {
            fetch(`{{ route('company.notifications.fetch') }}`)
                .then(res => res.json())
                .then(data => {
                    // Samtang abli ang dropdown, gibasa na niya silang tanan —
                    // ang ihap gikan sa server mahimong una pa sa pagmarka,
                    // ug kung tugutan, mobalik ang numero nga bag-o lang
                    // nawala.
                    const bellOpen = document.querySelector('#companyNotifBellBtn')
                        ?.parentElement?.querySelector('.dropdown-menu.show');

                    const badge = document.getElementById('companyNotifBadge');
                    if (data.unread_count > 0 && !bellOpen) {
                        badge.textContent = data.unread_count;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }

                    const list = document.getElementById('companyNotifList');
                    const clearForm = document.getElementById('companyNotifClearAllForm');
                    if (data.notifications.length === 0) {
                        list.innerHTML = `<li class="peso-empty" style="padding:32px 16px;">
                            <i class="ph ph-bell-slash"></i>
                            <div class="peso-empty-text">No notifications yet</div>
                        </li>`;
                        clearForm.style.display = 'none';
                    } else {
                        list.innerHTML = data.notifications.map(renderCompanyNotifItem).join('');
                        clearForm.style.display = '';

                        // Samang rason sa badge sa taas: abli ang dropdown, so
                        // basa na silang tanan bisan pa'g ang tubag sa server
                        // wala pa nakaabot sa pagmarka.
                        if (bellOpen) {
                            list.querySelectorAll('.notif-item.unread')
                                .forEach(el => el.classList.remove('unread'));
                        }
                    }
                })
                .catch(() => {});
        }

        // ── Pag-abli sa bell, nakita na niya silang tanan — mawala dayon ang
        // ── numero, ug ang tanang laray dili na "unread".
        // ──
        // ── Ang badge gitago sa wala pa mobalik ang server: ang mo-abli mao
        // ── ang mobasa, ug ang paghulat sa network mo-himo sa numero nga
        // ── daw wala mausab. Kung mapakyas ang tawag, ang sunod nga refresh
        // ── (kada 20 segundos) mao ang mopakita sa tinuod nga ihap. ──
        function markAllCompanyNotificationsRead() {
            const badge = document.getElementById('companyNotifBadge');
            if (badge) {
                if (badge.style.display === 'none') return;   // wala nay bag-o
                badge.style.display = 'none';
            }

            document.querySelectorAll('#companyNotifList .notif-item.unread')
                .forEach(el => el.classList.remove('unread'));

            fetch(`{{ route('company.notifications.markAllRead') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).catch(() => {});
        }

        const companyNotifBell = document.getElementById('companyNotifBellBtn');
        companyNotifBell?.addEventListener('click', refreshCompanyNotifications);

        // Ang 'shown.bs.dropdown' ang gigamit, dili ang click: sa ingon niini
        // ang pagtago sa badge mahitabo human na maabli ang dropdown, ug dili
        // sa pagsira niini.
        companyNotifBell?.parentElement?.addEventListener('shown.bs.dropdown', markAllCompanyNotificationsRead);

        setInterval(refreshCompanyNotifications, 20000);

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
    @yield('scripts')
    {{-- Ang @yield ug ang @stack pareho nga kinahanglan: ang page mogamit ug
         @section para sa kaugalingon niyang script, apan ang partial (sama sa
         activity-calendar) mo-@push. Kung ang @stack wala, ang gi-push hilom
         nga mawala ug ang partial mogawas nga patay. --}}
    @stack('scripts')
</body>
</html>
