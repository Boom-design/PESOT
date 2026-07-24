<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PESO CDO — All Job Postings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --peso-green: #4dd9c0; --peso-light: #90d870; --peso-dark: #2d7a5f; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: 'Segoe UI', sans-serif; background: #0d1f18; display: flex; flex-direction: column; }

        .peso-navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 500; padding: 14px 20px; background: rgba(255,255,255,0.12); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); border-bottom: 1px solid rgba(255,255,255,0.28); }
        body { padding-top: 68px; }
        .navbar-inner { width: 100%; max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; position: relative; }
        .navbar-brand { display: flex; align-items: center; gap: 10px; }
        .navbar-brand img { width: 34px; height: 34px; object-fit: contain; }
        .navbar-brand span { color: #fff; font-weight: 700; font-size: 12.5px; line-height: 1.25; }
        .navbar-brand small { color: rgba(255,255,255,0.65); font-weight: 500; font-size: 10.5px; }
        .navbar-toggle { display: block; background: none; border: none; color: #fff; font-size: 22px; cursor: pointer; }
        .navbar-links { display: none; position: absolute; top: 100%; left: 0; right: 0; background: rgba(13,31,24,0.97); flex-direction: column; padding: 16px 20px 20px; gap: 14px; border-bottom: 1px solid rgba(255,255,255,0.12); }
        .navbar-links.open { display: flex; }
        .navbar-links a { color: rgba(255,255,255,0.85); text-decoration: none; font-size: 13.5px; font-weight: 600; }
        .navbar-links a:hover { color: #90d870; }
        .navbar-auth-btns { display: flex; gap: 10px; margin-top: 6px; }
        .btn-nav-login, .btn-nav-signup { border-radius: 8px; padding: 8px 16px; font-size: 12.5px; font-weight: 700; cursor: pointer; border: 1.5px solid transparent; text-decoration: none; display: inline-block; }
        .btn-nav-login { background: transparent; border-color: rgba(255,255,255,0.3); color: #fff; }
        .btn-nav-signup { background: linear-gradient(90deg, var(--peso-light), var(--peso-green)); color: #0f2e24; border: none; }

        @media (min-width: 900px) {
            .navbar-toggle { display: none; }
            .navbar-links { display: flex; position: static; flex-direction: row; align-items: center; background: none; border: none; padding: 0; gap: 26px; }
            .navbar-auth-btns { margin-top: 0; }
        }

        .page-wrap { flex: 1; max-width: 1200px; width: 100%; margin: 0 auto; padding: 32px 20px 56px; }
        .page-header { color: #fff; margin-bottom: 24px; }
        .page-header h1 { font-size: 22px; font-weight: 800; margin-bottom: 6px; }
        .page-header p { color: rgba(255,255,255,0.7); font-size: 13px; margin: 0; }

        .jobs-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
        @media (min-width: 768px) { .jobs-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1024px) { .jobs-grid { grid-template-columns: repeat(3, 1fr); } }

        .job-card { background: rgba(255,255,255,0.96); border-radius: 14px; padding: 16px; box-shadow: 0 6px 18px rgba(0,0,0,0.15); display: flex; flex-direction: column; gap: 8px; }
        .job-card-top { display: flex; align-items: flex-start; justify-content: space-between; }
        .job-card-type-badge { background: #eafaf0; color: var(--peso-dark); font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 20px; white-space: nowrap; }
        .job-icon { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, var(--peso-light), var(--peso-green)); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; }
        .job-title { font-size: 14.5px; font-weight: 700; color: var(--peso-dark); margin: 0; }
        .job-company { font-size: 11.5px; color: #888; margin: 0; }
        .job-meta { display: flex; flex-wrap: wrap; gap: 6px; }
        .job-badge { background: #f0f9f6; color: var(--peso-dark); font-size: 10.5px; padding: 3px 9px; border-radius: 20px; font-weight: 600; white-space: nowrap; }
        .job-badge-deadline { background: #fff8e1; color: #f9a825; font-size: 10.5px; padding: 3px 9px; border-radius: 20px; font-weight: 600; white-space: nowrap; }
        .btn-view-more { background: linear-gradient(90deg, var(--peso-light), var(--peso-green)); border: none; color: #fff; font-weight: 600; border-radius: 10px; padding: 9px 16px; font-size: 11.5px; margin-top: auto; }

        .empty-box { background: rgba(255,255,255,0.9); border-radius: 16px; padding: 48px 20px; text-align: center; }
        .empty-box i { font-size: 40px; color: #c0e8dc; }
        .empty-box h6 { color: var(--peso-dark); font-weight: 700; margin-top: 12px; }
        .empty-box p { color: #888; font-size: 13px; margin: 0; }

        .landing-pagination { margin-top: 26px; display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .landing-pagination .pagination-summary { color: #fff; font-size: 13px; font-weight: 700; }
        .landing-pagination .pagination { margin: 0; justify-content: center; gap: 4px; flex-wrap: wrap; }
        .landing-pagination .pagination .page-link { color: #fff; border-color: rgba(255,255,255,0.35); font-size: 13px; padding: 6px 12px; border-radius: 8px; background: rgba(255,255,255,0.14); min-width: 40px; display: flex; align-items: center; justify-content: center; }
        .landing-pagination .pagination .page-item.disabled .page-link { background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.6); }

        .peso-footer { text-align: center; padding: 20px; color: rgba(255,255,255,0.6); font-size: 11.5px; display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .footer-fb-link { width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,0.1); color: #fff; font-size: 16px; display: flex; align-items: center; justify-content: center; text-decoration: none; }
        .footer-fb-link:hover { background: #1877f2; }

        .job-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 999; align-items: center; justify-content: center; padding: 20px; }
        .job-modal-box { background: #fff; border-radius: 18px; padding: 24px 20px; max-width: 480px; width: 100%; position: relative; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .job-modal-box .close-btn { position: absolute; top: 14px; right: 14px; background: none; border: none; font-size: 18px; color: #888; cursor: pointer; }
    </style>
</head>
<body>

    <nav class="peso-navbar">
        <div class="navbar-inner">
            <div class="navbar-brand">
                <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo">
                <span>PUBLIC EMPLOYMENT SERVICE OFFICE<br><small>A Web-based Job Management System</small></span>
            </div>
            <button class="navbar-toggle" onclick="document.getElementById('navbarLinks').classList.toggle('open')">
                <i class="bi bi-list"></i>
            </button>
            <div class="navbar-links" id="navbarLinks">
                <a href="{{ route('landing') }}">Home</a>
                <a href="{{ route('jobs.all') }}">Job Postings</a>
                <a href="{{ route('landing') }}#aboutSection">About Us</a>
                <div class="navbar-auth-btns">
                    <a href="{{ route('login') }}" class="btn-nav-login">Login</a>
                    <a href="{{ route('landing') }}#jobsSection" class="btn-nav-signup">Sign Up</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="page-wrap">
        <div class="page-header">
            <h1>All Job Postings</h1>
            <p>{{ $jobs->total() }} open position{{ $jobs->total() === 1 ? '' : 's' }} available right now.</p>
        </div>

        @if($jobs->isEmpty())
            <div class="empty-box">
                <i class="bi bi-briefcase"></i>
                <h6>No job vacancies found</h6>
                <p>Check back later for new opportunities.</p>
            </div>
        @else
            <div class="jobs-grid">
                @foreach($jobs as $job)
                <div class="job-card">
                    <div class="job-card-top">
                        <div class="job-icon"><i class="bi bi-building"></i></div>
                        <span class="job-card-type-badge">{{ ucfirst(str_replace('_', ' ', $job->type)) }}</span>
                    </div>
                    <p class="job-title">{{ $job->title }}</p>
                    <p class="job-company">{{ $job->company->company_name ?? 'Company' }}</p>
                    <div class="job-meta">
                        <span class="job-badge"><i class="bi bi-geo-alt me-1"></i>{{ $job->location }}</span>
                        <span class="job-badge"><i class="bi bi-people me-1"></i>{{ $job->slots }} slot/s</span>
                        @if($job->deadline)
                        <span class="job-badge-deadline"><i class="bi bi-calendar me-1"></i>Until {{ \Carbon\Carbon::parse($job->deadline)->format('M d, Y') }}</span>
                        @endif
                    </div>
                    <button type="button" class="btn-view-more"
                        onclick="showJobModal(
                            '{{ addslashes($job->title) }}',
                            '{{ addslashes($job->company->company_name ?? 'Company') }}',
                            '{{ addslashes($job->location) }}',
                            '{{ ucfirst(str_replace('_', ' ', $job->type)) }}',
                            '{{ $job->slots }}',
                            '{{ $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('M d, Y') : '' }}',
                            '{{ addslashes(strip_tags($job->description ?? '')) }}'
                        )">
                        View More
                    </button>
                </div>
                @endforeach
            </div>

            <div class="landing-pagination">
                <div class="pagination-summary">Page {{ $jobs->currentPage() }} out of {{ $jobs->lastPage() }}</div>
                <nav aria-label="Job listings pagination">
                    <ul class="pagination">
                        <li class="page-item {{ $jobs->onFirstPage() ? 'disabled' : '' }}">
                            @if($jobs->onFirstPage())
                                <span class="page-link" aria-disabled="true" aria-label="Previous page"><i class="bi bi-chevron-left"></i></span>
                            @else
                                <a class="page-link" href="{{ $jobs->previousPageUrl() }}" rel="prev" aria-label="Previous page"><i class="bi bi-chevron-left"></i></a>
                            @endif
                        </li>
                        <li class="page-item {{ $jobs->hasMorePages() ? '' : 'disabled' }}">
                            @if($jobs->hasMorePages())
                                <a class="page-link" href="{{ $jobs->nextPageUrl() }}" rel="next" aria-label="Next page"><i class="bi bi-chevron-right"></i></a>
                            @else
                                <span class="page-link" aria-disabled="true" aria-label="Next page"><i class="bi bi-chevron-right"></i></span>
                            @endif
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
    </div>

    <div class="peso-footer">
        <a href="https://www.facebook.com/PESOCDO" target="_blank" rel="noopener" class="footer-fb-link" aria-label="PESO CDO Facebook Page">
            <i class="bi bi-facebook"></i>
        </a>
        <div>© {{ date('Y') }} Decierdo · Tagarao · Rivas · Santizo — All rights reserved</div>
    </div>

    <div id="jobModalOverlay" class="job-modal-overlay">
        <div class="job-modal-box">
            <button class="close-btn" onclick="closeJobModal()"><i class="bi bi-x-lg"></i></button>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div class="job-icon"><i class="bi bi-building"></i></div>
                <div>
                    <div id="modalTitle" style="font-size:17px;font-weight:800;color:#2d7a5f;"></div>
                    <div id="modalCompany" style="font-size:12px;color:#888;"></div>
                </div>
            </div>
            <div id="modalMeta" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;"></div>
            <div id="modalDescription" style="font-size:13px;color:#555;line-height:1.6;margin-bottom:20px;"></div>
            <a href="{{ route('login') }}" class="btn-view-more" style="text-decoration:none;display:block;text-align:center;">
                <i class="bi bi-send-fill me-2"></i>Login to Apply
            </a>
        </div>
    </div>

    <script>
        function showJobModal(title, company, location, type, slots, deadline, description) {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalCompany').textContent = company;
            let metaHtml = `
                <span class="job-badge"><i class="bi bi-geo-alt me-1"></i>${location}</span>
                <span class="job-badge"><i class="bi bi-clock me-1"></i>${type}</span>
                <span class="job-badge"><i class="bi bi-people me-1"></i>${slots} slot/s</span>`;
            if (deadline) {
                metaHtml += `<span class="job-badge-deadline"><i class="bi bi-calendar me-1"></i>Until ${deadline}</span>`;
            }
            document.getElementById('modalMeta').innerHTML = metaHtml;
            document.getElementById('modalDescription').textContent = description || 'No description provided.';
            document.getElementById('jobModalOverlay').style.display = 'flex';
        }
        function closeJobModal() {
            document.getElementById('jobModalOverlay').style.display = 'none';
        }
    </script>

</body>
</html>