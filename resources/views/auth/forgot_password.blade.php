<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — PESO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --peso-green: #4dd9c0; --peso-light: #90d870; --peso-dark: #2d7a5f; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; font-family: 'Segoe UI', sans-serif;
            background: #0d1f18; display: flex; align-items: center; justify-content: center;
            padding: 24px 14px;
        }
        .auth-box {
            width: 100%; max-width: 420px;
            background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.28);
            border-radius: 20px; padding: 32px 28px;
            backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.35);
        }
        .auth-brand-row { display:flex; align-items:center; justify-content:center; gap:10px; margin-bottom:8px; }
        .auth-logo { width:56px; height:56px; object-fit:contain; }
        .auth-brand-text { color:#fff; font-weight:700; font-size:11px; text-align:left; line-height:1.3; }
        .form-title { font-size:19px; font-weight:800; color:#fff; margin:12px 0 4px; text-align:center; }
        .form-sub { font-size:12.5px; color:rgba(255,255,255,0.75); margin-bottom:22px; text-align:center; }
        .peso-label { font-size:11px; font-weight:700; color:rgba(255,255,255,0.9); margin-bottom:6px; display:block; letter-spacing:0.3px; text-transform:uppercase; }
        .peso-input {
            width:100%; border:1.5px solid rgba(255,255,255,0.35); border-radius:10px;
            font-size:13px; padding:10px 14px; color:#fff; background:rgba(255,255,255,0.08); outline:none;
        }
        .peso-input::placeholder { color:rgba(255,255,255,0.55); }
        .peso-input:focus { border-color:#90d870; box-shadow:0 0 0 3px rgba(144,216,112,0.25); background:rgba(255,255,255,0.14); }
        .btn-login {
            width:100%; background:linear-gradient(90deg,#90d870,#4dd9c0); border:none; color:#0f2e24;
            font-weight:700; border-radius:10px; padding:11px; font-size:13.5px; cursor:pointer;
            box-shadow:0 4px 20px rgba(77,217,192,0.4); margin-top:6px;
        }
        .btn-login:hover { opacity:0.92; }
        .error-msg {
            background:rgba(198,40,40,0.25); border:1px solid rgba(198,40,40,0.4); color:#ffd6d6;
            border-radius:10px; padding:9px 12px; font-size:11.5px; margin-bottom:14px; display:flex; align-items:center; gap:8px;
        }
        .success-msg {
            background:rgba(77,217,192,0.2); border:1px solid rgba(77,217,192,0.4); color:#d9fff5;
            border-radius:10px; padding:9px 12px; font-size:11.5px; margin-bottom:14px; display:flex; align-items:center; gap:8px;
        }
        .back-link { text-align:center; margin-top:18px; }
        .back-link a { color:#90d870; font-size:12px; font-weight:600; text-decoration:none; }
        .back-link a:hover { color:#fff; }
    </style>
</head>
<body>
    <div class="auth-box">
        <div class="auth-brand-row">
            <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo" class="auth-logo">
            <div class="auth-brand-text">PUBLIC EMPLOYMENT SERVICE OFFICE<br>A Web-based Job Management System</div>
        </div>
        <div class="form-title">Forgot Password?</div>
        <div class="form-sub">Enter your email and we'll send you a verification code</div>

        @if($errors->any())
            <div class="error-msg"><i class="bi bi-exclamation-circle-fill"></i>{{ $errors->first() }}</div>
        @endif
        @if(session('success'))
            <div class="success-msg"><i class="bi bi-check-circle-fill"></i>{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label class="peso-label">Email Address</label>
            <input type="email" name="email" class="peso-input" placeholder="Enter your registered email"
                   value="{{ old('email') }}" required>
            <button type="submit" class="btn-login">
                <i class="bi bi-send-fill me-2"></i>Send Verification Code
            </button>
        </form>

        <div class="back-link">
            <a href="{{ route('login') }}"><i class="bi bi-arrow-left me-1"></i>Back to Login</a>
        </div>
    </div>
</body>
</html>