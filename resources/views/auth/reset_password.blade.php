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
        .form-sub strong { color: #fff; }
        .peso-label { font-size:11px; font-weight:700; color: rgba(255,255,255,0.9); margin-bottom:6px; display:block; letter-spacing:0.3px; text-transform:uppercase; }
        .peso-input {
            width:100%; border:1px solid rgba(255,255,255,0.4); border-radius:10px;
            font-size:13px; padding:10px 14px; color: #fff; background:rgba(255,255,255,0.1); outline:none;
        }
        .peso-input::placeholder { color:rgba(255,255,255,0.6); }
        .peso-input:focus { border-color: #fff; box-shadow:0 0 0 3px rgba(255,255,255,0.2); background:rgba(255,255,255,0.16); }
        .code-input {
            text-align:center; font-size:22px; letter-spacing:10px; font-weight:700;
        }
        .input-group-custom { position:relative; }
        .input-group-custom .peso-input { padding-right:42px; }
        .input-group-custom .toggle-pw {
            position:absolute; right:10px; top:50%; transform:translateY(-50%);
            background:none; border:none; color:rgba(255,255,255,0.8); cursor:pointer; font-size:15px; padding:0;
        }
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
        .mb-3 { margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="auth-box">
        <div class="auth-brand-row">
            <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo" class="auth-logo">
            <div class="auth-brand-text">PUBLIC EMPLOYMENT SERVICE OFFICE<br>A Web-based Job Management System</div>
        </div>
        <div class="form-title">Reset Password</div>
        {{-- Ang employer nga agianan nangita pinaagi sa pangalan sa kompanya —
             wala niya kabalo ang address, ug dili siya angay makahibalo dinhi.
             Tinabunan ang gipakita, ug ang tinuod naa sa session. --}}
        @php $maskedOnly = $maskedOnly ?? false; @endphp
        <div class="form-sub">Enter the code sent to <strong>{{ $shownEmail ?? $email }}</strong></div>

        @if($errors->any())
            <div class="error-msg"><i class="ph-fill ph-warning-circle"></i>{{ $errors->first() }}</div>
        @endif
        @if(session('success'))
            <div class="success-msg"><i class="ph-fill ph-check-circle"></i>{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @unless($maskedOnly)
                <input type="hidden" name="email" value="{{ $email }}">
            @endunless

            <div class="mb-3">
                <label class="peso-label">Verification Code</label>
                <input type="text" name="code" class="peso-input code-input" placeholder="000000"
                       maxlength="6" inputmode="numeric" pattern="[0-9]*" required>
            </div>

            <div class="mb-3">
                <label class="peso-label">New Password</label>
                <div class="input-group-custom">
                    <input type="password" name="password" id="password" class="peso-input"
                           placeholder="Set a strong password" required minlength="8">
                    <button type="button" class="toggle-pw" onclick="togglePw('password','pw-icon-1')">
                        <i class="ph ph-eye" id="pw-icon-1"></i>
                    </button>
                </div>
                @include('partials.password-hint', ['onDark' => true])
            </div>

            <div class="mb-3">
                <label class="peso-label">Confirm New Password</label>
                <div class="input-group-custom">
                    <input type="password" name="password_confirmation" id="password_confirmation" class="peso-input"
                           placeholder="Re-enter new password" required minlength="8">
                    <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','pw-icon-2')">
                        <i class="ph ph-eye" id="pw-icon-2"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="ph-fill ph-check-circle me-2"></i>Reset Password
            </button>
        </form>

        <form method="POST" action="{{ $maskedOnly ? route('password.company.send') : route('password.email') }}" style="margin-top:10px;">
            @csrf
            @if($maskedOnly)
                {{-- Ang resend mo-agi pag-usab sa company name, dili sa address. --}}
                <input type="hidden" name="company_name" value="{{ session('recovery_company') }}">
            @else
                <input type="hidden" name="email" value="{{ $email }}">
            @endif
            <button type="submit" class="btn btn-sm w-100"
                style="background:transparent;border:1px solid rgba(255,255,255,0.5);color:#fff;border-radius:10px;font-size:12px;padding:8px;">
                <i class="ph ph-arrows-clockwise me-1"></i>Resend Code
            </button>
        </form>

        <div class="back-link">
            <a href="{{ route('login') }}"><i class="ph ph-arrow-left me-1"></i>Back to Login</a>
        </div>
    </div>

    <script>
        function togglePw(fieldId, iconId) {
            const pw = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            if (pw.type === 'password') {
                pw.type = 'text';
                icon.className = 'ph ph-eye-slash';
            } else {
                pw.type = 'password';
                icon.className = 'ph ph-eye';
            }
        }
    </script>
</body>
</html>