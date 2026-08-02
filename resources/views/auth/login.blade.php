<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PESO System — Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }

            html, body {
                min-height: 100vh;
                font-family: 'Segoe UI', sans-serif;
            }

            /* ── FULL BACKGROUND ── */
            .bg-wrapper {
                position: fixed;
                inset: 0;
                background: url('{{ asset('images/cityhall.png') }}') center center / cover no-repeat;
                z-index: 0;
            }
            .bg-overlay {
                position: fixed;
                inset: 0;
                background:
                    linear-gradient(180deg, rgba(15,46,36,0.55) 0%, rgba(15,46,36,0.72) 60%, rgba(10,32,25,0.85) 100%);
                z-index: 1;
            }

            /* ── PAGE LAYOUT ── */
            .page {
                position: relative;
                z-index: 2;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 40px 20px;
            }

            .brand-header {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 12px;
                margin-bottom: 28px;
                animation: fadeInUp 0.6s ease forwards;
            }
            .brand-header img {
                width: 84px; height: 84px;
                object-fit: contain;
                flex-shrink: 0;
                filter: drop-shadow(0 4px 12px rgba(0,0,0,0.35));
            }
            .brand-header .brand-text {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                justify-content: center;
                text-align: left;
            }
            .brand-header h1 {
                font-size: 19px;
                font-weight: 800;
                color: #fff;
                line-height: 1.2;
                text-shadow: 0 2px 12px rgba(0,0,0,0.35);
                margin: 0;
            }
            .brand-header p {
                font-size: 12.5px;
                color: rgba(255,255,255,0.95);
                margin-top: 4px;
                letter-spacing: 0.2px;
                line-height: 1.2;
            }

            /* ── GLASS CARD ── */
            .glass-card {
                width: 100%;
                max-width: 400px;
                background: rgba(255,255,255,0.12);
                border: 1px solid rgba(255,255,255,0.28);
                border-radius: 20px;
                padding: 36px 32px;
                backdrop-filter: blur(18px);
                -webkit-backdrop-filter: blur(18px);
                box-shadow: 0 20px 60px rgba(0,0,0,0.35);
                animation: fadeInUp 0.6s ease 0.1s forwards;
                opacity: 0;
            }

            .form-title {
                font-size: 19px; font-weight: 800;
                color: #fff; margin-bottom: 4px;
                text-align: center;
            }
            .form-sub {
                font-size: 12.5px; color: rgba(255,255,255,0.75);
                margin-bottom: 24px;
                text-align: center;
            }

            .peso-label {
                font-size: 11.5px; font-weight: 700;
                color: rgba(255,255,255,0.9);
                margin-bottom: 6px;
                display: block;
                letter-spacing: 0.3px;
                text-transform: uppercase;
            }
            .peso-input {
                width: 100%;
                border: 1.5px solid rgba(255,255,255,0.35);
                border-radius: 10px;
                font-size: 13px;
                padding: 11px 14px;
                color: #fff;
                background: rgba(255,255,255,0.08);
                transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
                outline: none;
            }
            .peso-input::placeholder { color: rgba(255,255,255,0.55); }
            .peso-input:focus {
                border-color: #90d870;
                box-shadow: 0 0 0 3px rgba(144,216,112,0.25);
                background: rgba(255,255,255,0.14);
            }
            .input-group-custom { position: relative; }
            .input-group-custom .peso-input { padding-right: 42px; }
            .input-group-custom .toggle-pw {
                position: absolute; right: 12px; top: 50%;
                transform: translateY(-50%);
                background: none; border: none;
                color: rgba(255,255,255,0.75); cursor: pointer;
                font-size: 16px; padding: 0;
            }

            .btn-login {
                width: 100%;
                background: linear-gradient(90deg, #90d870, #4dd9c0);
                border: none; color: #0f2e24;
                font-weight: 700; border-radius: 10px;
                padding: 12px; font-size: 14px;
                cursor: pointer;
                transition: opacity 0.2s, transform 0.2s;
                box-shadow: 0 4px 20px rgba(77,217,192,0.4);
                margin-top: 6px;
            }
            .btn-login:hover { opacity: 0.92; transform: translateY(-1px); }

            .error-msg {
                background: rgba(198,40,40,0.25);
                border: 1px solid rgba(198,40,40,0.4);
                color: #ffd6d6;
                border-radius: 10px;
                padding: 10px 14px; font-size: 12px;
                margin-bottom: 16px;
                display: flex; align-items: center; gap: 8px;
            }
            .success-msg {
                background: rgba(77,217,192,0.2);
                border: 1px solid rgba(77,217,192,0.4);
                color: #d9fff5;
                border-radius: 10px;
                padding: 10px 14px; font-size: 12px;
                margin-bottom: 16px;
                display: flex; align-items: center; gap: 8px;
            }

            .footer-note {
                text-align: center; font-size: 11px;
                color: rgba(255,255,255,0.6);
                margin-top: 18px;
            }
            .footer-note a { color: #90d870; font-weight: 600; text-decoration: none; }

            .register-link-row {
                text-align: center;
                margin-top: 18px;
            }
            .register-link-row span { font-size: 12.5px; color: rgba(255,255,255,0.75); }
            .register-link-row button {
                background: none; border: none;
                color: #90d870; font-weight: 700;
                font-size: 12.5px; cursor: pointer;
                margin-left: 4px;
            }

            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(18px); }
                to   { opacity: 1; transform: translateY(0); }
            }
        </style>
    </head>
    <body>

        {{-- ── FULL BACKGROUND PHOTO + OVERLAY ── --}}
        <div class="bg-wrapper"></div>
        <div class="bg-overlay"></div>

        {{-- ── PAGE CONTENT ── --}}
        <div class="page">

            <div class="brand-header">
                <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo">
                <div class="brand-text">
                    <h1>A Web-based Job Management System<br>for PESO CDO</h1>
                    <p>Public Employment Service Office — Connecting talent with opportunity across Cagayan de Oro.</p>
                </div>
            </div>

            <div class="glass-card">
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
                            value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="mb-4">
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

                <div class="register-link-row">
                    <span>Don't have an account?</span>
                    <button onclick="document.getElementById('registerModal').style.display='flex'">
                        Register here
                    </button>
                </div>

                @if(!App\Models\User::where('role', 'admin')->exists())
                    <div class="footer-note">
                        No admin yet? <a href="{{ route('admin.register') }}">Register here</a>
                    </div>
                @endif
            </div>

            <div class="footer-note">© {{ date('Y') }} Decierdo · Tagarao · Rivas · Santizo — All rights reserved</div>

        </div>

        <script>
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
        </script>

