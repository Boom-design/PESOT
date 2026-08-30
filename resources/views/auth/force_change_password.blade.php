{{--
    Ang employer nga gihatagan ug temporary password sa telepono. Walay laing
    page nga maabot niya hangtod mo-ilis siya — ang EnsurePasswordChanged nga
    middleware maoy nagpugos.

    Walay "Back to Login" dinhi: ang bugtong gawasanan mao ang pag-set ug bag-o
    o ang logout.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.head-brand')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css" rel="stylesheet">
    <link href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/fill/style.css" rel="stylesheet">
    <style>
        body {
            margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center;
            background: linear-gradient(135deg, #1D6F27 0%, #28812F 100%);
            font-family: 'Segoe UI', system-ui, sans-serif; padding:24px;
        }
        .auth-box {
            width:100%; max-width:430px; background:rgba(255,255,255,0.1);
            border:1px solid rgba(255,255,255,0.25); border-radius:18px;
            padding:30px 28px; backdrop-filter:blur(10px);
        }
        .auth-brand-row { display:flex; align-items:center; gap:12px; justify-content:center; margin-bottom:6px; }
        .auth-logo { width:44px; height:44px; object-fit:contain; }
        .auth-brand-text { font-size:10.5px; font-weight:700; color:rgba(255,255,255,0.85); line-height:1.4; }
        .form-title { font-size:19px; font-weight:800; color:#fff; margin:12px 0 4px; text-align:center; }
        .form-sub { font-size:12.5px; color:rgba(255,255,255,0.8); margin-bottom:20px; text-align:center; line-height:1.6; }
        .peso-label {
            font-size:11px; font-weight:700; color:rgba(255,255,255,0.9); margin-bottom:6px;
            display:block; letter-spacing:.3px; text-transform:uppercase;
        }
        .peso-input {
            width:100%; padding:11px 14px; border-radius:10px; font-size:14px; color:#fff;
            background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.3); margin-bottom:14px;
        }
        .peso-input::placeholder { color:rgba(255,255,255,0.6); }
        .peso-input:focus {
            outline:none; border-color:#fff; background:rgba(255,255,255,0.16);
            box-shadow:0 0 0 3px rgba(255,255,255,0.2);
        }
        .btn-login {
            width:100%; padding:12px; border:none; border-radius:10px; background:#fff;
            color:#1D6F27; font-size:14px; font-weight:800; cursor:pointer;
        }
        .btn-login:hover { background:rgba(255,255,255,0.9); }
        .error-msg, .notice {
            border-radius:10px; padding:11px 14px; font-size:12.5px; margin-bottom:14px; line-height:1.6;
        }
        .error-msg { background:rgba(220,53,69,0.22); border:1px solid rgba(255,140,150,0.6); color:#fff; }
        .notice    { background:rgba(255,255,255,0.14); border:1px solid rgba(255,255,255,0.3); color:rgba(255,255,255,0.92); }
        .logout-row { text-align:center; margin-top:16px; }
        .logout-row button {
            background:none; border:none; color:#fff; font-size:12px; font-weight:700;
            text-decoration:underline; cursor:pointer;
        }
    </style>
</head>
<body>
    <div class="auth-box">
        <div class="auth-brand-row">
            <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo" class="auth-logo">
            <div class="auth-brand-text">PUBLIC EMPLOYMENT SERVICE OFFICE<br>A Web-based Job Management System</div>
        </div>

        <div class="form-title">Set Your Own Password</div>
        <div class="form-sub">
            You signed in with a temporary password given to you by PESO.
            Choose your own now — until you do, you cannot go anywhere else.
        </div>

        @if($errors->any())
            <div class="error-msg"><i class="ph-fill ph-warning-circle me-1"></i>{{ $errors->first() }}</div>
        @endif

        <div class="notice">
            <i class="ph-fill ph-info me-1"></i>
            PESO staff know the temporary password because they read it to you.
            Once you save a new one, only you will know it.
        </div>

        <form method="POST" action="{{ route('password.force.update') }}">
            @csrf
            <label class="peso-label">New Password</label>
            <input type="password" name="password" class="peso-input"
                   placeholder="At least 8 characters" required autofocus>
            @include('partials.password-hint', ['onDark' => true])

            <label class="peso-label">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="peso-input"
                   placeholder="Type it again" required>

            <button type="submit" class="btn-login">
                <i class="ph-fill ph-check-circle me-2"></i>Save New Password
            </button>
        </form>

        <div class="logout-row">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"><i class="ph ph-sign-out me-1"></i>Sign out instead</button>
            </form>
        </div>
    </div>
</body>
</html>
