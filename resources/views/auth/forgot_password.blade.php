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
        body {
            margin: 0; min-height: 100vh; font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: var(--g-800); display: flex; align-items: center; justify-content: center;
            padding: 24px 14px;
        }
        .auth-box {
            width: 100%; max-width: 420px;
            background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.3);
            border-radius: 20px; padding: 32px 28px;
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .auth-brand-row { display:flex; align-items:center; justify-content:center; gap:10px; margin-bottom:8px; }
        .auth-logo { width:56px; height:56px; object-fit:contain; }
        .auth-brand-text { color: #fff; font-weight:700; font-size:11px; text-align:left; line-height:1.3; }
        .form-title { font-size:19px; font-weight:800; color: #fff; margin:12px 0 4px; text-align:center; }
        .form-sub { font-size:12.5px; color:rgba(255,255,255,0.8); margin-bottom:22px; text-align:center; }
        .peso-label { font-size:11px; font-weight:700; color: rgba(255,255,255,0.9); margin-bottom:6px; display:block; letter-spacing:0.3px; text-transform:uppercase; }
        .peso-input {
            width:100%; border:1px solid rgba(255,255,255,0.4); border-radius:10px;
            font-size:13px; padding:10px 14px; color: #fff; background:rgba(255,255,255,0.1); outline:none;
        }
        .peso-input::placeholder { color:rgba(255,255,255,0.6); }
        .peso-input:focus { border-color: #fff; box-shadow:0 0 0 3px rgba(255,255,255,0.2); background:rgba(255,255,255,0.16); }
        .btn-login {
            width:100%; background:#fff; border:none; color: var(--g-700);
            font-weight:700; border-radius:10px; padding:11px; font-size:13.5px; cursor:pointer;
            box-shadow:0 4px 20px rgba(0,0,0,0.2); margin-top:6px;
        }
        .btn-login:hover { opacity:0.92; }
        .error-msg {
            background:rgba(198,40,40,0.25); border:1px solid rgba(198,40,40,0.45); color:var(--danger-br);
            border-radius:10px; padding:9px 12px; font-size:11.5px; margin-bottom:14px; display:flex; align-items:center; gap:8px;
        }
        .success-msg {
            background:rgba(255,255,255,0.16); border:1px solid rgba(255,255,255,0.4); color:#fff;
            border-radius:10px; padding:9px 12px; font-size:11.5px; margin-bottom:14px; display:flex; align-items:center; gap:8px;
        }
        .back-link { text-align:center; margin-top:18px; }
        .back-link a { color: #fff; font-size:12px; font-weight:700; text-decoration:underline; }
        .back-link a:hover { color: rgba(255,255,255,0.8); }

        /* Account-type picker */
        .pick-card {
            display:flex; align-items:center; gap:14px; width:100%;
            padding:16px 18px; margin-bottom:12px; border-radius:12px;
            background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.28);
            text-decoration:none; transition:background .15s, border-color .15s;
        }
        .pick-card:hover { background:rgba(255,255,255,0.2); border-color:#fff; }
        .pick-card i { font-size:26px; color:#fff; flex-shrink:0; }
        .pick-title { font-size:15px; font-weight:800; color:#fff; }
        .pick-sub { font-size:11.5px; color:rgba(255,255,255,0.75); }

        /* Masked email / phone panel */
        .found-box {
            background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.3);
            border-radius:12px; padding:16px 18px; margin-bottom:14px;
        }
        .found-label {
            font-size:10.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase;
            color:rgba(255,255,255,0.7); margin-bottom:4px;
        }
        .found-company { font-size:15px; font-weight:800; color:#fff; margin-bottom:6px; }
        .found-email {
            font-family:ui-monospace, Menlo, Consolas, monospace;
            font-size:15px; font-weight:700; color:#fff; letter-spacing:.5px;
            word-break:break-all; margin-bottom:10px;
        }
        .found-hint { font-size:11.5px; color:rgba(255,255,255,0.8); line-height:1.6; }

        .btn-secondary-link {
            display:block; width:100%; text-align:center; margin-top:10px;
            padding:11px 14px; border-radius:10px; text-decoration:none;
            background:transparent; border:1px solid rgba(255,255,255,0.4);
            color:#fff; font-size:12.5px; font-weight:700;
        }
        .btn-secondary-link:hover { background:rgba(255,255,255,0.14); color:#fff; }
    </style>
</head>
<body>
    <div class="auth-box">
        <div class="auth-brand-row">
            <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo" class="auth-logo">
            <div class="auth-brand-text">PUBLIC EMPLOYMENT SERVICE OFFICE<br>A Web-based Job Management System</div>
        </div>
        @php
            // as = 'pick' | 'jobseeker' | 'employer'
            $as = request('as');
            $as = in_array($as, ['jobseeker', 'employer'], true) ? $as : 'pick';
        @endphp

        @if($as === 'pick')
            <div class="form-title">Forgot Password?</div>
            <div class="form-sub">First, tell us what kind of account you are recovering</div>
        @elseif($as === 'jobseeker')
            <div class="form-title">Jobseeker Account</div>
            <div class="form-sub">Enter your email and we'll send you a verification code</div>
        @else
            <div class="form-title">Employer Account</div>
            <div class="form-sub">Search by company name — you do not need to remember the email</div>
        @endif

        @if($errors->any())
            <div class="error-msg"><i class="ph-fill ph-warning-circle"></i>{{ $errors->first() }}</div>
        @endif
        @if(session('success'))
            <div class="success-msg"><i class="ph-fill ph-check-circle"></i>{{ session('success') }}</div>
        @endif

        {{-- ══════════ STEP 1 — PICK THE ACCOUNT TYPE ══════════ --}}
        @if($as === 'pick')

            <a href="{{ route('password.request', ['as' => 'jobseeker']) }}" class="pick-card">
                <i class="ph-fill ph-user"></i>
                <div>
                    <div class="pick-title">Jobseeker</div>
                    <div class="pick-sub">You applied for jobs through PESO</div>
                </div>
            </a>

            <a href="{{ route('password.request', ['as' => 'employer']) }}" class="pick-card">
                <i class="ph-fill ph-buildings"></i>
                <div>
                    <div class="pick-title">Employer</div>
                    <div class="pick-sub">Your company posts job vacancies</div>
                </div>
            </a>

        {{-- ══════════ JOBSEEKER — email, sama ra sa kaniadto ══════════ --}}
        @elseif($as === 'jobseeker')

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <label class="peso-label">Email Address</label>
                <input type="email" name="email" class="peso-input" placeholder="Enter your registered email"
                       value="{{ old('email') }}" required>
                <button type="submit" class="btn-login">
                    <i class="ph-fill ph-paper-plane-tilt me-2"></i>Send Verification Code
                </button>
            </form>

        {{-- ══════════ EMPLOYER ══════════ --}}
        @else

            {{-- Nakit-an ang kompanya: ipakita ang tinabunan nga email aron
                 mailhan sa bag-ong HR kung iyaha ba o iya sa ni-hawa nga HR.
                 Ang tinuod nga address wala gyud gi-render — ang pagpadala
                 mo-pangita pag-usab gikan sa company name sa server. --}}
            @if(session('recovery_company'))
                <div class="found-box">
                    <div class="found-label">Account found</div>
                    <div class="found-company">{{ session('recovery_company') }}</div>
                    <div class="found-email">{{ session('recovery_masked_email') }}</div>
                    <div class="found-hint">
                        Is this an inbox you can open? If the officer who registered
                        the company has left and this was their personal email, choose
                        the second option.
                    </div>
                </div>

                <form method="POST" action="{{ route('password.company.send') }}">
                    @csrf
                    <input type="hidden" name="company_name" value="{{ session('recovery_company') }}">
                    <button type="submit" class="btn-login">
                        <i class="ph-fill ph-paper-plane-tilt me-2"></i>Yes, send the code there
                    </button>
                </form>

                <a href="{{ route('password.request', ['as' => 'employer', 'stuck' => 1]) }}" class="btn-secondary-link">
                    <i class="ph ph-phone me-1"></i>I can't access this email
                </a>

            {{-- Wala na siyay maabot nga inbox — tawag na lang sa opisina. --}}
            @elseif(request('stuck'))
                <div class="found-box">
                    <div class="found-label">Call the PESO office</div>
                    <div class="found-company">{{ config('peso.office.contact_number') }}</div>
                    <div class="found-hint">
                        {{ config('peso.office.hours') }}
                        <br><br>
                        PESO staff will verify that you are the company's new authorised
                        contact, then set a temporary password for you over the phone.
                        You will be asked to change it the moment you log in.
                        <br><br>
                        Have ready: the company name, your company ID, and a letter or
                        endorsement showing you are the new contact person.
                    </div>
                </div>

                <a href="{{ route('password.request', ['as' => 'employer']) }}" class="btn-secondary-link">
                    <i class="ph ph-arrow-left me-1"></i>Search for a company again
                </a>

            {{-- Unang lakang: ang pangalan sa kompanya. --}}
            @else
                <form method="POST" action="{{ route('password.company.lookup') }}">
                    @csrf
                    <label class="peso-label">Company Name</label>
                    <input type="text" name="company_name" class="peso-input"
                           placeholder="e.g. Northline Manufacturing"
                           value="{{ old('company_name') }}" required autofocus>
                    <button type="submit" class="btn-login">
                        <i class="ph-fill ph-magnifying-glass me-2"></i>Find My Account
                    </button>
                </form>
            @endif

        @endif

        <div class="back-link">
            @if($as === 'pick')
                <a href="{{ route('login') }}"><i class="ph ph-arrow-left me-1"></i>Back to Login</a>
            @else
                <a href="{{ route('password.request') }}"><i class="ph ph-arrow-left me-1"></i>Choose a different account type</a>
            @endif
        </div>
    </div>
</body>
</html>