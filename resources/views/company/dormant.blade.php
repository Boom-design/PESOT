{{--
    Ang employer nga na-disable kay wala mitubag sa pangutana sa opisina kung
    nagpangita pa ba sila ug tawo. Kini ra ang pahina nga iyang maabot — ang
    EnsureEmployerActive nga middleware maoy nagpugos.

    Walay link pabalik sa dashboard: ang bugtong gawasanan mao ang pagsulti sa
    ilang rason o ang logout. Ang account moabli pag-usab kung mabasa na kini
    sa staff — dili automatic, kay ang tumong sa tibuok butang mao nga adunay
    tawo sa opisina nga nakahibalo kung unsa ang nahitabo sa kompanya.
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
            width:100%; max-width:560px; background:rgba(255,255,255,0.1);
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
        .status-option {
            display:flex; align-items:flex-start; gap:10px; padding:11px 14px; margin-bottom:8px;
            background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.28);
            border-radius:10px; cursor:pointer;
        }
        .status-option:hover { background:rgba(255,255,255,0.16); }
        .status-option input { margin-top:3px; flex-shrink:0; }
        .status-option .label { font-size:13.5px; font-weight:700; color:#fff; }
        .status-option .hint  { font-size:11.5px; color:rgba(255,255,255,0.78); line-height:1.5; }
        .btn-login {
            width:100%; padding:12px; border:none; border-radius:10px; background:#fff;
            color:#1D6F27; font-size:14px; font-weight:800; cursor:pointer;
        }
        .btn-login:hover { background:rgba(255,255,255,0.9); }
        .error-msg, .notice, .ok-msg {
            border-radius:10px; padding:11px 14px; font-size:12.5px; margin-bottom:14px; line-height:1.6;
        }
        .error-msg { background:rgba(220,53,69,0.22); border:1px solid rgba(255,140,150,0.6); color:#fff; }
        .ok-msg    { background:rgba(58,163,70,0.30); border:1px solid rgba(179,230,184,0.7); color:#fff; }
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

        <div class="form-title">Your account is inactive</div>
        <div class="form-sub">
            Nothing has been deleted. Tell PESO what happened and staff will switch
            your account back on.
        </div>

        @if(session('success'))
            <div class="ok-msg"><i class="ph-fill ph-check-circle me-1"></i>{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="error-msg"><i class="ph-fill ph-warning-circle me-1"></i>{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="error-msg"><i class="ph-fill ph-warning-circle me-1"></i>{{ $errors->first() }}</div>
        @endif

        <div class="notice">
            <i class="ph-fill ph-info me-1"></i>
            @if($lastPosted)
                Your last job vacancy was posted on <strong>{{ $lastPosted->format('M d, Y') }}</strong>.
            @else
                No job vacancy has ever been posted from this account.
            @endif
            @if($employer?->inactivity_notified_at)
                We emailed you on <strong>{{ $employer->inactivity_notified_at->format('M d, Y') }}</strong>
                to ask about your hiring status and did not hear back.
            @endif
            @if($hiddenCount)
                <strong>{{ $hiddenCount }}</strong> of your job postings
                {{ $hiddenCount === 1 ? 'is' : 'are' }} hidden from jobseekers for now, and
                {{ $hiddenCount === 1 ? 'it comes' : 'they come' }} back when the account is reopened.
            @endif
        </div>

        @if($employer?->inactivity_responded_at)
            <div class="ok-msg">
                <i class="ph-fill ph-paper-plane-tilt me-1"></i>
                You already answered on {{ $employer->inactivity_responded_at->format('M d, Y g:i A') }}.
                PESO staff will review it and reopen your account. You may sign out — nothing else is
                needed from you.
            </div>
        @endif

        <form method="POST" action="{{ route('employer.dormant.submit') }}">
            @csrf

            <label class="peso-label">Which one describes your company right now?</label>

            @foreach([
                'still_hiring' => ['Still hiring', 'We are operating and will post vacancies again.'],
                'paused'       => ['Paused for now', 'We are operating but not hiring at the moment.'],
                'closed'       => ['Closed down', 'The company has stopped operating.'],
            ] as $value => $option)
            <label class="status-option">
                <input type="radio" name="inactivity_status" value="{{ $value }}"
                       {{ old('inactivity_status', $employer?->inactivity_status) === $value ? 'checked' : '' }} required>
                <span>
                    <span class="label">{{ $option[0] }}</span><br>
                    <span class="hint">{{ $option[1] }}</span>
                </span>
            </label>
            @endforeach

            <label class="peso-label" style="margin-top:14px;">Tell PESO what happened</label>
            <textarea name="inactivity_response" class="peso-input" rows="4" maxlength="2000"
                      placeholder="A sentence or two is enough."
                      required>{{ old('inactivity_response', $employer?->inactivity_response) }}</textarea>

            <button type="submit" class="btn-login">
                <i class="ph-fill ph-paper-plane-tilt me-2"></i>Send to PESO
            </button>
        </form>

        <div class="logout-row">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"><i class="ph ph-sign-out me-1"></i>Sign out</button>
            </form>
        </div>
    </div>
</body>
</html>
