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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/peso.css') }}">
        <style>
            * { box-sizing: border-box; }
            html, body { height: 100%; }
            body {
                margin: 0;
                min-height: 100vh;
                font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
                /* The header keeps --g-600; the rest of the page sits one step
                   deeper so the large green field is easier on the eyes. */
                background: var(--g-800);
                display: flex;
                flex-direction: column;
            }

            .peso-navbar {
                position: fixed; top: 0; left: 0; right: 0; z-index: 500;
                padding: 14px 20px;
                background: rgba(20,85,27,0.94);
                backdrop-filter: blur(18px);
                -webkit-backdrop-filter: blur(18px);
                border-bottom: 1px solid rgba(255,255,255,0.2);
            }
            body { padding-top: 68px; }
            .navbar-inner {
                width: 100%; max-width: 1200px; margin: 0 auto;
                display: flex; align-items: center; justify-content: space-between;
                position: relative;
            }
            .navbar-brand { display: flex; align-items: center; gap: 10px; }
            .navbar-brand img { width: 34px; height: 34px; object-fit: contain; }
            .navbar-brand span { color: #fff; font-weight: 700; font-size: 12.5px; line-height: 1.25; }
            .navbar-brand small { color: rgba(255,255,255,0.75); font-weight: 500; font-size: 10.5px; }
            .navbar-toggle { display: block; background: none; border: none; color: #fff; font-size: 22px; cursor: pointer; }
            .navbar-links {
                display: none; position: absolute; top: 100%; left: 0; right: 0;
                background: rgba(20,85,27,0.97);
                flex-direction: column; padding: 16px 20px 20px; gap: 14px;
                border-bottom: 1px solid rgba(255,255,255,0.2);
            }
            .navbar-links.open { display: flex; }
            .navbar-links a { color: rgba(255,255,255,0.9); text-decoration: none; font-size: 13.5px; font-weight: 600; }
            .navbar-links a:hover { color: #fff; }
            .navbar-auth-btns { display: flex; gap: 10px; margin-top: 6px; }
            .btn-nav-login, .btn-nav-signup {
                border-radius: 8px; padding: 8px 16px; font-size: 12.5px; font-weight: 700; cursor: pointer; border: 1px solid transparent;
            }
            .btn-nav-login { background: transparent; border-color: rgba(255,255,255,0.5); color: #fff; }
            .btn-nav-signup { background: #fff; color: var(--g-700); border: none; }

            @media (min-width: 900px) {
                .navbar-toggle { display: none; }
                .navbar-links {
                    display: flex; position: static; flex-direction: row; align-items: center;
                    background: none; border: none; padding: 0; gap: 26px;
                }
                .navbar-auth-btns { margin-top: 0; }
            }

            .hero-section {
                position: relative;
                overflow: hidden;
                padding: 48px 20px;
            }
            .hero-slides { position: absolute; inset: 0; z-index: 0; }
            .hero-slide {
                position: absolute; inset: 0;
                width: 100%; height: 100%;
                object-fit: cover; object-position: center;
                opacity: 0;
                transition: opacity 1.2s ease-in-out;
            }
            .hero-slide.is-active { opacity: 1; }
            .hero-scrim {
                position: absolute; inset: 0; z-index: 1;
                background: linear-gradient(to right,
                    rgba(20,85,27,0.88) 0%,
                    rgba(20,85,27,0.72) 35%,
                    rgba(20,85,27,0.28) 55%,
                    rgba(20,85,27,0) 75%);
            }
            @media (prefers-reduced-motion: reduce) {
                .hero-slide { transition: none; }
            }
            .hero-content { position: relative; z-index: 2; max-width: 1200px; margin: 0 auto; }
            .hero-text-block { max-width: 560px; text-align: left; }
            .hero-tag {
                display: inline-block; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.45);
                color: #fff; font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;
                padding: 5px 14px; border-radius: 20px; margin-bottom: 18px;
            }
            .hero-section h1 { color: #fff; font-weight: 800; font-size: 26px; line-height: 1.3; margin-bottom: 10px; text-shadow: 0 2px 10px rgba(0,0,0,0.4); }
            .hero-highlight { color: var(--g-200); }
            .hero-section p { color: rgba(255,255,255,0.92); font-size: 13.5px; max-width: 520px; height: 88px; margin: 0 0 14px; line-height: 1.5; text-shadow: 0 1px 6px rgba(0,0,0,0.35); }
            .hero-btns { display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-start; margin-top: 0px; margin-bottom: 200px; }
            .btn-hero-primary, .btn-hero-secondary {
                border-radius: 10px; padding: 8px 15px; font-size: 15px; font-weight: 900; cursor: pointer; border: none; line-height: 1.2;
            }
            .btn-hero-primary { background: #fff; color: var(--g-700); }
            .btn-hero-secondary { background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.5); color: #fff; }

            @media (min-width: 768px) {
                .hero-section { padding: 90px 24px 64px; }
                .hero-section h1 { font-size: 40px; }
                .hero-section p { font-size: 22px; height: 93px; }
                .btn-hero-primary, .btn-hero-secondary { padding: 13px 18px; font-size: 16px; }
            }

            .about-section { max-width: 900px; margin: 0 auto; padding: 40px 20px; text-align: center; }
            .about-section h2 { color: #fff; font-weight: 800; font-size: 22px; margin-bottom: 14px; }
            .about-section p { color: rgba(255,255,255,0.85); font-size: 13.5px; line-height: 1.7; max-width: 640px; margin: 0 auto 10px; }

            .auth-overlay-backdrop {
                position: fixed; inset: 0; background: rgba(0,0,0,0.5);
                opacity: 0; pointer-events: none; transition: opacity 0.35s ease; z-index: 590;
            }
            .auth-overlay-backdrop.auth-open { opacity: 1; pointer-events: auto; }

            #authView {
        position: fixed; top: 0; right: 0; height: 100vh; width: 100%; max-width: 440px;
        background: var(--g-800); box-shadow: -12px 0 40px rgba(0,0,0,0.35);
        transform: translateX(100%); transition: transform 0.4s ease;
        z-index: 600; overflow-y: auto; scrollbar-gutter: stable;
        padding: 32px 40px;
        display: flex; align-items: flex-start; justify-content: center;
    }
            #authView.auth-open { transform: translateX(0); }

            .auth-wrap {
        width: 100%; max-width: 424px; margin: 40px auto 0; display: flex; flex-direction: column; align-items: stretch;
    }
            .auth-wrap .login-box {
        max-width: 100%; margin: 0 auto; background: transparent; border: none; box-shadow: none;
        padding: 0; backdrop-filter: none; width: 100%; align-items: center;
    }

    .auth-wrap .login-box form {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: stretch;
    }
            .auth-back-btn {
                background: none; border: none; color: rgba(255,255,255,0.8); font-size: 12.5px; font-weight: 600;
                margin-bottom: 28px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
                padding: 0; align-self: flex-start;
            }
            .auth-back-btn:hover { color: #fff; }

            .main-wrap {
                flex: 1;
                display: flex;
                justify-content: center;
                max-width: 1200px;
                width: 100%;
                margin: 0 auto;
                padding: 16px 14px 40px;
            }

            .jobs-panel-box {
                width: 100%;
                max-width: 1140px;
                background: rgba(255,255,255,0.14);
                border: 1px solid rgba(255,255,255,0.3);
                border-radius: 16px;
                padding: 20px 18px;
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                box-shadow: 0 20px 60px rgba(0,0,0,0.2);
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
            }

            .login-box {
                width: 100%;
                max-width: 700px;
                background: rgba(255,255,255,0.14);
                border: 1px solid rgba(255,255,255,0.3);
                border-radius: 16px;
                padding: 20px 18px;
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                box-shadow: 0 20px 60px rgba(0,0,0,0.2);
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
            }

            @media (min-width: 768px) {
                .main-wrap { padding: 32px 24px 56px; }
                .jobs-panel-box,
                .login-box { padding: 28px 26px; border-radius: 20px; }
            }

            .jobs-panel-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                margin-bottom: 16px;
                flex-wrap: wrap;
            }
            .jobs-panel-header-left { display: flex; align-items: center; gap: 10px; }
            .jobs-panel-header h5 {
                color: #fff;
                font-weight: 800;
                font-size: 18px;
                margin: 0;
            }
            .jobs-panel-header .badge-count {
                color: rgba(255,255,255,0.85);
                font-size: 11px;
                font-weight: 600;
            }

            .job-row {
                background: #fff;
                border: 1px solid var(--n-200);
                border-radius: 14px;
                padding: 14px 16px;
                margin-bottom: 12px;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 12px;
                box-shadow: 0 4px 14px rgba(0,0,0,0.05);
            }
            .job-row .btn-view-more { width: 100%; margin-top: 4px; }

            @media (min-width: 480px) {
                .job-row { flex-wrap: nowrap; padding: 16px 18px; }
                .job-row .btn-view-more { width: auto; margin-top: 0; }
            }
            .job-icon {
                width: 44px; height: 44px; border-radius: 12px;
                background: var(--g-600);
                display: flex; align-items: center; justify-content: center;
                color: #fff; font-size: 18px; flex-shrink: 0;
            }
            .job-row-body { flex: 1; min-width: 0; }
            .job-title { font-size: 14px; font-weight: 700; color: var(--peso-dark); margin: 0; }
            .job-company { font-size: 11.5px; color: var(--n-500); margin: 1px 0 6px; }
            .job-meta { display: flex; flex-wrap: wrap; gap: 6px; }
            /* Card meta — location, slots, deadline. Text, not buttons. */
            .job-badge {
                color: var(--n-500);
                font-size: 10.5px; font-weight: 600;
                white-space: nowrap;
            }
            .job-badge-deadline {
                color: var(--warn);
                font-size: 10.5px; font-weight: 600;
                white-space: nowrap;
            }
            .btn-view-more {
                background: var(--g-600);
                border: none; color: #fff; font-weight: 600;
                border-radius: 10px; padding: 8px 16px; font-size: 11.5px;
                white-space: nowrap; flex-shrink: 0;
            }

            .empty-box {
                background: #fff;
                border: 1px solid var(--n-200);
                border-radius: 16px; padding: 48px 20px; text-align: center;
            }
            .empty-box i { font-size: 40px; color: var(--n-300); }
            .empty-box h6 { color: var(--peso-dark); font-weight: 700; margin-top: 12px; }
            .empty-box p { color: var(--n-500); font-size: 13px; margin: 0; }

            .landing-pagination {
                margin-top: 18px;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }
            .landing-pagination .pagination-summary {
                color: #fff;
                font-size: 13px;
                font-weight: 700;
                letter-spacing: 0.3px;
            }
            .landing-pagination nav {
                width: 100%;
                display: flex;
                justify-content: center;
            }
            .landing-pagination .pagination {
                margin: 0;
                justify-content: center;
                gap: 4px;
                flex-wrap: wrap;
            }
            .landing-pagination .pagination .page-link {
                color: #fff;
                border-color: rgba(255,255,255,0.4);
                font-size: 13px;
                padding: 6px 12px;
                border-radius: 8px;
                background: rgba(255,255,255,0.14);
                min-width: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .landing-pagination .pagination .page-link:hover {
                color: #fff;
                background: rgba(255,255,255,0.24);
            }
            .landing-pagination .pagination .page-item.active .page-link {
                background: var(--g-400);
                border-color: transparent;
                color: #fff;
            }
            .landing-pagination .pagination .page-item.disabled .page-link {
                background: rgba(255,255,255,0.08);
                color: rgba(255,255,255,0.5);
                cursor: not-allowed;
            }

            .auth-logo {
                display: block; width: 70px; height: 70px; object-fit: contain; flex-shrink: 0;
            }
            .auth-brand-row {
        display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 4px; width: 100%;
    }
            .auth-brand-text {
        display: flex; flex-direction: column; align-items: flex-start; gap: 2px;
        color: #fff; font-weight: 700; font-size: 10px; line-height: 1.2;
        white-space: nowrap;
    }
            .auth-brand-text small {
                color: rgba(255,255,255,0.85); font-weight: 600; font-size: 10.5px;
                display: block;
            }
            .form-title { font-size: 18px; font-weight: 800; color: #fff; margin-bottom: 4px; text-align: center; width: 100%; }
    .form-sub { font-size: 12px; color: rgba(255,255,255,0.8); margin-bottom: 22px; text-align: center; width: 100%; }
            .peso-label {
        font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.9);
        margin: 0 auto 6px; display: block; letter-spacing: 0.3px; text-transform: uppercase;
        text-align: left;
        width: 100%;
    }
            .peso-input {
        width: 100%; border: 1px solid rgba(255,255,255,0.4); border-radius: 10px;
        font-size: 13px; padding: 10px 14px; color: #fff;
        background: rgba(255,255,255,0.1); outline: none; box-sizing: border-box;
        margin: 0 auto;
    }
            .peso-input::placeholder { color: rgba(255,255,255,0.6); }
            .peso-input:focus {
                border-color: #fff; box-shadow: 0 0 0 3px rgba(255,255,255,0.2);
                background: rgba(255,255,255,0.16);
            }
            .input-group-custom { position: relative; width: 100%; margin: 0 auto; }
            .input-group-custom .peso-input {
                width: 100%; padding-right: 42px; box-sizing: border-box;
            }
            .input-group-custom .toggle-pw {
                position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                background: none; border: none; color: rgba(255,255,255,0.8);
                cursor: pointer; font-size: 15px; padding: 0; line-height: 1;
                display: inline-flex; align-items: center; justify-content: center;
            }
            .btn-login {
        width: 100%; background: var(--g-600);
        border: none; color: #fff; font-weight: 700; border-radius: 10px;
        padding: 11px; font-size: 13.5px; cursor: pointer;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2); margin: 4px auto 0;
    }
            .btn-login:hover { opacity: 0.92; }
            .error-msg {
        background: rgba(198,40,40,0.25); border: 1px solid rgba(198,40,40,0.45);
        color: var(--danger-br); border-radius: 10px; padding: 9px 12px; font-size: 11.5px;
        margin: 0 auto 14px; display: flex; align-items: center; gap: 8px;
        width: 100%; box-sizing: border-box;
    }
    .success-msg {
        background: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.4);
        color: #fff; border-radius: 10px; padding: 9px 12px; font-size: 11.5px;
        margin: 0 auto 14px; display: flex; align-items: center; gap: 8px;
        width: 100%; box-sizing: border-box;
    }
            .register-link-row { text-align: center; margin-top: 16px; }
            .register-link-row span { font-size: 12px; color: rgba(255,255,255,0.8); }
            .register-link-row button {
                background: none; border: none; color: #fff; font-weight: 700;
                font-size: 12px; cursor: pointer; margin-left: 4px; text-decoration: underline;
            }

            .jobs-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
            @media (min-width: 576px) { .jobs-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; } }
            @media (min-width: 992px) { .jobs-grid { grid-template-columns: repeat(4, 1fr); gap: 16px; } }
            .job-card {
                background: #fff; border: 1px solid var(--n-200); border-radius: 14px; padding: 16px;
                box-shadow: 0 4px 14px rgba(0,0,0,0.05); display: flex; flex-direction: column; gap: 8px;
            }
            .job-card-poster {
                display: block;
                width: calc(100% + 32px);
                height: 130px;
                object-fit: cover;
                margin: -16px -16px 4px;
                border-radius: 13px 13px 0 0;
            }
            .job-card-top { display: flex; align-items: flex-start; justify-content: space-between; }
            .job-card-type-badge {
                color: var(--g-700); font-size: 10px; font-weight: 700;
                white-space: nowrap;
            }
            .job-card .job-title { font-size: 14.5px; }
            .job-card .btn-view-more { margin-top: auto; }
            .btn-view-all-header {
                display: inline-flex; align-items: center; gap: 6px;
                color: #fff; font-size: 12px; font-weight: 700; text-decoration: none;
                white-space: nowrap;
            }
            .btn-view-all-header:hover { text-decoration: underline; }

            .job-type-tab-btn {
                padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 700;
                text-decoration: none; color: rgba(255,255,255,0.85);
                background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.3);
            }
            .job-type-tab-btn:hover { color: #fff; background: rgba(255,255,255,0.22); }
            .job-type-tab-btn.active {
                background: #fff;
                color: var(--g-700); border-color: transparent;
            }

            .footer-box {
                width: 100%; margin-top: 48px; padding: 40px 24px 24px;
                /* One step below the page field so the footer reads as a
                   shadow. Portal footers keep --footer-bg. */
                background: var(--g-800);
            }
            .footer-grid {
                max-width: 1100px; margin: 0 auto;
                display: grid; grid-template-columns: 1fr; gap: 32px;
            }
            @media (min-width: 768px) {
                .footer-grid { grid-template-columns: 1.4fr 1fr 1.2fr; gap: 24px; }
            }
            .footer-brand-col { display: flex; flex-direction: column; gap: 10px; }
            .footer-brand-row { display: flex; align-items: center; gap: 10px; }
            .footer-brand-row img { width: 42px; height: 42px; object-fit: contain; }
            .footer-brand-row span { color: #fff; font-weight: 800; font-size: 13.5px; line-height: 1.3; }
            .footer-brand-row small { display: block; color: rgba(255,255,255,0.75); font-weight: 500; font-size: 11px; margin-top: 2px; }
            .footer-desc { color: rgba(255,255,255,0.85); font-size: 12.5px; line-height: 1.7; margin: 0; max-width: 340px; }
            .footer-col h6 {
                color: #fff; font-weight: 800; font-size: 13px; text-transform: uppercase;
                letter-spacing: 0.5px; margin-bottom: 14px;
            }
            .footer-col ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
            .footer-col ul a {
                color: rgba(255,255,255,0.88); text-decoration: none; font-size: 13px; font-weight: 500;
            }
            .footer-col ul a:hover { color: #fff; text-decoration: underline; }
            .footer-contact-item {
                display: flex; align-items: flex-start; gap: 10px; margin-bottom: 14px;
                color: rgba(255,255,255,0.9); font-size: 12.5px; line-height: 1.6;
            }
            .footer-contact-item i { color: #fff; font-size: 15px; margin-top: 2px; flex-shrink: 0; }
            .footer-fb-link {
                display: inline-flex; align-items: center; justify-content: center;
                width: 40px; height: 40px;
                background: rgba(255,255,255,0.18); color: #fff;
                border-radius: 50%; text-decoration: none;
            }
            .footer-fb-link i { font-size: 17px; color: #fff; margin: 0; }
            .footer-fb-link:hover { background: #1877f2; color: #fff; }
            .footer-bottom {
                max-width: 1100px; margin: 32px auto 0; padding-top: 20px;
                border-top: 1px solid rgba(255,255,255,0.25);
                text-align: center;
            }
            .footer-bottom p { color: rgba(255,255,255,0.8); font-size: 11.5px; margin: 0; }

            .job-modal-overlay {
                display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5);
                z-index: 999; align-items: center; justify-content: center; padding: 20px;
            }
            .job-modal-box {
                background: #fff; border-radius: 18px; padding: 24px 20px;
                max-width: 480px; width: 100%; position: relative;
                box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            }

            @media (min-width: 576px) {
                .job-modal-box { padding: 32px; }
            }
            .job-modal-box .close-btn {
                position: absolute; top: 14px; right: 14px; background: none; border: none;
                font-size: 18px; color: var(--n-500); cursor: pointer;
            }
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
                    <i class="ph ph-list"></i>
                </button>
                <div class="navbar-links" id="navbarLinks">
                    <a href="#home" onclick="showHome(event)">Home</a>
                    <a href="#jobsSection" onclick="scrollToSection(event, 'jobsSection')">Job Postings</a>
                    <a href="#aboutSection" onclick="scrollToSection(event, 'aboutSection')">About Us</a>
                    <div class="navbar-auth-btns">
                        <button class="btn-nav-login" onclick="showAuth('login')">Login</button>
                        <button class="btn-nav-signup" onclick="showAuth('register')">Sign Up</button>
                    </div>
                </div>
            </div>
        </nav>

        <div id="mainView">

        <section class="hero-section" id="home">
            <div class="hero-slides" aria-hidden="true">
                @foreach($heroImages as $i => $src)
                    <img class="hero-slide{{ $i === 0 ? ' is-active' : '' }}"
                         src="{{ $src }}" alt="" decoding="async"
                         @if($i === 0) fetchpriority="high" @endif>
                @endforeach
            </div>
            <div class="hero-scrim" aria-hidden="true"></div>
            <div class="hero-content">
                <div class="hero-text-block">
                    <span class="hero-tag">PUBLIC EMPLOYMENT SERVICE OFFICE</span>
                    <h1>Connecting Talent,<br><span class="hero-highlight">Creating Opportunities,</span><br>Building Cagayan de Oro.</h1>
                    <p>PESO CDO is your partner in employment. We help job seekers find the right opportunities and employers find the right talent.</p>
                    <div class="hero-btns">
                        <button class="btn-hero-primary" onclick="scrollToSection(event, 'jobsSection')">
                            <i class="ph-fill ph-briefcase me-2"></i>Browse Job Postings
                        </button>
                        <button class="btn-hero-secondary" onclick="showAuth('register')">
                            <i class="ph-fill ph-user-plus me-2"></i>Get Started
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <div class="main-wrap" id="jobsSection">

            <div class="jobs-panel-box">
                <div class="jobs-panel-header">
                <div class="jobs-panel-header-left">
                    <h5><i class="ph-fill ph-briefcase me-1"></i> Job Postings</h5>
                    <span class="badge-count">{{ $openJobsCount }} open</span>
                </div>
                <a href="{{ route('jobs.all') }}" class="btn-view-all-header">
                    View All Jobs <i class="ph ph-arrow-right"></i>
                </a>
            </div>

            <div class="d-flex gap-2 mb-3" style="flex-wrap:wrap;">
                <a href="{{ route('landing', ['job_type' => 'all']) }}#jobsSection" class="job-type-tab-btn {{ $jobType === 'all' ? 'active' : '' }}">All</a>
                <a href="{{ route('landing', ['job_type' => 'local']) }}#jobsSection" class="job-type-tab-btn {{ $jobType === 'local' ? 'active' : '' }}">Local</a>
                <a href="{{ route('landing', ['job_type' => 'overseas']) }}#jobsSection" class="job-type-tab-btn {{ $jobType === 'overseas' ? 'active' : '' }}">Overseas</a>
                <a href="{{ route('landing', ['job_type' => 'job_fair']) }}#jobsSection" class="job-type-tab-btn {{ $jobType === 'job_fair' ? 'active' : '' }}">Job Fair</a>
            </div>

                @if($jobs->isEmpty())
                    <div class="empty-box">
                        <i class="ph ph-briefcase"></i>
                        <h6>No job vacancies found</h6>
                        <p>Check back later for new opportunities.</p>
                    </div>
                @else
                    <div class="jobs-grid">
                        @foreach($jobs as $job)
                        <div class="job-card">
                            @if($job->poster_image)
                            <img src="{{ asset('storage/' . $job->poster_image) }}"
                                 alt="{{ $job->title }} hiring poster" class="job-card-poster" loading="lazy">
                            @endif
                            <div class="job-card-top">
                                <div class="job-icon"><i class="ph ph-buildings"></i></div>
                                <span class="job-card-type-badge">{{ ucfirst(str_replace('_', ' ', $job->type)) }}</span>
                            </div>
                            <p class="job-title">{{ $job->title }}</p>
                            <p class="job-company">{{ $job->company->company_name ?? 'Company' }}</p>
                            <div class="job-meta">
                                <span class="job-badge"><i class="ph ph-map-pin me-1"></i>{{ $job->location }}</span>
                                <span class="job-badge"><i class="ph ph-users-three me-1"></i>{{ $job->slots }} slot/s</span>
                                @if($job->deadline)
                                <span class="job-badge-deadline">
                                    <i class="ph ph-calendar-blank me-1"></i>Until {{ \Carbon\Carbon::parse($job->deadline)->format('M d, Y') }}
                                </span>
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
                                    '{{ addslashes(strip_tags($job->description ?? '')) }}',
                                    '{{ $job->poster_image ? asset('storage/' . $job->poster_image) : '' }}'
                                )">
                                View More
                            </button>
                        </div>
                        @endforeach
                    </div>

                    @endif
            </div>

            </div>

        <section class="about-section" id="aboutSection">
            <h2>About This System</h2>
            <p>This is a Web-based Job  Management System developed for the Public Employment Service Office (PESO) of Cagayan de Oro City. It streamlines the entire job application process — from registration and job posting to matching and scheduling — for both job seekers and employers.</p>
            <p>The system allows job seekers to browse open vacancies, submit applications online, and track their status, while employers can post job openings, manage requirements, and connect with qualified applicants — all in one platform under PESO CDO.</p>
        </section>

        </div>

        <div id="authOverlayBackdrop" class="auth-overlay-backdrop" onclick="showHome()"></div>

        <div id="authView">
            <div class="auth-wrap">
                <button class="auth-back-btn" onclick="showHome()"><i class="ph ph-x"></i> Close</button>

                <div id="loginPane" class="login-box">
                    <div class="auth-brand-row">
                        <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo" class="auth-logo">
                        <div class="auth-brand-text">
                            <span>PUBLIC EMPLOYMENT SERVICE OFFICE</span>
                            <small>A Web-based Job Management System</small>
                        </div>
                    </div>
                    <div class="form-title">Login Page</div>
                    <div class="form-sub">Sign in to your PESO account</div>
                    @if($errors->any())
                        <div class="error-msg">
                            <i class="ph-fill ph-warning-circle"></i>
                            {{ $errors->first() }}
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="success-msg">
                            <i class="ph-fill ph-check-circle"></i>
                            {{ session('success') }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="peso-label">Email Address</label>
                            <input type="email" name="email" class="peso-input"
                                placeholder="Enter your email"
                                value="{{ old('email') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="peso-label">Password</label>
                            <div class="input-group-custom">
                                <input type="password" name="password" id="password"
                                    class="peso-input" placeholder="Enter your password" required>
                                <button type="button" class="toggle-pw" onclick="togglePw()">
                                    <i class="ph ph-eye" id="pw-icon"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn-login">
                            <i class="ph ph-sign-in me-2"></i>Sign In
                        </button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="{{ route('password.request') }}" style="font-size:12px;color:#fff;font-weight:600;text-decoration:underline;">
                            Forgot Password?
                        </a>
                    </div>
                    <div class="register-link-row">
                        <span>Don't have an account?</span>
                        <button onclick="switchAuthPane('register')">
                            Register here
                        </button>
                    </div>
                </div>

                <div id="registerPane" class="login-box" style="display:none;">
                    <div class="form-title">Create Account</div>
                    <div class="form-sub">Choose your account type to get started</div>
                    <div class="register-choice-row" style="display:flex;gap:14px;flex-wrap:wrap;">
                        <a href="{{ route('register.jobseeker') }}" style="text-decoration:none;flex:1 1 160px;">
                            <div style="border:2px solid rgba(255,255,255,0.3);border-radius:16px;padding:20px 14px;text-align:center;">
                                <div style="width:56px;height:56px;background:rgba(255,255,255,0.18);
                                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                                            margin:0 auto 10px;font-size:24px;color:#fff;">
                                    <i class="ph-fill ph-user"></i>
                                </div>
                                <div style="font-size:14px;font-weight:700;color:#fff;">Job Seeker</div>
                                <div style="font-size:10.5px;color:rgba(255,255,255,0.75);margin-top:6px;line-height:1.5;">
                                    Looking for a job? Register here to browse vacancies and apply online.
                                </div>
                            </div>
                        </a>
                        <a href="{{ route('register.company') }}" style="text-decoration:none;flex:1 1 160px;">
                            <div style="border:2px solid rgba(255,255,255,0.3);border-radius:16px;padding:20px 14px;text-align:center;">
                                <div style="width:56px;height:56px;background:rgba(255,255,255,0.18);
                                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                                            margin:0 auto 10px;font-size:24px;color:#fff;">
                                    <i class="ph-fill ph-buildings"></i>
                                </div>
                                <div style="font-size:14px;font-weight:700;color:#fff;">Employer</div>
                                <div style="font-size:10.5px;color:rgba(255,255,255,0.75);margin-top:6px;line-height:1.5;">
                                    Hiring? Register your company and post job vacancies on PESO.
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="register-link-row">
                        <span>Already have an account?</span>
                        <button onclick="switchAuthPane('login')">
                            Login here
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-box" id="contactSection">
            <div class="footer-grid">
                <div class="footer-brand-col">
                    <div class="footer-brand-row">
                        <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo">
                        <span>PUBLIC EMPLOYMENT SERVICE OFFICE<small>A Web-based Job Management System</small></span>
                    </div>
                    <p class="footer-desc">
                        Connecting job seekers and employers in Cagayan de Oro City — from registration and job posting to matching and scheduling, all in one platform under PESO CDO.
                    </p>
                </div>

                <div class="footer-col">
                    <h6>Quick Links</h6>
                    <ul>
                        <li><a href="#home" onclick="showHome(event)">Home</a></li>
                        <li><a href="#jobsSection" onclick="scrollToSection(event, 'jobsSection')">Job Postings</a></li>
                        <li><a href="#aboutSection" onclick="scrollToSection(event, 'aboutSection')">About Us</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h6>Contact Us</h6>
                    <div class="footer-contact-item">
                        <i class="ph-fill ph-map-pin"></i>
                        <span>2nd Floor New Executive Building, City Hall, Capistrano-Hayes Sts., Cagayan de Oro, Philippines, 9000</span>
                    </div>
                    <a href="https://www.facebook.com/PESOCDO" target="_blank" rel="noopener" class="footer-fb-link" aria-label="PESO CDO Facebook Page">
                        <i class="ph-fill ph-facebook-logo"></i>
                    </a>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Public Employment Service Office — Cagayan de Oro City. All rights reserved.</p>
            </div>
        </div>

        <div id="jobModalOverlay" class="job-modal-overlay">
            <div class="job-modal-box">
                <button class="close-btn" onclick="closeJobModal()"><i class="ph ph-x"></i></button>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                    <div class="job-icon"><i class="ph ph-buildings"></i></div>
                    <div>
                        <div id="modalTitle" style="font-size:17px;font-weight:800;color:var(--peso-dark);"></div>
                        <div id="modalCompany" style="font-size:12px;color:var(--n-500);"></div>
                    </div>
                </div>
                <img id="modalPoster" alt="Hiring poster"
                     style="display:none;width:100%;max-height:260px;object-fit:cover;
                            border-radius:12px;margin-bottom:14px;">
                <div id="modalMeta" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;"></div>
                <div id="modalDescription" style="font-size:13px;color:var(--n-700);line-height:1.6;margin-bottom:20px;"></div>
                <a href="{{ route('login') }}" class="btn-login" style="text-decoration:none;display:block;text-align:center;">
                    <i class="ph-fill ph-paper-plane-tilt me-2"></i>Login to Apply
                </a>
            </div>
        </div>

        <script>
            @if(session('success') || $errors->any() || request()->routeIs('login'))
                // A failed login redirects back here, so the panel has to reopen
                // itself — otherwise the message sits in the page unseen and the
                // attempt looks like it simply did nothing.
                document.addEventListener('DOMContentLoaded', function() {
                    showAuth('login');
                    @if($errors->any())
                        const pw = document.getElementById('password');
                        if (pw) pw.focus();
                    @endif
                });
            @endif

            function scrollToSection(e, id) {
                e.preventDefault();
                closeAuthPanel();
                document.getElementById(id).scrollIntoView({ behavior: 'smooth' });
                document.getElementById('navbarLinks').classList.remove('open');
            }

            function switchAuthPane(pane) {
                document.getElementById('loginPane').style.display = pane === 'login' ? 'flex' : 'none';
                document.getElementById('registerPane').style.display = pane === 'register' ? 'flex' : 'none';
            }

            function showAuth(pane) {
                switchAuthPane(pane);
                document.getElementById('authView').classList.add('auth-open');
                document.getElementById('authOverlayBackdrop').classList.add('auth-open');
                document.body.style.overflow = 'hidden';
                document.getElementById('navbarLinks').classList.remove('open');
            }

            function showHome(e) {
                if (e) e.preventDefault();
                closeAuthPanel();
                document.getElementById('home').scrollIntoView({ behavior: 'smooth' });
                document.getElementById('navbarLinks').classList.remove('open');
            }

            function closeAuthPanel() {
                document.getElementById('authView').classList.remove('auth-open');
                document.getElementById('authOverlayBackdrop').classList.remove('auth-open');
                document.body.style.overflow = '';
            }

            function togglePw() {
                const pw   = document.getElementById('password');
                const icon = document.getElementById('pw-icon');
                if (pw.type === 'password') {
                    pw.type = 'text';
                    icon.className = 'ph ph-eye-slash';
                } else {
                    pw.type = 'password';
                    icon.className = 'ph ph-eye';
                }
            }

            function showJobModal(title, company, location, type, slots, deadline, description, poster) {
                document.getElementById('modalTitle').textContent = title;
                document.getElementById('modalCompany').textContent = company;

                // Only the postings the employer gave a poster to show one.
                const posterEl = document.getElementById('modalPoster');
                posterEl.src = poster || '';
                posterEl.style.display = poster ? 'block' : 'none';

                let metaHtml = `
                    <span class="job-badge"><i class="ph ph-map-pin me-1"></i>${location}</span>
                    <span class="job-badge"><i class="ph ph-clock me-1"></i>${type}</span>
                    <span class="job-badge"><i class="ph ph-users-three me-1"></i>${slots} slot/s</span>`;
                if (deadline) {
                    metaHtml += `<span class="job-badge-deadline"><i class="ph ph-calendar-blank me-1"></i>Until ${deadline}</span>`;
                }
                document.getElementById('modalMeta').innerHTML = metaHtml;
                document.getElementById('modalDescription').textContent = description || 'No description provided.';

                document.getElementById('jobModalOverlay').style.display = 'flex';
            }

            function closeJobModal() {
                document.getElementById('jobModalOverlay').style.display = 'none';
            }

            // ── Hero background rotation ──
            (function () {
                const slides = document.querySelectorAll('.hero-slide');
                if (slides.length < 2) return;
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

                let current = 0;
                setInterval(function () {
                    slides[current].classList.remove('is-active');
                    current = (current + 1) % slides.length;
                    slides[current].classList.add('is-active');
                }, 5000);
            })();
        </script>

    </body>
    </html>