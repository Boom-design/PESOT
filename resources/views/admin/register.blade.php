<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PESO Admin Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: linear-gradient(160deg, #f0f7a0 0%, #a8e6cf 45%, #4dd9c0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .shape { position: absolute; border-radius: 50%; z-index: 0; }
        .shape1 { width: 400px; height: 400px; background: rgba(255,255,160,0.18); top: -120px; left: -120px; }
        .shape2 { width: 320px; height: 320px; background: rgba(77,217,192,0.18); bottom: -80px; right: -80px; }
        .shape3 { width: 220px; height: 220px; background: rgba(168,230,207,0.22); top: 40%; left: 60%; }
        .card {
            position: relative;
            z-index: 2;
            background: rgba(255,255,255,0.88);
            border-radius: 20px;
            padding: 36px 32px 28px;
            width: 100%;
            max-width: 420px;
            border: 1px solid rgba(255,255,255,0.7);
        }
        .logo-ring {
            width: 96px; height: 96px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f0f7a0, #4dd9c0);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 12px;
            border: 3px solid rgba(255,255,255,0.9);
        }
        .logo-ring img {
            width: 82px; height: 82px;
            border-radius: 50%;
            object-fit: cover;
        }
        .system-title {
            font-size: 18px;
            font-weight: 600;
            color: #1a4a3a;
            text-align: center;
        }
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #a8e6cf, transparent);
            margin: 16px 0;
        }
        .input-wrap {
            display: flex;
            align-items: center;
            border: 1.5px solid #a8e6cf;
            border-radius: 10px;
            background: rgba(255,255,255,0.85);
            margin-bottom: 14px;
            padding: 0 14px;
            gap: 10px;
        }
        .input-wrap i { color: #2d8a6a; font-size: 17px; }
        .input-wrap input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 12px 0;
            font-size: 14px;
            color: #1a3c2a;
            outline: none;
        }
        .input-wrap input::placeholder { color: #aaa; font-size: 13px; }
        .toggle-eye {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            color: #2d8a6a;
            font-size: 17px;
            display: flex;
            align-items: center;
        }
        .btn-register {
            width: 100%;
            padding: 13px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(90deg, #90d870, #4dd9c0);
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 6px;
            letter-spacing: 0.5px;
            transition: opacity 0.2s;
        }
        .btn-register:hover { opacity: 0.88; }
        .footer-text {
            text-align: center;
            margin-top: 16px;
            font-size: 13px;
            color: #5a8a6a;
        }
        .footer-text a { color: #2d8a6a; font-weight: 600; text-decoration: none; }
        .alert-danger { border-radius: 10px; font-size: 13px; margin-bottom: 14px; }
    </style>
</head>
<body>

    <div class="shape shape1"></div>
    <div class="shape shape2"></div>
    <div class="shape shape3"></div>

    <div class="card">

        <div class="logo-ring">
            <img src="{{ asset('images/peso_logo.png') }}" alt="PESO Logo"
                 onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=\'font-size:13px;font-weight:700;color:#1a4a3a;text-align:center;line-height:1.3;\'>P.E.S.O<br>CDO</span>'">
        </div>

        <div class="system-title">CREATE ADMIN ACCOUNT</div>

        <div class="divider"></div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.register') }}" method="POST">
            @csrf

            {{-- Full Name --}}
            <div class="input-wrap">
                <i class="bi bi-person"></i>
                <input type="text" name="name" placeholder="Full Name"
                       value="{{ old('name') }}" required>
            </div>

            {{-- Email --}}
            <div class="input-wrap">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" placeholder="Email Address"
                       value="{{ old('email') }}" required>
            </div>

            {{-- Phone --}}
            <div class="input-wrap">
                <i class="bi bi-phone"></i>
                <input type="tel" name="phone" placeholder="Phone Number"
                       value="{{ old('phone') }}" required>
            </div>

            {{-- Password --}}
            <div class="input-wrap">
                <i class="bi bi-lock"></i>
                <input type="password" name="password" id="password"
                       placeholder="Password" required>
                <button type="button" class="toggle-eye" onclick="togglePass('password', 'eyeIcon1')">
                    <i class="bi bi-eye-slash" id="eyeIcon1"></i>
                </button>
            </div>

            {{-- Confirm Password --}}
            <div class="input-wrap">
                <i class="bi bi-lock-fill"></i>
                <input type="password" name="password_confirmation" id="confirmPassword"
                       placeholder="Re-type Password" required>
                <button type="button" class="toggle-eye" onclick="togglePass('confirmPassword', 'eyeIcon2')">
                    <i class="bi bi-eye-slash" id="eyeIcon2"></i>
                </button>
            </div>

            <button type="submit" class="btn-register">Create Admin Account</button>
        </form>

        <div class="footer-text">
            Already have an account? <a href="{{ route('admin.login') }}">Sign In</a>
        </div>

    </div>

    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        }
    </script>

</body>
</html>