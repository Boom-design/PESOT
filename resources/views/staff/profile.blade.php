@extends('staff.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">My Profile</h5>
    <p class="mb-0" style="font-size:13px;color:#888;">Manage your account information and password</p>
</div>

<div class="row g-4">

    {{-- ── PROFILE INFO ── --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                <h6 class="fw-bold mb-0" style="color:#2d7a5f;">
                    <i class="bi bi-person-badge me-2" style="color:#4dd9c0;"></i>Staff Information
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('staff.profile.update') }}">
                    @csrf
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-semibold small" style="color:#2d7a5f;">Full Name *</label>
                            <input type="text" name="name" class="form-control"
                                style="border:1.5px solid #a8e6cf; border-radius:10px; font-size:13px;"
                                value="{{ old('name', $staff->name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small" style="color:#2d7a5f;">Email Address *</label>
                            <input type="email" name="email" class="form-control"
                                style="border:1.5px solid #a8e6cf; border-radius:10px; font-size:13px;"
                                value="{{ old('email', $staff->email) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small" style="color:#2d7a5f;">Phone Number</label>
                            <input type="text" name="phone" class="form-control"
                                style="border:1.5px solid #a8e6cf; border-radius:10px; font-size:13px;"
                                value="{{ old('phone', $staff->phone) }}">
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn fw-semibold px-4"
                                style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                       color:#fff; border:none; border-radius:10px;">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── RIGHT COLUMN ── --}}
    <div class="col-lg-5">

        {{-- Account Card --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body text-center p-4">
                <div style="width:72px;height:72px;
                            background:linear-gradient(135deg,#90d870,#4dd9c0);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 14px;
                            font-size:32px;color:#fff;">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div class="fw-bold mb-1" style="font-size:16px;color:#2d7a5f;">
                    {{ $staff->name }}
                </div>
                <div style="font-size:12px;color:#888;">{{ $staff->email }}</div>
                <div class="mt-2 d-flex gap-2 justify-content-center">
                    <span style="background:#e8f8f3;color:#2d7a5f;font-size:11px;
                                 padding:4px 12px;border-radius:20px;font-weight:600;">
                        STAFF
                    </span>
                    <span style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                                 color:#fff;font-size:11px;
                                 padding:4px 12px;border-radius:20px;font-weight:600;">
                        {{ $staff->staff_role === 'job_fair' ? 'JOB FAIR' : strtoupper($staff->staff_role) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                <h6 class="fw-bold mb-0" style="color:#2d7a5f;">
                    <i class="bi bi-lock me-2" style="color:#4dd9c0;"></i>Change Password
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('staff.profile.password') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:#2d7a5f;">Current Password</label>
                        <div style="position:relative;">
                            <input type="password" name="current_password" id="cp"
                                class="form-control"
                                style="border:1.5px solid #a8e6cf; border-radius:10px;
                                       font-size:13px; padding-right:40px;"
                                placeholder="Enter current password" required>
                            <button type="button" onclick="toggleField('cp','cp-icon')"
                                style="position:absolute;right:12px;top:50%;
                                       transform:translateY(-50%);background:none;
                                       border:none;color:#888;cursor:pointer;font-size:15px;">
                                <i class="bi bi-eye" id="cp-icon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color:#2d7a5f;">New Password</label>
                        <div style="position:relative;">
                            <input type="password" name="new_password" id="np"
                                class="form-control"
                                style="border:1.5px solid #a8e6cf; border-radius:10px;
                                       font-size:13px; padding-right:40px;"
                                placeholder="Min. 6 characters" required>
                            <button type="button" onclick="toggleField('np','np-icon')"
                                style="position:absolute;right:12px;top:50%;
                                       transform:translateY(-50%);background:none;
                                       border:none;color:#888;cursor:pointer;font-size:15px;">
                                <i class="bi bi-eye" id="np-icon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold small" style="color:#2d7a5f;">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation"
                            class="form-control"
                            style="border:1.5px solid #a8e6cf; border-radius:10px; font-size:13px;"
                            placeholder="Re-type new password" required>
                    </div>
                    <button type="submit" class="btn w-100 fw-semibold"
                        style="background:linear-gradient(90deg,#90d870,#4dd9c0);
                               color:#fff; border:none; border-radius:10px;">
                        <i class="bi bi-lock me-1"></i> Change Password
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function toggleField(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }
</script>
@endpush

@endsection