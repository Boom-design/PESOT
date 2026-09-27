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
        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { min-height: 100vh; font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; background: #1a1a1a; }

        .bg-wrapper {
            position: fixed; inset: 0;
            background: url('{{ asset('images/cityhall.jpg') }}') center center / cover no-repeat;
            /* Kaugalingon nga layer sa GPU. Kung wala, ang fixed nga layer
               ipintal pag-usab kada frame sa pag-scroll. */
            transform: translateZ(0);
            will-change: transform;
            z-index: 0;
        }
        .bg-overlay {
            position: fixed; inset: 0;
            background: linear-gradient(180deg, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.68) 100%);
            z-index: 1;
            /* Parehas nga rason sa .bg-wrapper. */
            transform: translateZ(0);
            will-change: transform;
        }

        .page {
            position: relative; z-index: 2;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px 12px;
        }

        .card-register {
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 18px;
            padding: 24px 20px; width: 100%; max-width: 860px;
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 24px 70px rgba(0,0,0,0.3);
            animation: fadeInUp 0.5s ease forwards;
        }

        @media (min-width: 768px) {
            .page { padding: 40px 20px; }
            .card-register { padding: 40px 48px; border-radius: 24px; }
        }

        .peso-label { font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.92); margin-bottom: 6px; display: block; }
        .peso-input {
            width: 100%; border: 1px solid rgba(255,255,255,0.4); border-radius: 10px;
            font-size: 13px; padding: 11px 14px; color: #fff;
            transition: border-color 0.2s; outline: none; background: rgba(255,255,255,0.1);
        }
        .peso-input::placeholder { color: rgba(255,255,255,0.6); }
        .peso-input:focus { border-color: #fff; box-shadow: 0 0 0 3px rgba(255,255,255,0.22); background: rgba(255,255,255,0.16); }
        .input-wrap { position: relative; }
        .input-wrap .peso-input { padding-right: 40px; }
        .toggle-pw {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; color: rgba(255,255,255,0.7);
            cursor: pointer; font-size: 15px; padding: 0;
        }
        .toggle-pw:hover { color: #fff; }
        .btn-register {
            width: 100%; background: #fff;
            border: none; color: var(--g-700); font-weight: 700; border-radius: 10px;
            padding: 12px; font-size: 14px; cursor: pointer;
            transition: opacity 0.2s; margin-top: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        }
        .btn-register:hover { opacity: 0.9; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        /* ══════════════════════════════════════════════════════════════
           SOLID WHITE CARD
           PESO CDO, 2026-09-14: the registration card was see-through glass
           over the City Hall photo, and the forms were hard to read. The card
           is plain white now, with dark text. The photo behind it stays.
           Written as overrides at the end so the rules above keep their
           layout; !important is needed where the markup carries inline
           white colours.
           ══════════════════════════════════════════════════════════════ */
        .card-register {
            background: #fff !important;
            border: 1px solid var(--n-200) !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            color: var(--n-700);
        }
        .peso-label { color: var(--g-700) !important; }
        .peso-input { background: #fff !important; border-color: var(--n-200) !important; color: #1f2937 !important; }
        .peso-input::placeholder { color: var(--n-400) !important; }
        .peso-input:focus { border-color: var(--g-600) !important; box-shadow: 0 0 0 3px rgba(22, 128, 61, 0.15) !important; background: #fff !important; }
        .peso-input[readonly] { background: var(--n-50) !important; color: var(--n-500) !important; }
        select.peso-input option,
        select.peso-input option:hover,
        select.peso-input option:focus,
        select.peso-input option:checked { background: #fff; color: #1f2937; }
        .custom-dropdown-list { background: #fff !important; border-color: var(--n-200) !important; box-shadow: 0 10px 28px rgba(0,0,0,0.12) !important; }
        .custom-dropdown-item { color: #1f2937 !important; }
        .custom-dropdown-item:hover { background: var(--g-50) !important; color: var(--g-700) !important; }
        .custom-dropdown-empty { color: var(--n-500) !important; }
        .form-check-label { color: var(--n-700) !important; }
        .form-check-input { background-color: #fff; border-color: var(--n-300); }
        .form-check-input:checked { background-color: var(--g-600); border-color: var(--g-600); }
        .radio-box { border-color: var(--n-200) !important; background: var(--n-50) !important; }
        .radio-group-title { color: var(--g-700) !important; }
        .toggle-pw { color: var(--n-500) !important; }
        .toggle-pw:hover { color: var(--g-700) !important; }
        .section-heading { color: var(--g-700) !important; }
        .section-heading i { color: var(--g-600) !important; }
        .alert-error { background: var(--danger-bg) !important; color: var(--danger) !important; border-color: var(--danger-br) !important; }
        .alert-warn  { background: var(--warn-bg) !important; color: var(--warn) !important; }
        .cert-box { background: var(--n-50) !important; border-color: var(--n-200) !important; }
        .cert-box p, .cert-box strong { color: var(--n-700) !important; }
        .progress-seg { background: var(--n-200) !important; }
        .progress-seg.active { background: var(--g-600) !important; }
        .wizard-nav { border-top-color: var(--n-200) !important; }
        .step-info { color: var(--n-500) !important; }
        .btn-step { border-color: var(--n-300) !important; color: var(--g-700) !important; }
        .btn-step:hover:not(:disabled) { background: var(--n-50) !important; }
        .btn-register { background: var(--g-600) !important; color: #fff !important; box-shadow: 0 4px 14px rgba(22, 128, 61, 0.25) !important; }

        /* Inline colours written for the dark glass. White text becomes dark
           green, except the white icon on the green logo circle. */
        .card-register [style*="color:#fff"]:not([style*="background:var(--g-600)"]) { color: var(--g-700) !important; }
        .card-register [style*="color:rgba(255,255,255"] { color: var(--n-500) !important; }
        .card-register [style*="background:rgba(255,255,255"] { background: var(--n-50) !important; }
        .card-register [style*="border:1px solid rgba(255,255,255"] { border-color: var(--n-200) !important; }
    </style>
</head>
<body>

    <div class="bg-wrapper"></div>
    <div class="bg-overlay"></div>

    <div class="page">
    <div class="card-register">
        <div class="text-center mb-4">
            <div style="width:52px;height:52px;background:var(--g-600);
                        border-radius:50%;display:flex;align-items:center;justify-content:center;
                        margin:0 auto 10px;font-size:22px;color:#fff;">
                <i class="ph-fill ph-user"></i>
            </div>
            <div style="font-size:20px;font-weight:800;color:#fff;">Job Seeker Registration</div>
            <div style="font-size:13px;color:rgba(255,255,255,0.8);margin-top:4px;">Create your PESO jobseeker account</div>
        </div>

        @if($errors->any())
            <div style="background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger-br);border-radius:10px;padding:10px 14px;
                        font-size:12px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <i class="ph-fill ph-warning-circle"></i> {{ $errors->first() }}
            </div>
        @endif

        <div id="nameEmailWarning" style="background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger-br);border-radius:10px;padding:10px 14px;
                    font-size:12px;margin-bottom:12px;display:none;align-items:center;gap:8px;">
            <i class="ph-fill ph-warning-circle"></i> <span id="warn-text"></span>
        </div>

        <form method="POST" action="{{ route('register.jobseeker.post') }}">
            @csrf
            @include('partials.data-privacy-notice', ['audience' => 'jobseeker'])
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="peso-label">First Name *</label>
                    <input type="text" name="first_name" class="peso-input"
                        placeholder="Juan" value="{{ old('first_name') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="peso-label">Middle Name</label>
                    <input type="text" name="middle_name" class="peso-input"
                        placeholder="Optional" value="{{ old('middle_name') }}">
                </div>
                <div class="col-md-4">
                    <label class="peso-label">Last Name *</label>
                    <input type="text" name="last_name" class="peso-input"
                        placeholder="Dela Cruz" value="{{ old('last_name') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="peso-label">Email Address *</label>
                    <input type="email" name="email" id="emailInput" class="peso-input"
                        placeholder="juan@email.com" value="{{ old('email') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="peso-label">Phone Number</label>
                    <input type="text" name="phone" class="peso-input"
                        inputmode="numeric" maxlength="11" pattern="09[0-9]{9}"
                        placeholder="09171234567" value="{{ old('phone') }}">
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <label class="peso-label">Password *</label>
                    <div class="input-wrap">
                        <input type="password" name="password" id="pw1" class="peso-input"
                            placeholder="Set a strong password" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('pw1','icon1')">
                            <i class="ph ph-eye" id="icon1"></i>
                        </button>
                    </div>
                    @include('partials.password-hint', ['onDark' => false])
                </div>
                <div class="col-md-4">
                    <label class="peso-label">Confirm Password *</label>
                    <div class="input-wrap">
                        <input type="password" name="password_confirmation" id="pw2" class="peso-input"
                            placeholder="Repeat password" required>
                        <button type="button" class="toggle-pw" onclick="togglePw('pw2','icon2')">
                            <i class="ph ph-eye" id="icon2"></i>
                        </button>
                    </div>
                    <div id="pwMismatchWarn" style="display:none;color:var(--danger);font-size:11px;margin-top:5px;">
                        <i class="ph-fill ph-warning-circle me-1"></i>Passwords do not match.
                    </div>
                </div>
            </div>
            <button type="submit" class="btn-register">
                <i class="ph-fill ph-user-list me-2"></i>Create Account
            </button>
        </form>

        <div class="text-center mt-3" style="font-size:13px;color:rgba(255,255,255,0.75);">
            Already have an account?
            <a href="{{ route('login') }}" style="color:#fff;font-weight:700;text-decoration:underline;">Sign in</a>
        </div>
    </div>
    </div>

    <script>
        function togglePw(id, iconId) {
            const input = document.getElementById(id);
            const icon  = document.getElementById(iconId);
            input.type  = input.type === 'password' ? 'text' : 'password';
            icon.className = input.type === 'password' ? 'ph ph-eye' : 'ph ph-eye-slash';
        }

        const firstNameInput = document.querySelector('input[name="first_name"]');
        const lastNameInput  = document.querySelector('input[name="last_name"]');
        const emailInput     = document.getElementById('emailInput');
        const warning        = document.getElementById('nameEmailWarning');
        const warnText       = document.getElementById('warn-text');

        function showWarn(msg) { warnText.textContent = msg; warning.style.display = 'flex'; }
        function hideWarn()    { warning.style.display = 'none'; }

        function checkName() {
            const first = firstNameInput.value.trim();
            const last  = lastNameInput.value.trim();
            if (!first || !last) { hideWarn(); return; }
            fetch(`/check-name?first_name=${encodeURIComponent(first)}&last_name=${encodeURIComponent(last)}`)
                .then(r => r.json())
                .then(data => data.taken ? showWarn('This name is already registered. If this is you, please sign in instead.') : hideWarn());
        }

        function checkEmail() {
            const email = emailInput.value.trim();
            if (!email) { hideWarn(); return; }
            fetch(`/check-email?email=${encodeURIComponent(email)}`)
                .then(r => r.json())
                .then(data => data.taken ? showWarn('This email address is already registered. Please sign in instead.') : hideWarn());
        }

        firstNameInput.addEventListener('blur', checkName);
        lastNameInput.addEventListener('blur', checkName);
        emailInput.addEventListener('blur', checkEmail);

        const pw1Input     = document.getElementById('pw1');
        const pw2Input     = document.getElementById('pw2');
        const pwMismatchEl = document.getElementById('pwMismatchWarn');

        function checkPasswordMatch() {
            if (!pw2Input.value) {
                pwMismatchEl.style.display = 'none';
                return;
            }
            pwMismatchEl.style.display = (pw1Input.value !== pw2Input.value) ? 'block' : 'none';
        }

        pw1Input.addEventListener('input', checkPasswordMatch);
        pw2Input.addEventListener('input', checkPasswordMatch);
    </script>
</body>
</html>