{{-- REGISTER MODAL --}}
<div id="registerModal"
    style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;
           background:rgba(0,0,0,0.6);z-index:999;
           align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:20px;padding:40px;
                max-width:480px;width:90%;position:relative;
                box-shadow:0 20px 60px rgba(0,0,0,0.3);">

        <button onclick="document.getElementById('registerModal').style.display='none'"
            style="position:absolute;top:16px;right:16px;
                   background:none;border:none;font-size:20px;
                   color:#888;cursor:pointer;">
            <i class="bi bi-x-lg"></i>
        </button>

        <div style="text-align:center;margin-bottom:28px;">
            <div style="font-size:20px;font-weight:800;color:#2d7a5f;">Create Account</div>
            <div style="font-size:13px;color:#888;margin-top:4px;">Choose your account type to get started</div>
        </div>

        <div style="display:flex;gap:16px;">
            <a href="{{ route('register.jobseeker') }}" style="text-decoration:none;flex:1;">
                <div style="border:2px solid #a8e6cf;border-radius:16px;
                            padding:24px 16px;text-align:center;transition:all 0.2s;"
                    onmouseover="this.style.borderColor='#4dd9c0';this.style.background='#f0f9f6';this.style.transform='translateY(-4px)'"
                    onmouseout="this.style.borderColor='#a8e6cf';this.style.background='#fff';this.style.transform='translateY(0)'">
                    <div style="width:64px;height:64px;background:linear-gradient(135deg,#90d870,#4dd9c0);
                                border-radius:50%;display:flex;align-items:center;justify-content:center;
                                margin:0 auto 12px;font-size:28px;color:#fff;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div style="font-size:15px;font-weight:700;color:#2d7a5f;">Job Seeker</div>
                    <div style="font-size:11px;color:#888;margin-top:6px;line-height:1.5;">
                        Looking for a job? Register here to browse vacancies and apply online.
                    </div>
                </div>
            </a>

            <a href="{{ route('register.company') }}" style="text-decoration:none;flex:1;">
                <div style="border:2px solid #a8e6cf;border-radius:16px;
                            padding:24px 16px;text-align:center;transition:all 0.2s;"
                    onmouseover="this.style.borderColor='#4dd9c0';this.style.background='#f0f9f6';this.style.transform='translateY(-4px)'"
                    onmouseout="this.style.borderColor='#a8e6cf';this.style.background='#fff';this.style.transform='translateY(0)'">
                    <div style="width:64px;height:64px;background:linear-gradient(135deg,#90d870,#4dd9c0);
                                border-radius:50%;display:flex;align-items:center;justify-content:center;
                                margin:0 auto 12px;font-size:28px;color:#fff;">
                        <i class="bi bi-building-fill"></i>
                    </div>
                    <div style="font-size:15px;font-weight:700;color:#2d7a5f;">Employer</div>
                    <div style="font-size:11px;color:#888;margin-top:6px;line-height:1.5;">
                        Hiring? Register your company and post job vacancies on PESO.
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

</body>
</html>