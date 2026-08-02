    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PESO CDO — Job Vacancies</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
        <style>
            :root {
                --peso-green:  #4dd9c0;
                --peso-light:  #90d870;
                --peso-dark:   #2d7a5f;
                --peso-border: #a8e6cf;
            }
            * { box-sizing: border-box; }
            html, body { height: 100%; }
            body {
                margin: 0;
                min-height: 100vh;
                font-family: 'Segoe UI', sans-serif;
                background: #0d1f18;
                display: flex;
                flex-direction: column;
            }

            .peso-navbar {
                position: fixed; top: 0; left: 0; right: 0; z-index: 500;
                padding: 14px 20px;
                background: rgba(13,31,24,0.82);
                backdrop-filter: blur(18px);
                -webkit-backdrop-filter: blur(18px);
                border-bottom: 1px solid rgba(255,255,255,0.12);
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
            .navbar-brand small { color: rgba(255,255,255,0.65); font-weight: 500; font-size: 10.5px; }
            .navbar-toggle { display: block; background: none; border: none; color: #fff; font-size: 22px; cursor: pointer; }
            .navbar-links {
                display: none; position: absolute; top: 100%; left: 0; right: 0;
                background: rgba(13,31,24,0.97);
                flex-direction: column; padding: 16px 20px 20px; gap: 14px;
                border-bottom: 1px solid rgba(255,255,255,0.12);
            }
            .navbar-links.open { display: flex; }
            .navbar-links a { color: rgba(255,255,255,0.85); text-decoration: none; font-size: 13.5px; font-weight: 600; }
            .navbar-links a:hover { color: #90d870; }
            .navbar-auth-btns { display: flex; gap: 10px; margin-top: 6px; }
            .btn-nav-login, .btn-nav-signup {
                border-radius: 8px; padding: 8px 16px; font-size: 12.5px; font-weight: 700; cursor: pointer; border: 1.5px solid transparent;
            }
            .btn-nav-login { background: transparent; border-color: rgba(255,255,255,0.3); color: #fff; }
            .btn-nav-signup { background: linear-gradient(90deg, var(--peso-light), var(--peso-green)); color: #0f2e24; border: none; }

            @media (min-width: 900px) {
                .navbar-toggle { display: none; }
                .navbar-links {
                    display: flex; position: static; flex-direction: row; align-items: center;
                    background: none; border: none; padding: 0; gap: 26px;
                }
                .navbar-auth-btns { margin-top: 0; }
            }

            .hero-section {
                background-image: linear-gradient(to right, rgba(13,31,24,0.85) 0%, rgba(13,31,24,0.7) 35%, rgba(13,31,24,0.25) 55%, rgba(13,31,24,0) 75%), url('{{ asset('images/cityhall.png') }}');
                background-size: cover; background-position: center;
                padding: 48px 20px;
            }
            .hero-content { max-width: 1200px; margin: 0 auto; }
            .hero-text-block { max-width: 560px; text-align: left; }
            .hero-tag {
                display: inline-block; background: rgba(144,216,112,0.2); border: 1px solid rgba(144,216,112,0.4);
                color: #90d870; font-size: 10.5px; font-weight: 700; letter-spacing: 0.5px;
                padding: 5px 14px; border-radius: 20px; margin-bottom: 18px;
            }
            .hero-section h1 { color: #fff; font-weight: 800; font-size: 26px; line-height: 1.3; margin-bottom: 10px; text-shadow: 0 2px 10px rgba(0,0,0,0.6); }
            .hero-highlight { color: #90d870; }
            .hero-section p { color: rgba(255,255,255,0.9); font-size: 13.5px; max-width: 520px; height: 88px; margin: 0 0 14px; line-height: 1.5; text-shadow: 0 1px 6px rgba(0,0,0,0.6); }
            .hero-btns { display: flex; flex-wrap: wrap; gap: 8px; justify-content: flex-start; margin-top: 0px; margin-bottom: 200px; }
            .btn-hero-primary, .btn-hero-secondary {
                border-radius: 10px; padding: 8px 15px; font-size: 15px; font-weight: 900; cursor: pointer; border: none; line-height: 1.2;
            }
            .btn-hero-primary { background: linear-gradient(90deg, var(--peso-light), var(--peso-green)); color: #0f2e24; }
            .btn-hero-secondary { background: rgba(255,255,255,0.12); border: 1.5px solid rgba(255,255,255,0.4); color: #fff; }

            @media (min-width: 768px) {
                .hero-section { padding: 90px 24px 64px; }
                .hero-section h1 { font-size: 40px; }
                .hero-section p { font-size: 22px; height: 93px; }
                .btn-hero-primary, .btn-hero-secondary { padding: 13px 18px; font-size: 16px; }
            }

            .about-section { max-width: 900px; margin: 0 auto; padding: 40px 20px; text-align: center; }
            .about-section h2 { color: #fff; font-weight: 800; font-size: 22px; margin-bottom: 14px; }
            .about-section p { color: rgba(255,255,255,0.78); font-size: 13.5px; line-height: 1.7; max-width: 640px; margin: 0 auto 10px; }

            .auth-overlay-backdrop {
                position: fixed; inset: 0; background: rgba(0,0,0,0.55);
                opacity: 0; pointer-events: none; transition: opacity 0.35s ease; z-index: 590;
            }
            .auth-overlay-backdrop.auth-open { opacity: 1; pointer-events: auto; }

            #authView {
        position: fixed; top: 0; right: 0; height: 100vh; width: 100%; max-width: 440px;
        background: #14322a; box-shadow: -12px 0 40px rgba(0,0,0,0.5);
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
                background: none; border: none; color: rgba(255,255,255,0.75); font-size: 12.5px; font-weight: 600;
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
                background: rgba(255,255,255,0.12);
                border: 1px solid rgba(255,255,255,0.28);
                border-radius: 16px;
                padding: 20px 18px;
                backdrop-filter: blur(18px);
                -webkit-backdrop-filter: blur(18px);
                box-shadow: 0 20px 60px rgba(0,0,0,0.35);
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
            }

            .login-box {
                width: 100%;
                max-width: 700px;
                background: rgba(255,255,255,0.12);
                border: 1px solid rgba(255,255,255,0.28);
                border-radius: 16px;
                padding: 20px 18px;
                backdrop-filter: blur(18px);
                -webkit-backdrop-filter: blur(18px);
                box-shadow: 0 20px 60px rgba(0,0,0,0.35);
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
                background: rgba(255,255,255,0.15);
                color: #fff;
                font-size: 11px;
                padding: 3px 10px;
                border-radius: 20px;
                font-weight: 600;
            }

            .job-row {
                background: rgba(255,255,255,0.96);
                border-radius: 14px;
                padding: 14px 16px;
                margin-bottom: 12px;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 12px;
                box-shadow: 0 6px 18px rgba(0,0,0,0.15);
            }
            .job-row .btn-view-more { width: 100%; margin-top: 4px; }

            @media (min-width: 480px) {
                .job-row { flex-wrap: nowrap; padding: 16px 18px; }
                .job-row .btn-view-more { width: auto; margin-top: 0; }
            }
            .job-icon {
                width: 44px; height: 44px; border-radius: 12px;
                background: linear-gradient(135deg, var(--peso-light), var(--peso-green));
                display: flex; align-items: center; justify-content: center;
                color: #fff; font-size: 18px; flex-shrink: 0;
            }
            .job-row-body { flex: 1; min-width: 0; }
            .job-title { font-size: 14px; font-weight: 700; color: var(--peso-dark); margin: 0; }
            .job-company { font-size: 11.5px; color: #888; margin: 1px 0 6px; }
            .job-meta { display: flex; flex-wrap: wrap; gap: 6px; }
            .job-badge {
                background: #f0f9f6; color: var(--peso-dark);
                font-size: 10.5px; padding: 3px 9px; border-radius: 20px; font-weight: 600;
                white-space: nowrap;
            }
            .job-badge-deadline {
                background: #fff8e1; color: #f9a825;
                font-size: 10.5px; padding: 3px 9px; border-radius: 20px; font-weight: 600;
                white-space: nowrap;
            }
            .btn-view-more {
                background: linear-gradient(90deg, var(--peso-light), var(--peso-green));
                border: none; color: #fff; font-weight: 600;
                border-radius: 10px; padding: 8px 16px; font-size: 11.5px;
                white-space: nowrap; flex-shrink: 0;
            }

            .empty-box {
                background: rgba(255,255,255,0.9);
                border-radius: 16px; padding: 48px 20px; text-align: center;
            }
            .empty-box i { font-size: 40px; color: #c0e8dc; }
            .empty-box h6 { color: var(--peso-dark); font-weight: 700; margin-top: 12px; }
            .empty-box p { color: #888; font-size: 13px; margin: 0; }

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
                text-shadow: 0 1px 2px rgba(0,0,0,0.35);
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
                border-color: rgba(255,255,255,0.35);
                font-size: 13px;
                padding: 6px 12px;
                border-radius: 8px;
                background: rgba(255,255,255,0.14);
                min-width: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                backdrop-filter: blur(6px);
            }
            .landing-pagination .pagination .page-link:hover {
                color: #fff;
                background: rgba(255,255,255,0.22);
            }
            .landing-pagination .pagination .page-item.active .page-link {
                background: linear-gradient(90deg, var(--peso-light), var(--peso-green));
                border-color: transparent;
                color: #fff;
            }
            .landing-pagination .pagination .page-item.disabled .page-link {
                background: rgba(255,255,255,0.12);
                color: rgba(255,255,255,0.6);
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
                color: rgba(255,255,255,0.95); font-weight: 600; font-size: 10.5px;
                display: block;
            }
            .form-title { font-size: 18px; font-weight: 800; color: #fff; margin-bottom: 4px; text-align: center; width: 100%; }
    .form-sub { font-size: 12px; color: rgba(255,255,255,0.75); margin-bottom: 22px; text-align: center; width: 100%; }
            .peso-label {
        font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.9);
        margin: 0 auto 6px; display: block; letter-spacing: 0.3px; text-transform: uppercase;
        text-align: left;
        width: 100%;
    }
            .peso-input {
        width: 100%; border: 1.5px solid rgba(255,255,255,0.35); border-radius: 10px;
        font-size: 13px; padding: 10px 14px; color: #fff;
        background: rgba(255,255,255,0.08); outline: none; box-sizing: border-box;
        margin: 0 auto;
    }
            .peso-input::placeholder { color: rgba(255,255,255,0.55); }
            .peso-input:focus {
                border-color: #90d870; box-shadow: 0 0 0 3px rgba(144,216,112,0.25);
                background: rgba(255,255,255,0.14);
            }
            .input-group-custom { position: relative; width: 100%; margin: 0 auto; }
            .input-group-custom .peso-input {
                width: 100%; padding-right: 42px; box-sizing: border-box;
            }
            .input-group-custom .toggle-pw {
                position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                background: none; border: none; color: rgba(255,255,255,0.75);
                cursor: pointer; font-size: 15px; padding: 0; line-height: 1;
                display: inline-flex; align-items: center; justify-content: center;
            }
            .btn-login {
        width: 100%; background: linear-gradient(90deg, #90d870, #4dd9c0);
        border: none; color: #0f2e24; font-weight: 700; border-radius: 10px;
        padding: 11px; font-size: 13.5px; cursor: pointer;
        box-shadow: 0 4px 20px rgba(77,217,192,0.4); margin: 4px auto 0;
    }
            .btn-login:hover { opacity: 0.92; }
            .error-msg {
        background: rgba(198,40,40,0.25); border: 1px solid rgba(198,40,40,0.4);
        color: #ffd6d6; border-radius: 10px; padding: 9px 12px; font-size: 11.5px;
        margin: 0 auto 14px; display: flex; align-items: center; gap: 8px;
        width: 100%; box-sizing: border-box;
    }
    .success-msg {
        background: rgba(77,217,192,0.2); border: 1px solid rgba(77,217,192,0.4);
        color: #d9fff5; border-radius: 10px; padding: 9px 12px; font-size: 11.5px;
        margin: 0 auto 14px; display: flex; align-items: center; gap: 8px;
        width: 100%; box-sizing: border-box;
    }
            .register-link-row { text-align: center; margin-top: 16px; }
            .register-link-row span { font-size: 12px; color: rgba(255,255,255,0.75); }
            .register-link-row button {
                background: none; border: none; color: #90d870; font-weight: 700;
                font-size: 12px; cursor: pointer; margin-left: 4px;
            }

            .jobs-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
            @media (min-width: 576px) { .jobs-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; } }
            @media (min-width: 992px) { .jobs-grid { grid-template-columns: repeat(4, 1fr); gap: 16px; } }
            .job-card {
                background: rgba(255,255,255,0.96); border-radius: 14px; padding: 16px;
                box-shadow: 0 6px 18px rgba(0,0,0,0.15); display: flex; flex-direction: column; gap: 8px;
            }
            .job-card-top { display: flex; align-items: flex-start; justify-content: space-between; }
            .job-card-type-badge {
                background: #eafaf0; color: var(--peso-dark); font-size: 10px; font-weight: 700;
                padding: 3px 10px; border-radius: 20px; white-space: nowrap;
            }
            .job-card .job-title { font-size: 14.5px; }
            .job-card .btn-view-more { margin-top: auto; }
            .btn-view-all-header {
                display: inline-flex; align-items: center; gap: 6px;
                color: #90d870; font-size: 12px; font-weight: 700; text-decoration: none;
                white-space: nowrap;
            }  
            .btn-view-all-header:hover { color: #fff; }

            .job-type-tab-btn {
                padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 700;
                text-decoration: none; color: rgba(255,255,255,0.75);
                background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            }
            .job-type-tab-btn:hover { color: #fff; background: rgba(255,255,255,0.18); }
            .job-type-tab-btn.active {
                background: linear-gradient(90deg, var(--peso-light), var(--peso-green));
                color: #0f2e24; border-color: transparent;
            }

            .footer-box {
                width: 100%; margin-top: 48px; padding: 40px 24px 24px;
                background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.15);
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
            .footer-brand-row small { display: block; color: rgba(255,255,255,0.6); font-weight: 500; font-size: 11px; margin-top: 2px; }
            .footer-desc { color: rgba(255,255,255,0.65); font-size: 12.5px; line-height: 1.7; margin: 0; max-width: 340px; }
            .footer-col h6 {
                color: #fff; font-weight: 800; font-size: 13px; text-transform: uppercase;
                letter-spacing: 0.5px; margin-bottom: 14px;
            }
            .footer-col ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
            .footer-col ul a {
                color: rgba(255,255,255,0.7); text-decoration: none; font-size: 13px; font-weight: 500;
            }
            .footer-col ul a:hover { color: #90d870; }
            .footer-contact-item {
                display: flex; align-items: flex-start; gap: 10px; margin-bottom: 14px;
                color: rgba(255,255,255,0.75); font-size: 12.5px; line-height: 1.6;
            }
            .footer-contact-item i { color: #90d870; font-size: 15px; margin-top: 2px; flex-shrink: 0; }
            .footer-fb-link {
                display: inline-flex; align-items: center; justify-content: center;
                width: 40px; height: 40px;
                background: rgba(255,255,255,0.1); color: #fff;
                border-radius: 50%; text-decoration: none;
            }
            .footer-fb-link i { font-size: 17px; color: #fff; margin: 0; }
            .footer-fb-link:hover { background: #1877f2; color: #fff; }
            .footer-bottom {
                max-width: 1100px; margin: 32px auto 0; padding-top: 20px;
                border-top: 1px solid rgba(255,255,255,0.1);
                text-align: center;
            }
            .footer-bottom p { color: rgba(255,255,255,0.5); font-size: 11.5px; margin: 0; }

            .job-modal-overlay {
                display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6);
                z-index: 999; align-items: center; justify-content: center; padding: 20px;
            }
            .job-modal-box {
                background: #fff; border-radius: 18px; padding: 24px 20px;
                max-width: 480px; width: 100%; position: relative;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }

            @media (min-width: 576px) {
                .job-modal-box { padding: 32px; }
            }
            .job-modal-box .close-btn {
                position: absolute; top: 14px; right: 14px; background: none; border: none;
                font-size: 18px; color: #888; cursor: pointer;
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
                    <i class="bi bi-list"></i>
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
            <div class="hero-content">
                <div class="hero-text-block">
                    <span class="hero-tag">PUBLIC EMPLOYMENT SERVICE OFFICE</span>
                    <h1>Connecting Talent,<br><span class="hero-highlight">Creating Opportunities,</span><br>Building Cagayan de Oro.</h1>
                    <p>PESO CDO is your partner in employment. We help job seekers find the right opportunities and employers find the right talent.</p>
                    <div class="hero-btns">
                        <button class="btn-hero-primary" onclick="scrollToSection(event, 'jobsSection')">
                            <i class="bi bi-briefcase-fill me-2"></i>Browse Job Postings
                        </button>
                        <button class="btn-hero-secondary" onclick="showAuth('register')">
                            <i class="bi bi-person-plus-fill me-2"></i>Get Started
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <div class="main-wrap" id="jobsSection">

            <div class="jobs-panel-box">
                <div class="jobs-panel-header">
                <div class="jobs-panel-header-left">
                    <h5><i class="bi bi-briefcase-fill me-1"></i> Job Postings</h5>
                    <span class="badge-count">{{ $openJobsCount }} open</span>
                </div>
                <a href="{{ route('jobs.all') }}" class="btn-view-all-header">
                    View All Jobs <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="d-flex gap-2 mb-3" style="flex-wrap:wrap;">
                <a href="{{ route('landing', ['job_type' => 'all']) }}#jobsSection" class="job-type-tab-btn {{ $jobType === 'all' ? 'active' : '' }}">All</a>
                <a href="{{ route('landing', ['job_type' => 'local']) }}#jobsSection" class="job-type-tab-btn {{ $jobType === 'local' ? 'active' : '' }}">Local</a>
                <a href="{{ route('landing', ['job_type' => 'ofw']) }}#jobsSection" class="job-type-tab-btn {{ $jobType === 'ofw' ? 'active' : '' }}">OFW</a>
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
                                <span class="job-badge-deadline">
                                    <i class="bi bi-calendar me-1"></i>Until {{ \Carbon\Carbon::parse($job->deadline)->format('M d, Y') }}
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
                                    '{{ addslashes(strip_tags($job->description ?? '')) }}'
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
                <button class="auth-back-btn" onclick="showHome()"><i class="bi bi-x-lg"></i> Close</button>

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
                            <i class="bi bi-exclamation-circle-fill"></i>
                            {{ $errors->first() }}
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="success-msg">
                            <i class="bi bi-check-circle-fill"></i>
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
                                    <i class="bi bi-eye" id="pw-icon"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn-login">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="{{ route('password.request') }}" style="font-size:12px;color:#90d870;font-weight:600;text-decoration:none;">
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
                            <div style="border:2px solid rgba(255,255,255,0.25);border-radius:16px;padding:20px 14px;text-align:center;">
                                <div style="width:56px;height:56px;background:linear-gradient(135deg,#90d870,#4dd9c0);
                                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                                            margin:0 auto 10px;font-size:24px;color:#fff;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div style="font-size:14px;font-weight:700;color:#fff;">Job Seeker</div>
                                <div style="font-size:10.5px;color:rgba(255,255,255,0.65);margin-top:6px;line-height:1.5;">
                                    Looking for a job? Register here to browse vacancies and apply online.
                                </div>
                            </div>
                        </a>
                        <a href="{{ route('register.company') }}" style="text-decoration:none;flex:1 1 160px;">
                            <div style="border:2px solid rgba(255,255,255,0.25);border-radius:16px;padding:20px 14px;text-align:center;">
                                <div style="width:56px;height:56px;background:linear-gradient(135deg,#90d870,#4dd9c0);
                                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                                            margin:0 auto 10px;font-size:24px;color:#fff;">
                                    <i class="bi bi-building-fill"></i>
                                </div>
                                <div style="font-size:14px;font-weight:700;color:#fff;">Employer</div>
                                <div style="font-size:10.5px;color:rgba(255,255,255,0.65);margin-top:6px;line-height:1.5;">
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
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>2nd Floor New Executive Building, City Hall, Capistrano-Hayes Sts., Cagayan de Oro, Philippines, 9000</span>
                    </div>
                    <a href="https://www.facebook.com/PESOCDO" target="_blank" rel="noopener" class="footer-fb-link" aria-label="PESO CDO Facebook Page">
                        <i class="bi bi-facebook"></i>
                    </a>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Public Employment Service Office — Cagayan de Oro City. All rights reserved.</p>
            </div>
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
                <a href="{{ route('login') }}" class="btn-login" style="text-decoration:none;display:block;text-align:center;">
                    <i class="bi bi-send-fill me-2"></i>Login to Apply
                </a>
            </div>
        </div>

        <script>
            @if(session('success'))
                document.addEventListener('DOMContentLoaded', function() {
                    showAuth('login');
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
                    icon.className = 'bi bi-eye-slash';
                } else {
                    pw.type = 'password';
                    icon.className = 'bi bi-eye';
                }
            }

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